<?php

function getUsersTableColumns(PDO $pdo): array
{
    static $columns = null;
    if ($columns !== null) {
        return $columns;
    }
    try {
        $columns = array_column($pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_ASSOC), 'Field');
    } catch (Throwable $e) {
        $columns = ['id', 'nom', 'prenom', 'email', 'gsm', 'password', 'role'];
    }
    return $columns;
}

/**
 * @return array{ok: bool, error?: string}
 */
function saveUserProfile(PDO $pdo, int $userId, array $input): array
{
    $nom = trim($input['nom'] ?? '');
    $prenom = trim($input['prenom'] ?? '');
    $email = trim($input['email'] ?? '');
    $telephone = trim($input['telephone'] ?? '');
    $dateNaissance = trim($input['date_naissance'] ?? '');
    $rue = trim($input['rue'] ?? '');
    $numero = trim($input['numero'] ?? '');
    $complement = trim($input['complement'] ?? '');
    $codePostal = trim($input['code_postal'] ?? '');
    $ville = trim($input['ville'] ?? '');

    if ($nom === '' || $prenom === '' || $email === '' || $telephone === '') {
        return ['ok' => false, 'error' => 'Tous les champs obligatoires doivent etre remplis.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Adresse email invalide.'];
    }

    $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $dup->execute([$email, $userId]);
    if ($dup->fetch()) {
        return ['ok' => false, 'error' => 'Cet email est deja utilise par un autre compte.'];
    }

    $cols = getUsersTableColumns($pdo);
    $values = [
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'telephone' => $telephone,
        'gsm' => $telephone,
        'date_naissance' => $dateNaissance !== '' ? $dateNaissance : null,
        'rue' => $rue,
        'numero' => $numero,
        'complement' => $complement,
        'code_postal' => $codePostal,
        'ville' => $ville,
    ];

    if (in_array('adresse', $cols, true) && !in_array('rue', $cols, true)) {
        $parts = array_filter([$numero, $rue, $complement, trim($codePostal . ' ' . $ville)]);
        $values['adresse'] = implode(', ', $parts);
    }

    $sets = [];
    $params = [];
    foreach ($values as $field => $value) {
        if (in_array($field, $cols, true)) {
            $sets[] = "{$field} = ?";
            $params[] = $value;
        }
    }

    if (empty($sets)) {
        return ['ok' => false, 'error' => 'Impossible de mettre a jour le profil (structure base incomplete).'];
    }

    $params[] = $userId;
    try {
        $pdo->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'Erreur lors de l\'enregistrement. Contactez le support si le probleme persiste.'];
    }

    return ['ok' => true];
}

function userTablesAvailable(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        $pdo->query('SELECT 1 FROM user_favoris LIMIT 1');
        $pdo->query('SELECT 1 FROM notifications LIMIT 1');
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

function getUserDashboardStats(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM commandes WHERE user_id = ?');
    $stmt->execute([$userId]);
    $totalCommandes = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE user_id = ? AND statut NOT IN ('terminee','annulee')");
    $stmt->execute([$userId]);
    $enCours = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(prix_total), 0) FROM commandes WHERE user_id = ? AND statut != 'annulee'");
    $stmt->execute([$userId]);
    $totalDepense = (float)$stmt->fetchColumn();

    $favoris = 0;
    if (userTablesAvailable($pdo)) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_favoris WHERE user_id = ?');
        $stmt->execute([$userId]);
        $favoris = (int)$stmt->fetchColumn();
    }

    return [
        'total_commandes' => $totalCommandes,
        'en_cours' => $enCours,
        'total_depense' => $totalDepense,
        'favoris' => $favoris,
    ];
}

function getUserFavoris(PDO $pdo, int $userId): array
{
    if (!userTablesAvailable($pdo)) {
        return [];
    }
    $stmt = $pdo->prepare('
        SELECT m.*, uf.created_at AS favori_le
        FROM user_favoris uf
        JOIN menus m ON m.id = uf.menu_id
        WHERE uf.user_id = ?
        ORDER BY uf.created_at DESC
    ');
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isMenuFavori(PDO $pdo, int $userId, int $menuId): bool
{
    if (!userTablesAvailable($pdo) || $menuId <= 0) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT 1 FROM user_favoris WHERE user_id = ? AND menu_id = ?');
    $stmt->execute([$userId, $menuId]);
    return (bool)$stmt->fetchColumn();
}

function toggleUserFavori(PDO $pdo, int $userId, int $menuId): ?bool
{
    if (!userTablesAvailable($pdo)) {
        return null;
    }
    if ($menuId <= 0) {
        return null;
    }
    if (isMenuFavori($pdo, $userId, $menuId)) {
        $pdo->prepare('DELETE FROM user_favoris WHERE user_id = ? AND menu_id = ?')->execute([$userId, $menuId]);
        return false;
    }
    $pdo->prepare('INSERT INTO user_favoris (user_id, menu_id) VALUES (?, ?)')->execute([$userId, $menuId]);
    return true;
}

function createUserNotification(PDO $pdo, int $userId, string $titre, string $message = '', ?string $lien = null, string $type = 'info'): void
{
    if (!userTablesAvailable($pdo)) {
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, titre, message, lien) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $type, $titre, $message, $lien]);
}

