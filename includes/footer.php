</main> <!-- fermeture main -->

<footer class="footer">

<div class="container">

<div class="row gy-4">

<!-- ABOUT -->
<div class="col-md-3">
<h5 class="footer-title">Vite & Gourmand</h5>
<p>
Entreprise familiale spécialisée dans la restauration événementielle.<br>
Des menus raffinés pour tous vos événements.
</p>
</div>

<!-- LIENS -->
<div class="col-md-3">
<h5 class="footer-title">Liens utiles</h5>
<ul class="list-unstyled footer-links">
<li><a href="menus.php">Nos menus</a></li>
<li><a href="contact.php">Contact</a></li>
<li><a href="faq.php">FAQ</a></li>
<li><a href="politique-confidentialite.php">Politique de confidentialite</a></li>
<li><a href="accessibilite.php">Accessibilite</a></li>
<li><a href="mentions-legales.php">Mentions légales</a></li>
<li><a href="cgv.php">CGV</a></li>
</ul>
</div>

<div class="col-md-3">
<h5 class="footer-title">Horaires</h5>
<?php
require_once __DIR__ . '/site-settings.php';
foreach (getFooterHoraires() as $ligneHoraire):
?>
<p class="mb-1"><?= htmlspecialchars($ligneHoraire) ?></p>
<?php endforeach; ?>
</div>

<!-- CONTACT -->
<div class="col-md-3">
<h5 class="footer-title">Contact</h5>
<p>
<span class="visually-hidden">Adresse : </span>11 Rue Verteuil 33000 Bordeaux, France<br>
<span class="visually-hidden">Telephone : </span><a href="tel:+33412345678" class="text-decoration-none" style="color:inherit">04 12 34 56 78</a><br>
<span class="visually-hidden">Email : </span><a href="mailto:contact@vite-gourmand.fr" class="text-decoration-none" style="color:inherit">contact@vite-gourmand.fr</a>
</p>
</div>

<!-- MAP -->
<div class="col-md-3">
<h5 class="footer-title">Localisation</h5>
<p class="small mb-2">
    <a href="https://www.google.com/maps/search/?api=1&amp;query=11+Rue+Verteuil+33000+Bordeaux" class="text-decoration-none" style="color:inherit">
        Voir l'adresse sur Google Maps
    </a>
</p>
<iframe
    title="Carte interactive — 11 Rue Verteuil, 33000 Bordeaux"
    src="https://www.google.com/maps?q=11+Rue+Verteuil+33000+Bordeaux&amp;output=embed"
    width="100%"
    height="150"
    style="border:0; border-radius:10px;"
    loading="lazy">
</iframe>

</div>

</div>

<hr>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center">

<p class="mb-0">
© <?= date('Y') ?> Vite & Gourmand
</p>

<p class="mb-0 small footer-credits">
Site realise pour Vite &amp; Gourmand
</p>

</div>

</div>

</footer>

<nav class="scroll-jump-nav" aria-label="Navigation rapide sur la page">
    <button type="button" id="scroll-to-top" class="scroll-jump-btn" hidden aria-label="Remonter en haut de la page">
        <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
    </button>
    <button type="button" id="scroll-to-bottom" class="scroll-jump-btn" hidden aria-label="Aller en bas de la page">
        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
    </button>
</nav>
<div id="page-end" class="visually-hidden" tabindex="-1">Fin de la page</div>

<!-- COOKIE BANNER -->
<div id="cookie-banner" class="cookie-banner" role="region" aria-label="Information sur les cookies" hidden>
    <div class="cookie-content">
        <p id="cookie-banner-text">Ce site utilise des cookies pour ameliorer votre experience de navigation.</p>
        <div class="cookie-buttons">
            <button type="button" id="accept-cookies" class="btn btn-success btn-sm">Accepter</button>
            <button type="button" id="refuse-cookies" class="btn btn-outline-light btn-sm">Refuser</button>
        </div>
    </div>
</div>

<!-- SCRIPTS FRONT (HTML/JS separes du back PHP) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="front/js/site.js?v=20260720c"></script>
<script src="front/js/scroll-jump.js?v=20260720c"></script>

</body>
</html>
