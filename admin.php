<?php
session_start();
require 'includes/db.php';

if(!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin"){
die("Accès refusé");
}

/* STATS */

$totalCommandes =
$pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();

$ca =
$pdo->query("SELECT SUM(prix_total) FROM commandes")->fetchColumn();

/* COMMANDES */

$sqlCommandes = "
SELECT commandes.*, users.nom, menus.titre
FROM commandes
JOIN users ON commandes.user_id = users.id
JOIN menus ON commandes.menu_id = menus.id
ORDER BY commandes.id DESC
";

$commandes = $pdo->query($sqlCommandes)->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>

<title>Dashboard Admin</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
background:#f4f6f9;
}

.sidebar{
height:100vh;
background:#1e1e2f;
color:white;
}

.sidebar a{
color:white;
text-decoration:none;
display:block;
padding:10px;
}

.sidebar a:hover{
background:#34344a;
}

.card{
border:none;
border-radius:10px;
}

</style>

</head>

<body>

<div class="container-fluid">

<div class="row">

<!-- SIDEBAR -->

<div class="col-md-2 sidebar p-3">

<h4>Admin</h4>

<hr>

<a href="admin.php">Dashboard</a>
<a href="admin-menus.php">Menus</a>
<a href="logout.php">Déconnexion</a>

</div>

<!-- CONTENU -->

<div class="col-md-10 p-4">

<h1 class="mb-4">Dashboard</h1>

<div class="row mb-4">

<div class="col-md-3">

<div class="card shadow-sm">

<div class="card-body">

<h6>Total commandes</h6>

<h2><?= $totalCommandes ?></h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card shadow-sm">

<div class="card-body">

<h6>Chiffre d'affaires</h6>

<h2><?= $ca ?? 0 ?> €</h2>

</div>

</div>

</div>

</div>


<!-- GRAPHIQUES -->

<div class="row">

<div class="col-md-6">

<div class="card shadow-sm">

<div class="card-body">

<h5>Commandes mensuelles</h5>

<canvas id="chartCommandes"></canvas>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card shadow-sm">

<div class="card-body">

<h5>Chiffre d'affaires</h5>

<canvas id="chartCA"></canvas>

</div>

</div>

</div>

</div>


<!-- TABLE COMMANDES -->

<div class="card shadow-sm mt-4">

<div class="card-body">

<h4>Commandes</h4>

<table class="table table-hover">

<thead>

<tr>
<th>Client</th>
<th>Menu</th>
<th>Personnes</th>
<th>Date</th>
<th>Prix</th>
</tr>

</thead>

<tbody>

<?php foreach($commandes as $commande): ?>

<tr>

<td><?= $commande["nom"] ?></td>
<td><?= $commande["titre"] ?></td>
<td><?= $commande["nb_personnes"] ?></td>
<td><?= $commande["date_livraison"] ?></td>
<td><?= $commande["prix_total"] ?> €</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

<script>

const labels=['Jan','Fev','Mar','Avr','Mai','Juin','Juil','Aout','Sep','Oct','Nov','Dec'];

const commandesData=[5,8,12,6,9,14,7,10,13,8,11,15];

const caData=[1200,1800,2200,1500,2000,2700,1600,2100,2500,1900,2300,3000];

new Chart(document.getElementById('chartCommandes'),{

type:'bar',

data:{
labels:labels,
datasets:[{
label:'Commandes',
data:commandesData,
backgroundColor:'#4e73df'
}]
}

});

new Chart(document.getElementById('chartCA'),{

type:'line',

data:{
labels:labels,
datasets:[{
label:'CA',
data:caData,
borderColor:'#1cc88a',
fill:true
}]
}

});

</script>

</body>

</html>