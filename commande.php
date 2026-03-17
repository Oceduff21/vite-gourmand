<?php
require 'includes/db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* SECURITE */
if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
    die("Menu invalide");
}

$id = $_GET["id"];

/* RECUP MENU */
$sql = "SELECT * FROM menus WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$menu = $stmt->fetch();

/* DEBUG (temporaire) */
if(!$menu){
    die("Menu introuvable (id=".$id.")");
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">

<h2>Commander : <?= htmlspecialchars($menu["titre"]) ?></h2>

<p><?= htmlspecialchars($menu["description"]) ?></p>

<h4><?= $menu["prix"] ?> € / personne</h4>

<form method="POST" action="valider-commande.php">

<input type="hidden" name="menu_id" value="<?= $menu["id"] ?>">

<div class="mb-3">
<label>Nombre de personnes</label>
<input type="number" name="quantite" class="form-control" required min="1">
</div>

<button class="btn btn-primary">Valider la commande</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>