function getUserNotifications(PDO $pdo, int $userId, int $limit = 20): array
{
    if (!userTablesAvailable($pdo)) {
        return [];
    }
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countUnreadNotifications(PDO $pdo, int $userId): int
{
    if (!userTablesAvailable($pdo)) {
        return 0;
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function markNotificationRead(PDO $pdo, int $userId, int $notificationId): void
{
    if (!userTablesAvailable($pdo)) {
        return;
    }
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$notificationId, $userId]);
}

function markAllNotificationsRead(PDO $pdo, int $userId): void
{
    if (!userTablesAvailable($pdo)) {
        return;
    }
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$userId]);
}

function getReviewEligibleStatuts(): array
{
    return ['livre', 'en_attente_materiel', 'terminee'];
}

function userHasReviewForOrder(PDO $pdo, int $userId, int $orderId): bool
{
    if ($orderId <= 0) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM avis WHERE user_id = ? AND commande_id = ?');
    $stmt->execute([$userId, $orderId]);
    return (int)$stmt->fetchColumn() > 0;
}

function canUserReviewOrder(PDO $pdo, int $userId, int $orderId): array
{
    $result = [
        'can_review' => false,
        'already_reviewed' => false,
        'reason' => '',
        'order' => null,
    ];

    if ($orderId <= 0) {
        $result['reason'] = 'Commande invalide.';
        return $result;
    }

    $stmt = $pdo->prepare('
        SELECT c.*, m.titre AS menu_titre
        FROM commandes c
        JOIN menus m ON m.id = c.menu_id
        WHERE c.id = ? AND c.user_id = ?
    ');
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $result['reason'] = 'Commande introuvable.';
        return $result;
    }

    $result['order'] = $order;

    if ($order['statut'] === 'annulee') {
        $result['reason'] = 'Cette commande a ete annulee.';
        return $result;
    }

    if (!in_array($order['statut'], getReviewEligibleStatuts(), true)) {
        $result['reason'] = 'Vous pourrez laisser un avis une fois la commande livree.';
        return $result;
    }

    if (userHasReviewForOrder($pdo, $userId, $orderId)) {
        $result['already_reviewed'] = true;
        $result['reason'] = 'Vous avez deja laisse un avis pour cette commande.';
        return $result;
    }

    $result['can_review'] = true;
    return $result;
}

function getOrdersPendingReview(PDO $pdo, int $userId): array
{
    $eligible = getReviewEligibleStatuts();
    $placeholders = implode(',', array_fill(0, count($eligible), '?'));
    $params = array_merge([$userId], $eligible);

    $stmt = $pdo->prepare("
        SELECT c.id, c.statut, c.date_livraison, m.titre AS menu_titre
        FROM commandes c
        JOIN menus m ON m.id = c.menu_id
        LEFT JOIN avis a ON a.commande_id = c.id AND a.user_id = c.user_id
        WHERE c.user_id = ?
          AND c.statut IN ($placeholders)
          AND a.id IS NULL
        ORDER BY c.date_livraison DESC
    ");
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchCommandeForUser(PDO $pdo, int $userId, int $orderId): ?array
{
    $stmt = $pdo->prepare('
        SELECT c.*, m.titre AS menu_titre, m.theme, m.prix AS menu_prix_unitaire, m.min_personnes
        FROM commandes c
        JOIN menus m ON m.id = c.menu_id
        WHERE c.id = ? AND c.user_id = ?
    ');
    $stmt->execute([$orderId, $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function fetchCommandePlats(PDO $pdo, int $orderId): array
{
    $stmt = $pdo->prepare('
        SELECT cd.quantite, cd.type, p.nom, p.regime, p.allergenes
        FROM commande_details cd
        JOIN plats p ON p.id = cd.plat_id
        WHERE cd.commande_id = ?
        ORDER BY FIELD(cd.type, "entree", "plat", "dessert"), p.nom
    ');
    $stmt->execute([$orderId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchCommandeBoissons(PDO $pdo, int $orderId): array
{
    try {
        $stmt = $pdo->prepare('
            SELECT cb.quantite, cb.prix_unitaire, b.nom
            FROM commande_boissons cb
            JOIN boissons b ON b.id = cb.boisson_id
            WHERE cb.commande_id = ?
            ORDER BY b.nom
        ');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function computeBoissonsSubtotal(array $boissons): float
{
    $total = 0.0;
    foreach ($boissons as $b) {
        $total += (float)($b['prix_unitaire'] ?? 0) * (int)($b['quantite'] ?? 0);
    }
    return round($total, 2);
}

function getPlatTypeLabels(): array
{
    return [
        'entree' => 'Entrees',
        'plat' => 'Plats',
        'dessert' => 'Desserts',
    ];
}

function fetchCommandeHistorique(PDO $pdo, int $orderId): array
{
    $stmt = $pdo->prepare('SELECT * FROM commande_historique WHERE commande_id = ? ORDER BY created_at ASC');
    $stmt->execute([$orderId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
