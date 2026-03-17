<?php
session_start();
require 'includes/db.php';

if($_SERVER["REQUEST_METHOD"] === "POST"){

$sql="INSERT INTO menus
(titre,description,theme,regime,prix,min_personnes,stock)
VALUES(?,?,?,?,?,?,?)";

$stmt=$pdo->prepare($sql);

$stmt->execute([
$_POST["titre"],
$_POST["description"],
$_POST["theme"],
$_POST["regime"],
$_POST["prix"],
$_POST["min_personnes"],
$_POST["stock"]
]);

header("Location: admin-menus.php");
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">

<h2>Ajouter un menu</h2>

<form method="POST">

<input name="titre" class="form-control mb-2" placeholder="Titre">

<textarea name="description" class="form-control mb-2"></textarea>

<input name="theme" class="form-control mb-2" placeholder="Thème">

<input name="regime" class="form-control mb-2" placeholder="Régime">

<input name="prix" class="form-control mb-2" placeholder="Prix">

<input name="min_personnes" class="form-control mb-2" placeholder="Minimum">

<input name="stock" class="form-control mb-2" placeholder="Stock">

<button class="btn btn-success">
Créer
</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>