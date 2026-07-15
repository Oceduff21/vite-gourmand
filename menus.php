<?php
require 'includes/db.php';
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">

<h1 class="text-center mb-5">Nos Menus</h1>

<!-- RECHERCHE -->
<div class="row mb-4">
    <div class="col-md-6 mx-auto">
        <input type="text" id="search-menu" class="form-control" placeholder="🔍 Rechercher un menu...">
    </div>
</div>

<!-- FILTRES -->
<div class="row g-3 mb-4">

<div class="col-md-2">
<select id="filtre-theme" class="form-select">
<option value="">Thème</option>
<option value="noel">Noël</option>
<option value="paques">Pâques</option>
<option value="mariage">Mariage</option>
<option value="anniversaire">Anniversaire</option>
<option value="gastronomique">Gastronomique</option>
<option value="oriental">Oriental</option>
<option value="brunch">Brunch</option>
<option value="business">Business</option>
<option value="enfant">Enfant</option>
</select>
</div>

<div class="col-md-2">
<select id="filtre-regime" class="form-select">
<option value="">Régime</option>
<option value="classique">Classique</option>
<option value="vegan">Vegan</option>
<option value="vegetarien">Végétarien</option>
<option value="premium">Premium</option>
</select>
</div>

<div class="col-md-2">
<input type="number" id="prix-min" class="form-control" placeholder="Prix min">
</div>

<div class="col-md-2">
<input type="number" id="prix-max" class="form-control" placeholder="Prix max">
</div>

<div class="col-md-2">
<input type="number" id="filtre-personnes" class="form-control" placeholder="Min pers">
</div>

<div class="col-md-2">
<select id="sort" class="form-select">
<option value="">Trier</option>
<option value="prix_asc">Prix ↑</option>
<option value="prix_desc">Prix ↓</option>
<option value="populaire">Populaire</option>
</select>
</div>

</div>

<!-- RESULTATS -->
<div class="row g-4" id="menus-container"></div>

</div>

<!-- SCRIPT AJAX -->
<script>

const search = document.getElementById("search-menu");
const theme = document.getElementById("filtre-theme");
const regime = document.getElementById("filtre-regime");
const prixMin = document.getElementById("prix-min");
const prixMax = document.getElementById("prix-max");
const personnes = document.getElementById("filtre-personnes");
const sort = document.getElementById("sort");

function loadMenus(){

    const params = new URLSearchParams({
        search: search.value,
        theme: theme.value,
        regime: regime.value,
        prix_min: prixMin.value,
        prix_max: prixMax.value,
        personnes: personnes.value,
        sort: sort.value
    });

    fetch("load-menus.php?" + params.toString())
    .then(res => res.text())
    .then(data => {
        document.getElementById("menus-container").innerHTML = data;
    });
}

/* EVENTS */
document.querySelectorAll(
"#search-menu, #filtre-theme, #filtre-regime, #prix-min, #prix-max, #filtre-personnes, #sort"
).forEach(el => {
    el.addEventListener("input", loadMenus);
});

/* LOAD INIT */
loadMenus();

</script>

<?php include 'includes/footer.php'; ?>