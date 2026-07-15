<?php
session_start();
require 'includes/db.php';

if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit();
}

$id = $_GET["id"] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM commandes WHERE id=? AND user_id=?");
$stmt->execute([$id, $_SESSION["user_id"]]);
$commande = $stmt->fetch();

if(!$commande){
    die("Commande introuvable");
}

if($commande["statut"] !== "en_attente"){
    die("Modification impossible");
}

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $nb = (int)$_POST["nb_personnes"];
    $date = $_POST["date"];
    $heure = $_POST["heure"];

    $stmt = $pdo->prepare("
    UPDATE commandes 
    SET nb_personnes=?, date_livraison=?, heure_livraison=?
    WHERE id=? AND user_id=?
    ");

    $stmt->execute([$nb, $date, $heure, $id, $_SESSION["user_id"]]);

    header("Location: espace-utilisateur.php");
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
<h2>Modifier la commande</h2>

<form method="POST">

<label>Personnes</label>
<input type="number" name="nb_personnes" value="<?= $commande["nb_personnes"] ?>" class="form-control">

<label class="mt-3">Date</label>
<input type="date" name="date" value="<?= $commande["date_livraison"] ?>" class="form-control">

<label class="mt-3">Heure</label>
<input type="time" name="heure" value="<?= $commande["heure_livraison"] ?>" class="form-control">

<br>

<button class="btn btn-primary">Enregistrer</button>

</form>
</div>

<?php include 'includes/footer.php'; ?>