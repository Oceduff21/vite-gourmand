<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';
require_once __DIR__ . '/../back/autoload.php';

use ViteGourmand\Controllers\CommandeController;
use ViteGourmand\Models\Commande;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    http_response_code(405);
    die('Action non autorisee.');
}

$id = (int)($_POST['id'] ?? 0);
$statut = $_POST['statut'] ?? '';
$redirect = $_POST['redirect'] ?? 'list';
$redirectUrl = ($redirect === 'detail' && $id) ? 'commande-detail.php?id=' . $id : 'admin-commandes.php';
$allowed = array_keys(Commande::getStatuts());
$commandeController = new CommandeController($pdo);

require '../includes/user-helpers.php';

function adminStatutRedirect(string $url, ?string $error = null, ?string $success = null): void
{
    if ($error) {
        $_SESSION['admin_flash_error'] = $error;
    }
    if ($success) {
        $_SESSION['admin_flash'] = $success;
    }
    header('Location: ' . $url);
    exit();
}

if (!$id || !in_array($statut, $allowed, true)) {
    header('Location: admin-commandes.php');
    exit();
}

$stmtCurrent = $pdo->prepare('SELECT statut, user_id FROM commandes WHERE id = ?');
$stmtCurrent->execute([$id]);
$current = $stmtCurrent->fetch(PDO::FETCH_ASSOC);
if (!$current) {
    header('Location: admin-commandes.php');
    exit();
}

$currentStatut = $current['statut'];
$orderUserId = (int)$current['user_id'];
$staffId = (int)($_SESSION['user_id'] ?? 0);

