<?php
$pageTitle = 'Accessibilite — Vite & Gourmand';
$pageDescription = 'Declaration et engagement accessibilite numerique du site Vite et Gourmand.';
include 'includes/header.php';
?>

<div class="container py-5" id="main-content">
    <h1 class="mb-4">Accessibilite numerique</h1>

    <p class="lead">Vite &amp; Gourmand s'efforce de rendre son site accessible au plus grand nombre, conformement au referentiel general d'amelioration de l'accessibilite (RGAA).</p>

    <h2 class="h4 mt-4">Mesures mises en place</h2>
    <ul>
        <li>Structure HTML semantique et langue du document (<code>lang="fr"</code>)</li>
        <li>Lien d'evitement vers le contenu principal</li>
        <li>Navigation au clavier avec focus visible</li>
        <li>Formulaires avec etiquettes explicites liees aux champs</li>
        <li>Textes alternatifs sur les images informatives</li>
        <li>Attributs ARIA sur les composants dynamiques (wizard menu, alertes, modales)</li>
        <li>Reduction des animations si l'utilisateur a active <em>prefers-reduced-motion</em></li>
        <li>Graphiques accompagnes de tableaux de donnees accessibles</li>
        <li>Notes etoiles avec alternative textuelle</li>
    </ul>

    <h2 class="h4 mt-4">Etat de conformite</h2>
    <p>
        Le site a fait l'objet d'un audit interne RGAA (structure, formulaires, tableaux, graphiques, navigation clavier).
        La conformite est estimee a environ <strong>90&nbsp;%</strong> sur le site public et le back-office.
        Un audit certificateur externe sur l'ensemble des 106 criteres n'a pas ete realise.
        Des non-conformites residuelles peuvent subsister (contrastes ponctuels, contenus tiers embedes).
    </p>

    <h2 class="h4 mt-4">Signaler un probleme</h2>
    <p>
        Si vous rencontrez un obstacle a l'accessibilite, contactez-nous :
        <a href="mailto:contact@vite-gourmand.fr">contact@vite-gourmand.fr</a>
        ou via le <a href="contact.php">formulaire de contact</a>.
    </p>

    <p class="text-muted small mt-4">Derniere mise a jour : <?= date('d/m/Y') ?></p>
</div>

<?php include 'includes/footer.php'; ?>
