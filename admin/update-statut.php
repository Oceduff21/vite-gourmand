<?php
session_start();
require '../includes/db.php';
require '../includes/helpers.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: ../index.php');
    exit();
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$statut = $_GET['statut'] ?? $_POST['statut'] ?? '';
$allowed = array_keys(getStatutsCommande());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $statut === 'annulee') {
    $motif = trim($_POST['motif'] ?? '');
    $mode = trim($_POST['mode_contact'] ?? '');
    if (!$motif || !$mode) {
        die('Motif et mode de contact obligatoires pour annuler.');
    }
    $note = "Annulation - Contact: $mode - Motif: $motif";
    $pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?')->execute(['annulee', $id]);
    enregistrerHistorique($pdo, $id, 'annulee', $note);
    header('Location: admin-commandes.php');
    exit();
}

if (!$id || !in_array($statut, $allowed)) {
    header('Location: admin-commandes.php');
    exit();
}

$pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?')->execute([$statut, $id]);
enregistrerHistorique($pdo, $id, $statut);

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

header('Location: admin-commandes.php');
exit();
