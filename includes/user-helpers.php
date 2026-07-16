<?php

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
