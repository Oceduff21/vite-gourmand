<?php
require 'includes/db.php';
require 'includes/helpers.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<?php include 'includes/header.php'; ?>

<div class="container">

<h1 class="text-center mb-5">Nos Menus</h1>

<div class="row mb-4">

<div class="col-md-6">

<input
type="text"
id="search-menu"
class="form-control"
placeholder="Rechercher un menu...">

</div>

</div>

<!-- FILTRES -->

<div class="row filtres align-items-center">

<div class="col-md-4 mb-3">

<select id="filtre-theme" class="form-select">
<option value="">Tous les thèmes</option>
<option value="noel">Noël</option>
<option value="paques">Pâques</option>
<option value="mariage">Mariage</option>
<option value="vegan">Vegan</option>
<option value="anniversaire">Anniversaire</option>
</select>

</div>

<div class="col-md-4 mb-3">

<select id="filtre-regime" class="form-select">
<option value="">Tous les régimes</option>
<option value="classique">Classique</option>
<option value="vegan">Vegan</option>
</select>

</div>

<div class="col-md-4 mb-3">

<input
type="number"
id="filtre-prix"
class="form-control"
placeholder="Prix maximum">

</div>

</div>

<!-- GRILLE MENUS -->

<div class="row g-4" id="menus-container"></div>


<script>

const search = document.getElementById("search-menu");
const theme = document.getElementById("filtre-theme");
const regime = document.getElementById("filtre-regime");
const prix = document.getElementById("filtre-prix");

function loadMenus(){

let url = "load-menus.php?";

url += "search=" + search.value;
url += "&theme=" + theme.value;
url += "&regime=" + regime.value;
url += "&prix=" + prix.value;

fetch(url)

.then(response => response.text())

.then(data => {

document.getElementById("menus-container").innerHTML = data;

});

}

search.addEventListener("input", loadMenus);
theme.addEventListener("change", loadMenus);
regime.addEventListener("change", loadMenus);
prix.addEventListener("input", loadMenus);

loadMenus();

</script>

<?php include 'includes/footer.php'; ?>