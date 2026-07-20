/**
 * FRONT — menus-filter.js
 * Filtres catalogue : appelle l'API AJAX back-end api/load-menus.php
 * Separation claire : JS = presentation / PHP API = donnees + HTML fragment.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const search = document.getElementById('search-menu');
        const theme = document.getElementById('filtre-theme');
        const regime = document.getElementById('filtre-regime');
        const prixMin = document.getElementById('prix-min');
        const prixMax = document.getElementById('prix-max');
        const personnes = document.getElementById('filtre-personnes');
        const sort = document.getElementById('sort');
        const container = document.getElementById('menus-container');
        const loading = document.getElementById('menus-loading');
        const resetBtn = document.getElementById('btn-reset-filters');

        if (!container || !search) return;

        // Endpoint AJAX back-end (fragment HTML)
        const API_URL = 'api/load-menus.php';
        let loadTimer = null;

        function loadMenus() {
            clearTimeout(loadTimer);
            loadTimer = setTimeout(() => {
                loading.classList.remove('d-none');
                loading.setAttribute('aria-busy', 'true');
                const params = new URLSearchParams({
                    search: search.value.trim(),
                    theme: theme.value,
                    regime: regime.value,
                    prix_min: prixMin.value,
                    prix_max: prixMax.value,
                    personnes: personnes.value,
                    sort: sort.value
                });

                fetch(API_URL + '?' + params.toString())
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.text();
                    })
                    .then(html => { container.innerHTML = html; })
                    .catch(() => {
                        container.innerHTML = '<div class="col-12"><p class="text-center text-danger py-5" role="alert">Impossible de charger les menus. Rechargez la page.</p></div>';
                    })
                    .finally(() => {
                        loading.classList.add('d-none');
                        loading.setAttribute('aria-busy', 'false');
                    });
            }, 250);
        }

        function resetFilters() {
            search.value = '';
            theme.value = '';
            regime.value = '';
            prixMin.value = '';
            prixMax.value = '';
            personnes.value = '';
            sort.value = '';
            loadMenus();
            search.focus();
        }

        document.querySelectorAll('#search-menu, #filtre-theme, #filtre-regime, #prix-min, #prix-max, #filtre-personnes, #sort')
            .forEach(el => el.addEventListener('input', loadMenus));

        if (resetBtn) resetBtn.addEventListener('click', resetFilters);

        loadMenus();
    });
})();
