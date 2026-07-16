<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/user-helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = (int)($_POST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '') || $id <= 0) {
    header('Location: espace-utilisateur.php?tab=commandes');
    exit();
}

$stmt = $pdo->prepare("UPDATE commandes SET statut = 'annulee' WHERE id = ? AND user_id = ? AND statut = 'en_attente'");
$stmt->execute([$id, $_SESSION['user_id']]);

if ($stmt->rowCount() > 0) {
    enregistrerHistorique($pdo, $id, 'annulee', 'Annulation par le client');
    createUserNotification(
        $pdo,
        (int)$_SESSION['user_id'],
        'Commande #' . $id . ' annulee',
        'Votre demande d\'annulation a ete enregistree.',
        'espace-utilisateur.php?tab=commandes',
        'info'
    );
}

header('Location: espace-utilisateur.php?tab=commandes');
exit();
