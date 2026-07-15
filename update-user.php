<?php
session_start();
require 'includes/db.php';

/* SECURITE */
if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit();
}

/* RECUP DATA */
$nom = $_POST["nom"] ?? '';
$prenom = $_POST["prenom"] ?? '';
$email = $_POST["email"] ?? '';
$telephone = $_POST["telephone"] ?? '';

$date_naissance = $_POST["date_naissance"] ?? '';

$rue = $_POST["rue"] ?? '';
$numero = $_POST["numero"] ?? '';
$complement = $_POST["complement"] ?? '';
$code_postal = $_POST["code_postal"] ?? '';
$ville = $_POST["ville"] ?? '';

/* VALIDATION */
if(!$nom || !$prenom || !$email || !$telephone){
    die("Tous les champs obligatoires doivent être remplis");
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    die("Email invalide");
}

/* UPDATE */
$stmt = $pdo->prepare("
UPDATE users SET
nom=?,
prenom=?,
email=?,
telephone=?,
date_naissance=?,
rue=?,
numero=?,
complement=?,
code_postal=?,
ville=?
WHERE id=?
");

$stmt->execute([
    $nom,
    $prenom,
    $email,
    $telephone,
    $date_naissance,
    $rue,
    $numero,
    $complement,
    $code_postal,
    $ville,
    $_SESSION["user_id"]
]);

/* REDIRECTION */
header("Location: espace-utilisateur.php");
exit();