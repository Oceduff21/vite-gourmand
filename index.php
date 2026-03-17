<?php
require 'includes/db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* MENUS PHARES */
$sql = "SELECT * FROM menus LIMIT 3";
$stmt = $pdo->query($sql);
$menus = $stmt->fetchAll();
?>

<?php 
include 'includes/header.php';
require 'includes/helpers.php';
?>

<!-- HERO -->
<section class="hero">
<div class="hero-content">
<h1>Vite & Gourmand</h1>
<p>Traiteur à Bordeaux depuis 25 ans</p>
<a href="menus.php" class="btn btn-primary">Voir les menus</a>
</div>
</section>

<!-- MENUS -->
<section class="section">
<div class="container">

<h2 class="section-title">Nos menus</h2>

<div class="row">

<?php foreach($menus as $menu): ?>

<?php $badge = getBadge($menu["theme"]); ?>

<div class="col-md-4 mb-4">

<div class="menu-card">

<div class="menu-img-wrapper">

<img 
src="assets/images/menu-<?= strtolower($menu["theme"]) ?>.jpg"
onerror="this.src='assets/images/default.jpg'">

<span class="menu-badge <?= $badge["class"] ?>">
<?= $badge["icon"] ?> <?= htmlspecialchars($menu["theme"]) ?>
</span>

</div>

<div class="menu-body">

<h5><?= htmlspecialchars($menu["titre"]) ?></h5>

<p class="menu-desc">
<?= htmlspecialchars(substr($menu["description"],0,80)) ?>...
</p>

<div class="menu-footer">

<span class="menu-price">
<?= $menu["prix"] ?> €
</span>

<a href="menu.php?id=<?= $menu["id"] ?>" class="btn btn-sm btn-primary">
Voir
</a>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>
</section>

<?php include 'includes/footer.php'; ?>