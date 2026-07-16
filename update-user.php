<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/flash.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('Session expiree. Rechargez la page et reessayez.');
    header('Location: espace-utilisateur.php?tab=profil');
    exit();
}

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$date_naissance = $_POST['date_naissance'] ?? '';
$rue = trim($_POST['rue'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$complement = trim($_POST['complement'] ?? '');
$code_postal = trim($_POST['code_postal'] ?? '');
$ville = trim($_POST['ville'] ?? '');

if (!$nom || !$prenom || !$email || !$telephone) {
    die('Tous les champs obligatoires doivent etre remplis.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Email invalide.');
}

$stmt = $pdo->prepare('
UPDATE users SET
    nom = ?, prenom = ?, email = ?, telephone = ?, gsm = ?,
    date_naissance = ?, rue = ?, numero = ?, complement = ?,
    code_postal = ?, ville = ?
WHERE id = ?
');

$stmt->execute([
    $nom, $prenom, $email, $telephone, $telephone,
    $date_naissance ?: null, $rue, $numero, $complement,
    $code_postal, $ville, $_SESSION['user_id']
]);

setFlash('Profil mis a jour avec succes.');
header('Location: espace-utilisateur.php?tab=profil');
exit();
