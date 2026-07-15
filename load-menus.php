<?php 
require 'includes/db.php';

$search = $_GET["search"] ?? '';
$theme = $_GET["theme"] ?? '';
$regime = $_GET["regime"] ?? '';
$prix_min = $_GET["prix_min"] ?? '';
$prix_max = $_GET["prix_max"] ?? '';
$personnes = $_GET["personnes"] ?? '';
$sort = $_GET["sort"] ?? '';

$sql = "SELECT * FROM menus WHERE 1=1";
$params = [];

/* SEARCH */
if(!empty($search)){
$sql .= " AND titre LIKE ?";
$params[] = "%$search%";
}

/* THEME (safe LOWER) */
if(!empty($theme)){
$sql .= " AND LOWER(COALESCE(theme,'')) = ?";
$params[] = strtolower($theme);
}

/* REGIME (safe NULL) */
if(!empty($regime)){
$sql .= " AND LOWER(COALESCE(regime,'')) = ?";
$params[] = strtolower($regime);
}

/* PRIX */
if($prix_min !== ''){
$sql .= " AND prix >= ?";
$params[] = $prix_min;
}

if($prix_max !== ''){
$sql .= " AND prix <= ?";
$params[] = $prix_max;
}

/* PERSONNES */
if(!empty($personnes)){
$sql .= " AND min_personnes >= ?";
$params[] = $personnes;
}

/* TRI */
switch($sort){
case "prix_asc":
    $sql .= " ORDER BY prix ASC";
    break;
case "prix_desc":
    $sql .= " ORDER BY prix DESC";
    break;
default:
    $sql .= " ORDER BY id DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$menus = $stmt->fetchAll();

/* DEBUG (TEMP) */
// echo "<pre>"; print_r($menus); echo "</pre>";

if(empty($menus)){
echo "<p class='text-center mt-5'>Aucun menu trouvé</p>";
exit;
}

/* DISPLAY */
foreach($menus as $menu){

$themeImg = strtolower($menu["theme"] ?? "default");

echo '
<div class="col-xl-3 col-lg-4 col-md-6">

    <div class="card menu-card h-100 shadow-sm">

        <img src="assets/images/menu-'.$themeImg.'.jpg"
                class="card-img-top"
                onerror="this.src=\'assets/images/default.jpg\'">

        <div class="card-body d-flex flex-column">

            <h5 class="fw-bold">'.htmlspecialchars($menu["titre"]).'</h5>

            <p class="text-muted small flex-grow-1">
                '.htmlspecialchars(substr($menu["description"] ?? "",0,80)).'...
            </p>

            <p>👥 '.($menu["min_personnes"] ?? 0).' pers</p>

            <p class="text-danger fw-bold">
                '.number_format($menu["prix"],2).' €
            </p>

            <a href="menu.php?id='.$menu["id"].'"
                class="btn btn-danger w-100 mt-auto">
                Voir
            </a>

        </div>

    </div>

</div>
';
}