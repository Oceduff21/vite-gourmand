<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    http_response_code(405);
    die('Action non autorisee.');
}

$id = (int)($_POST['id'] ?? 0);
$statut = $_POST['statut'] ?? '';
$redirect = $_POST['redirect'] ?? 'list';
$redirectUrl = ($redirect === 'detail' && $id) ? 'commande-detail.php?id=' . $id : 'admin-commandes.php';
$allowed = array_keys(getStatutsCommande());

require '../includes/user-helpers.php';

if (!$id || !in_array($statut, $allowed, true)) {
    header('Location: admin-commandes.php');
    exit();
}

if ($statut === 'annulee') {
    $motif = trim($_POST['motif'] ?? '');
    $mode = trim($_POST['mode_contact'] ?? '');
    if (!$motif || !$mode) {
        die('Motif et mode de contact obligatoires pour annuler.');
    }
    $note = "Annulation - Contact: $mode - Motif: $motif";
    $staffId = (int)($_SESSION['user_id'] ?? 0);
    $pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?')->execute(['annulee', $id]);
    enregistrerHistorique($pdo, $id, 'annulee', $note, $staffId);
    $stmtUser = $pdo->prepare('SELECT user_id FROM commandes WHERE id = ?');
    $stmtUser->execute([$id]);
    $orderUserId = (int)$stmtUser->fetchColumn();
    if ($orderUserId > 0) {
        createUserNotification($pdo, $orderUserId, 'Commande #' . $id . ' refusee', $note, 'espace-utilisateur.php?tab=commandes', 'warning');
        $stmtMail = $pdo->prepare('SELECT u.email, u.prenom, u.nom FROM commandes c JOIN users u ON c.user_id = u.id WHERE c.id = ?');
        $stmtMail->execute([$id]);
        $client = $stmtMail->fetch(PDO::FETCH_ASSOC);
        if ($client && !empty($client['email'])) {
            sendMail(
                $client['email'],
                'Commande #' . $id . ' refusee - Vite & Gourmand',
                "Bonjour {$client['prenom']},\n\nVotre commande #{$id} n'a pas pu etre acceptee.\n\nMotif : {$motif}\nContact prealable : {$mode}\n\nPour toute question : contact@vite-gourmand.fr\n\nVite & Gourmand"
            );
        }
    }
    header('Location: ' . $redirectUrl);
    exit();
}

$staffId = (int)($_SESSION['user_id'] ?? 0);
$pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?')->execute([$statut, $id]);
enregistrerHistorique($pdo, $id, $statut, null, $staffId);

$stmtUser = $pdo->prepare('SELECT user_id FROM commandes WHERE id = ?');
$stmtUser->execute([$id]);
$orderUserId = (int)$stmtUser->fetchColumn();
if ($orderUserId > 0) {
    $notifTitle = $statut === 'acceptee'
        ? 'Commande #' . $id . ' confirmee'
        : 'Mise a jour commande #' . $id;
    $notifMsg = $statut === 'acceptee'
        ? 'Votre commande a ete acceptee. Nous preparons votre prestation.'
        : 'Nouveau statut : ' . getStatutLabel($statut);
    $notifType = $statut === 'acceptee' ? 'success' : 'info';
    if ($statut === 'livre') {
        $notifTitle = 'Commande #' . $id . ' livree';
        $notifMsg = 'Votre prestation a ete livree. Partagez votre avis !';
        $notifType = 'success';
    }
    createUserNotification(
        $pdo,
        $orderUserId,
        $notifTitle,
        $notifMsg,
        $statut === 'livre' ? 'avis.php?commande_id=' . $id : 'suivi-commande.php?id=' . $id,
        $notifType
    );
}

if ($statut === 'acceptee') {
    if ($redirect === 'detail') {
        $_SESSION['admin_flash'] = 'Commande confirmee. Le client a ete notifie.';
    }
    $stmt = $pdo->prepare('SELECT u.email, u.nom, u.prenom, c.date_livraison, c.heure_livraison, m.titre FROM commandes c JOIN users u ON c.user_id = u.id JOIN menus m ON c.menu_id = m.id WHERE c.id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && !empty($user['email'])) {
        sendMail(
            $user['email'],
            'Commande confirmee - Vite & Gourmand',
            "Bonjour {$user['prenom']},\n\nVotre commande #{$id} pour le menu \"{$user['titre']}\" est confirmee.\nLivraison prevue le {$user['date_livraison']} a {$user['heure_livraison']}.\n\nMerci de votre confiance !"
        );
    }
}

if ($statut === 'terminee') {
    $stmt = $pdo->prepare('SELECT u.email, u.nom, u.prenom FROM commandes c JOIN users u ON c.user_id = u.id WHERE c.id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if ($user) {
        sendMail($user['email'], 'Commande terminee - Vite & Gourmand', "Bonjour {$user['prenom']},\n\nVotre commande est terminee. Connectez-vous pour laisser un avis.\n\nMerci !");
    }
}

if ($statut === 'en_attente_materiel') {
    $stmt = $pdo->prepare('SELECT u.email, u.prenom FROM commandes c JOIN users u ON c.user_id = u.id WHERE c.id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if ($user) {
        sendMail($user['email'], 'Retour materiel requis', "Bonjour {$user['prenom']},\n\nMerci de restituer le materiel sous 10 jours ouvrables. Passé ce delai, 600 EUR seront factures (voir CGV).");
    }
}

header('Location: ' . $redirectUrl);
exit();
