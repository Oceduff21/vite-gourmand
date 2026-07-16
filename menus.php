<?php
$pageTitle = 'Nos menus traiteur — Vite & Gourmand';
$pageDescription = 'Parcourez nos menus evenementiels : mariage, Noel, business, vegan, enfant. Filtrez par theme, regime et budget.';
require 'includes/db.php';
require 'includes/menu-helpers.php';
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">

<nav aria-label="Fil d'Ariane" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Menus</li>
    </ol>
</nav>

<h1 class="text-center mb-2">Nos menus traiteur</h1>
<p class="text-center text-muted mb-5">Composez votre menu et commandez en ligne — livraison sur Bordeaux Metropole.</p>

<div class="row mb-4">
    <div class="col-lg-8 mx-auto">
        <input type="search" id="search-menu" class="form-control form-control-lg" placeholder="Rechercher un menu..." autocomplete="off">
    </div>
</div>

<div class="row g-3 mb-4 align-items-end">
    <div class="col-md-2">
        <label for="filtre-theme" class="form-label small mb-1">Theme</label>
        <select id="filtre-theme" class="form-select">
            <option value="">Tous</option>
            <option value="noel">Noel</option>
            <option value="paques">Paques</option>
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
        <label for="filtre-regime" class="form-label small mb-1">Regime</label>
        <select id="filtre-regime" class="form-select">
            <option value="">Tous</option>
            <option value="classique">Classique</option>
            <option value="vegan">Vegan</option>
            <option value="vegetarien">Vegetarien</option>
            <option value="premium">Premium</option>
        </select>
    </div>
    <div class="col-md-2">
        <label for="prix-min" class="form-label small mb-1">Prix min</label>
        <input type="number" id="prix-min" class="form-control" min="0" step="1" placeholder="EUR">
    </div>
    <div class="col-md-2">
        <label for="prix-max" class="form-label small mb-1">Prix max</label>
        <input type="number" id="prix-max" class="form-control" min="0" step="1" placeholder="EUR">
    </div>
    <div class="col-md-2">
        <label for="filtre-personnes" class="form-label small mb-1">Nb invites</label>
        <input type="number" id="filtre-personnes" class="form-control" min="1" placeholder="Ex. 20">
    </div>
    <div class="col-md-2">
        <label for="sort" class="form-label small mb-1">Tri</label>
        <select id="sort" class="form-select">
            <option value="">Recents</option>
            <option value="prix_asc">Prix croissant</option>
            <option value="prix_desc">Prix decroissant</option>
            <option value="populaire">Populaires</option>
        </select>
    </div>
</div>

<div id="menus-loading" class="text-center text-muted py-4 d-none">
    <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
    Chargement des menus...
</div>

<div class="row g-4" id="menus-container"></div>

</div>

<script>
const search = document.getElementById('search-menu');
const theme = document.getElementById('filtre-theme');
const regime = document.getElementById('filtre-regime');
const prixMin = document.getElementById('prix-min');
const prixMax = document.getElementById('prix-max');
const personnes = document.getElementById('filtre-personnes');
const sort = document.getElementById('sort');
const container = document.getElementById('menus-container');
const loading = document.getElementById('menus-loading');
let loadTimer = null;

function loadMenus() {
    clearTimeout(loadTimer);
    loadTimer = setTimeout(() => {
        loading.classList.remove('d-none');
        const params = new URLSearchParams({
            search: search.value.trim(),
            theme: theme.value,
            regime: regime.value,
            prix_min: prixMin.value,
            prix_max: prixMax.value,
            personnes: personnes.value,
            sort: sort.value
        });

        fetch('load-menus.php?' + params.toString())
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(html => { container.innerHTML = html; })
            .catch(() => {
                container.innerHTML = '<div class="col-12"><p class="text-center text-danger py-5">Impossible de charger les menus. Rechargez la page.</p></div>';
            })
            .finally(() => loading.classList.add('d-none'));
    }, 250);
}

document.querySelectorAll('#search-menu, #filtre-theme, #filtre-regime, #prix-min, #prix-max, #filtre-personnes, #sort')
    .forEach(el => el.addEventListener('input', loadMenus));

loadMenus();
</script>

<?php include 'includes/footer.php'; ?>