if ($statut === 'annulee') {
    $motif = trim($_POST['motif'] ?? '');
    $mode = trim($_POST['mode_contact'] ?? '');
    $contactConfirme = !empty($_POST['contact_confirme']);
    if (!$contactConfirme) {
        adminStatutRedirect(
            'annuler-commande.php?id=' . $id . ($redirect === 'detail' ? '&from=detail' : ''),
            'Confirmez avoir contacte le client avant d\'annuler la commande.'
        );
    }
    if (!$motif || !$mode) {
        adminStatutRedirect(
            'annuler-commande.php?id=' . $id . ($redirect === 'detail' ? '&from=detail' : ''),
            'Le mode de contact et le motif sont obligatoires.'
        );
    }
    $note = "Annulation - Contact: $mode - Motif: $motif";
    $pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?')->execute(['annulee', $id]);
    enregistrerHistorique($pdo, $id, 'annulee', $note, $staffId);
    if (is_file(__DIR__ . '/../includes/mongo.php')) {
        require_once __DIR__ . '/../includes/mongo.php';
        mongoUpdateCommandeStatut($id, 'annulee');
    }
    if ($orderUserId > 0) {
        createUserNotification($pdo, $orderUserId, 'Commande #' . $id . ' refusee', $note, 'espace-utilisateur.php?tab=commandes', 'warning');
        $stmtMail = $pdo->prepare('SELECT u.email, u.prenom FROM commandes c JOIN users u ON c.user_id = u.id WHERE c.id = ?');
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
    adminStatutRedirect($redirectUrl, null, 'Commande annulee. Le client a ete notifie.');
}

if ($currentStatut !== $statut && !$commandeController->canTransition($currentStatut, $statut)) {
    adminStatutRedirect(
        $redirectUrl,
        'Transition de statut non autorisee : ' . getStatutLabel($currentStatut) . ' → ' . getStatutLabel($statut) . '.'
    );
}

if ($currentStatut === $statut) {
    adminStatutRedirect($redirectUrl);
}

$pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?')->execute([$statut, $id]);
enregistrerHistorique($pdo, $id, $statut, null, $staffId);

if (is_file(__DIR__ . '/../includes/mongo.php')) {
    require_once __DIR__ . '/../includes/mongo.php';
    mongoUpdateCommandeStatut($id, $statut);
}

$stmtMail = $pdo->prepare('
    SELECT u.email, u.nom, u.prenom, c.date_livraison, c.heure_livraison, m.titre
    FROM commandes c
    JOIN users u ON c.user_id = u.id
    JOIN menus m ON c.menu_id = m.id
    WHERE c.id = ?
');
$stmtMail->execute([$id]);
$user = $stmtMail->fetch(PDO::FETCH_ASSOC);

if ($orderUserId > 0) {
    $notifTitle = 'Mise a jour commande #' . $id;
    $notifMsg = 'Nouveau statut : ' . getStatutLabel($statut);
    $notifType = 'info';
    $notifLink = 'suivi-commande.php?id=' . $id;

    if ($statut === 'acceptee') {
        $notifTitle = 'Commande #' . $id . ' confirmee';
        $notifMsg = 'Votre commande a ete acceptee. Nous preparons votre prestation.';
        $notifType = 'success';
    } elseif ($statut === 'en_preparation') {
        $notifTitle = 'Commande #' . $id . ' en preparation';
        $notifMsg = 'Notre equipe cuisine prepare votre menu.';
    } elseif ($statut === 'en_livraison') {
        $notifTitle = 'Commande #' . $id . ' en livraison';
        $notifMsg = 'Votre commande est en cours de livraison.';
    } elseif ($statut === 'livre') {
        $notifTitle = 'Commande #' . $id . ' livree';
        $notifMsg = 'Votre prestation a ete livree. Partagez votre avis !';
        $notifType = 'success';
        $notifLink = 'avis.php?commande_id=' . $id;
    } elseif ($statut === 'en_attente_materiel') {
        $notifTitle = 'Retour materiel requis';
        $notifMsg = 'Merci de restituer le materiel sous 10 jours ouvrables (voir email).';
        $notifType = 'warning';
    } elseif ($statut === 'terminee') {
        $notifTitle = 'Commande #' . $id . ' terminee';
        $notifMsg = 'Votre commande est terminee. Merci de votre confiance !';
        $notifType = 'success';
        $notifLink = 'avis.php?commande_id=' . $id;
    }

    createUserNotification($pdo, $orderUserId, $notifTitle, $notifMsg, $notifLink, $notifType);
}

if ($user && !empty($user['email'])) {
    $prenom = $user['prenom'];
    $dateLiv = $user['date_livraison'];
    $heureLiv = substr($user['heure_livraison'], 0, 5);
    $menuTitre = $user['titre'];

    if ($statut === 'acceptee') {
        sendMail(
            $user['email'],
            'Commande confirmee - Vite & Gourmand',
            "Bonjour {$prenom},\n\nVotre commande #{$id} pour le menu \"{$menuTitre}\" est confirmee.\nLivraison prevue le {$dateLiv} a {$heureLiv}.\n\nMerci de votre confiance !"
        );
    } elseif ($statut === 'en_preparation') {
        sendMail(
            $user['email'],
            'Commande en preparation - Vite & Gourmand',
            "Bonjour {$prenom},\n\nVotre commande #{$id} est en cours de preparation par notre equipe cuisine.\nLivraison prevue le {$dateLiv} a {$heureLiv}.\n\nVite & Gourmand"
        );
    } elseif ($statut === 'en_livraison') {
        sendMail(
            $user['email'],
            'Commande en livraison - Vite & Gourmand',
            "Bonjour {$prenom},\n\nVotre commande #{$id} est en cours de livraison par notre equipe logistique.\nRendez-vous prevu le {$dateLiv} a {$heureLiv}.\n\nVite & Gourmand"
        );
    } elseif ($statut === 'livre') {
        sendMail(
            $user['email'],
            'Commande livree - Vite & Gourmand',
            "Bonjour {$prenom},\n\nVotre commande #{$id} a ete livree.\nNous esperons que votre evenement s'est deroule a merveille.\nConnectez-vous pour laisser un avis.\n\nVite & Gourmand"
        );
    } elseif ($statut === 'en_attente_materiel') {
        sendMail(
            $user['email'],
            'Retour materiel requis - Vite & Gourmand',
            "Bonjour {$prenom},\n\nDu materiel vous a ete prete dans le cadre de votre commande #{$id}.\nMerci de le restituer sous 10 jours ouvrables en prenant contact avec Vite & Gourmand :\n- Email : contact@vite-gourmand.fr\n- Telephone : 04 12 34 56 78\n\nPasse ce delai, des frais de 600 EUR seront factures conformement a nos CGV.\n\nVite & Gourmand"
        );
    } elseif ($statut === 'terminee') {
        sendMail(
            $user['email'],
            'Commande terminee - Vite & Gourmand',
            "Bonjour {$prenom},\n\nVotre commande #{$id} est terminee.\nConnectez-vous pour laisser un avis si vous le souhaitez.\n\nMerci de votre confiance !"
        );
    }
}

adminStatutRedirect($redirectUrl, null, 'Statut mis a jour : ' . getStatutLabel($statut) . '.');
