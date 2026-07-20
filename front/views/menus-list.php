<!-- FRONT — vue HTML liste menus (pas de logique metier) -->
<div class="container py-5 menus-listing-page">

<h1 class="text-center mb-2">Nos menus traiteur</h1>
<p class="text-center menus-intro text-muted mb-4">Composez votre menu et commandez en ligne — livraison sur Bordeaux Metropole.</p>

<div class="menus-filter-panel card-custom mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-lg-8">
            <label for="search-menu" class="form-label fw-semibold">Rechercher</label>
            <input type="search" id="search-menu" class="form-control form-control-lg" placeholder="Ex. anniversaire, vegan, mariage..." autocomplete="off" aria-controls="menus-container" aria-describedby="search-menu-hint">
            <p id="search-menu-hint" class="form-text small mb-0">Combinez mots-cles, theme et regime : les resultats incluent tous les criteres saisis (ex. anniversaire + vegan).</p>
        </div>
        <div class="col-lg-4 d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary w-100" id="btn-reset-filters">Reinitialiser</button>
        </div>
    </div>

    <fieldset class="row g-3 mt-2 align-items-end border-0 p-0 m-0">
        <legend class="visually-hidden">Filtrer les menus</legend>
        <div class="col-md-6 col-lg-2">
            <label for="filtre-theme" class="form-label small fw-semibold">Theme</label>
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
        <div class="col-md-6 col-lg-2">
            <label for="filtre-regime" class="form-label small fw-semibold">Regime</label>
            <select id="filtre-regime" class="form-select">
                <option value="">Tous</option>
                <option value="classique">Classique</option>
                <option value="vegan">Vegan</option>
                <option value="vegetarien">Vegetarien</option>
                <option value="premium">Premium</option>
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <label for="prix-min" class="form-label small fw-semibold">Prix min (EUR)</label>
            <input type="number" id="prix-min" class="form-control" min="0" step="1" inputmode="numeric">
        </div>
        <div class="col-md-4 col-lg-2">
            <label for="prix-max" class="form-label small fw-semibold">Prix max (EUR)</label>
            <input type="number" id="prix-max" class="form-control" min="0" step="1" inputmode="numeric">
        </div>
        <div class="col-md-4 col-lg-2">
            <label for="filtre-personnes" class="form-label small fw-semibold">Nb invites</label>
            <input type="number" id="filtre-personnes" class="form-control" min="1" inputmode="numeric" placeholder="Ex. 20">
        </div>
        <div class="col-md-6 col-lg-2">
            <label for="sort" class="form-label small fw-semibold">Tri</label>
            <select id="sort" class="form-select">
                <option value="">Recents</option>
                <option value="prix_asc">Prix croissant</option>
                <option value="prix_desc">Prix decroissant</option>
                <option value="populaire">Populaires</option>
            </select>
        </div>
    </fieldset>
</div>

<div id="menus-loading" class="text-center text-muted py-4 d-none" role="status" aria-live="polite" aria-busy="true">
    <div class="spinner-border spinner-border-sm me-2" aria-hidden="true"></div>
    <span>Chargement des menus...</span>
</div>

<div class="row g-4" id="menus-container" aria-live="polite" aria-relevant="additions text"></div>

</div>
