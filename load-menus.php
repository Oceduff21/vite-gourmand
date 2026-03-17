<?php

require 'includes/db.php';

$search = $_GET["search"] ?? "";
$theme = $_GET["theme"] ?? "";
$regime = $_GET["regime"] ?? "";
$prix = $_GET["prix"] ?? "";

$sql = "SELECT * FROM menus WHERE 1";

$params = [];

if($search){
$sql .= " AND titre LIKE ?";
$params[] = "%$search%";
}

if($theme){
$sql .= " AND theme = ?";
$params[] = $theme;
}

if($regime){
$sql .= " AND regime = ?";
$params[] = $regime;
}

if($prix){
$sql .= " AND prix <= ?";
$params[] = $prix;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$menus = $stmt->fetchAll();

foreach($menus as $menu){

echo '

<div class="col-xl-3 col-lg-4 col-md-6">

<div class="card h-100 shadow-sm">

<img
src="assets/images/menu-'.strtolower($menu["theme"]).'.jpg"
class="card-img-top">

<div class="card-body">

<h5>'.$menu["titre"].'</h5>

<p class="text-muted">'.$menu["description"].'</p>

<p>
<strong>'.$menu["prix"].' €</strong>
</p>

<a href="menu.php?id='.$menu["id"].'"
class="btn btn-primary w-100">

Voir le menu

</a>

</div>

</div>

</div>

';

}