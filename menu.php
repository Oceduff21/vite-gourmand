<?php
require 'includes/db.php';
require 'includes/helpers.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* SECURITE ID */
if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
    die("Menu invalide");
}

$id = $_GET["id"];

/* RECUP MENU */
$sql = "SELECT * FROM menus WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$menu = $stmt->fetch();

if(!$menu){
    die("Menu introuvable");
}

/* VARIABLES PROPRES */
$titre = $menu["titre"];
$description = $menu["description"];
$theme = $menu["theme"];
$prix = $menu["prix"];
$min = $menu["min_personnes"];
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">

<div class="row">

<!-- IMAGE -->
<div class="col-md-6">

<div class="menu-img-wrapper">

<img 
src="assets/images/menu-<?= strtolower($theme) ?>.jpg"
onerror="this.src='assets/images/default.jpg'">

<?php $badge = getBadge($theme); ?>

<span class="menu-badge <?= $badge["class"] ?>">
<?= $badge["icon"] ?> <?= htmlspecialchars($theme) ?>
</span>

</div>

</div>

<!-- INFOS -->
<div class="col-md-6">

<h2><?= htmlspecialchars($titre) ?></h2>

<p class="text-muted"><?= htmlspecialchars($description) ?></p>

<hr>

<p><strong>Thème :</strong> <?= htmlspecialchars($theme) ?></p>
<p><strong>Minimum :</strong> <?= $min ?> personnes</p>

<h4 class="text-danger"><?= $prix ?> € / personne</h4>

<a href="commande.php?id=<?= $id ?>" class="btn btn-primary mt-3">
Commander ce menu
</a>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>