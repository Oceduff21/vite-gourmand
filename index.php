<?php
$pageTitle = 'Vite & Gourmand — Traiteur premium a Bordeaux';
$pageDescription = 'Traiteur evenementiel haut de gamme a Bordeaux : mariages, seminaires, receptions privees. Menus sur mesure, livraison et service premium.';
require 'includes/db.php';
require 'includes/menu-helpers.php';

$moyenne = 0;
$avisList = [];

try {
    $stmt = $pdo->query("
        SELECT avis.*, users.nom
        FROM avis
        JOIN users ON avis.user_id = users.id
        WHERE is_validated = 1
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $avisList = $stmt->fetchAll();
    $moyenne = (float)$pdo->query("SELECT AVG(note) FROM avis WHERE is_validated = 1")->fetchColumn();
} catch (Throwable $e) {
    $avisList = [];
    $moyenne = 0;
}

$featuredMenus = [];
try {
    $featuredMenus = $pdo->query('SELECT * FROM menus WHERE stock > 0 ORDER BY id LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $featuredMenus = [];
}

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero" aria-label="Presentation Vite et Gourmand">

    <div class="hero-slide active" aria-hidden="true" style="background-image:url('<?= htmlspecialchars(assetImageUrl('assets/images/presentation.jpg')) ?>')"></div>
    <div class="hero-slide" aria-hidden="true" style="background-image:url('<?= htmlspecialchars(assetImageUrl('assets/images/hero1.jpg')) ?>')"></div>
    <div class="hero-slide" aria-hidden="true" style="background-image:url('<?= htmlspecialchars(assetImageUrl('assets/images/hero2.jpg')) ?>')"></div>
    <div class="hero-slide" aria-hidden="true" style="background-image:url('<?= htmlspecialchars(assetImageUrl('assets/images/hero3.jpg')) ?>')"></div>

    <div class="hero-overlay" aria-hidden="true"></div>

    <button type="button" class="hero-btn prev" aria-label="Diapositive precedente">&#10094;</button>
    <button type="button" class="hero-btn next" aria-label="Diapositive suivante">&#10095;</button>

    <div class="hero-content" aria-live="polite" aria-atomic="true">
        <h1 id="hero-title">Une cuisine elegante</h1>

        <p id="hero-text">
            Vite & Gourmand propose des prestations culinaires haut de gamme pour vos evenements prives et professionnels.
        </p>

        <p class="hero-subtext">
            Notre equipe met son savoir-faire au service d'une experience gastronomique unique.
        </p>

        <a href="menus.php" class="btn btn-hero mt-3">
            Decouvrir nos menus
        </a>
    </div>

</section>

<!-- EQUIPE -->
<section class="bg-light py-5">
<div class="container text-center">

<h2 class="mb-5">Une equipe professionnelle</h2>

<div class="row g-4">

<div class="col-md-4">
    <div class="team-card">
        <img src="<?= htmlspecialchars(assetImageUrl('assets/images/chef.jpg')) ?>" alt="Chef experimente en cuisine">
        <div class="p-3">
            <h5>Chef experimente</h5>
            <p>15 ans d'experience en gastronomie.</p>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="team-card">
        <img src="<?= htmlspecialchars(assetImageUrl('assets/images/chef.jpg')) ?>" alt="Service traiteur">
        <div class="p-3">
            <h5>Service haut de gamme</h5>
            <p>Professionnalisme et discretion.</p>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="team-card">
        <img src="<?= htmlspecialchars(assetImageUrl('assets/images/presentation.jpg')) ?>" alt="Organisation evenementielle">
        <div class="p-3">
            <h5>Organisation</h5>
            <p>Accompagnement sur mesure.</p>
        </div>
    </div>
</div>

</div>
</div>
</section>

<!-- CHIFFRES CLES -->
<section class="py-5 bg-white">
<div class="container">
<div class="row g-4 text-center">
<div class="col-6 col-md-3"><div class="stat-card p-4"><div class="stat-number">15+</div><div class="text-muted small">Ans d'experience</div></div></div>
<div class="col-6 col-md-3"><div class="stat-card p-4"><div class="stat-number">14</div><div class="text-muted small">Menus thematiques</div></div></div>
<div class="col-6 col-md-3"><div class="stat-card p-4"><div class="stat-number"><?= $moyenne ? round($moyenne, 1) : '4.8' ?></div><div class="text-muted small">Note moyenne</div></div></div>
<div class="col-6 col-md-3"><div class="stat-card p-4"><div class="stat-number">48h</div><div class="text-muted small">Devis gratuit</div></div></div>
</div>
<div class="text-center mt-4">
<a href="avis.php" class="btn btn-outline-dark me-2">Tous les avis clients</a>
<a href="a-propos.php" class="btn btn-outline-dark me-2">Notre histoire</a>
<a href="faq.php" class="btn btn-outline-dark">Questions frequentes</a>
</div>
</div>
</section>

<!-- MENUS -->
<section class="container py-5">

<h2 class="text-center mb-5">Nos menus phares</h2>

<?php if (empty($featuredMenus)): ?>
<p class="text-center text-muted">Catalogue en cours de mise a jour.</p>
<?php else: ?>
<div class="row g-4">
<?php foreach ($featuredMenus as $menu): ?>
<div class="col-md-6 col-lg-4">
    <div class="menu-card h-100 shadow-sm">
        <img src="<?= htmlspecialchars(menuCoverUrl($menu)) ?>" alt="<?= htmlspecialchars($menu['titre']) ?>" class="card-img-top" style="height:180px;object-fit:cover" loading="lazy">
        <div class="p-3 d-flex flex-column">
            <h5><?= htmlspecialchars($menu['titre']) ?></h5>
            <p class="text-muted small flex-grow-1 mb-2"><?= htmlspecialchars(formatMenuExcerpt($menu['description'] ?? '')) ?></p>
            <p class="mb-1 small"><i class="fa-solid fa-users me-1"></i> Min. <?= (int)$menu['min_personnes'] ?> pers.</p>
            <p class="text-danger fw-bold mb-3"><?= number_format((float)$menu['prix'], 2) ?> EUR / pers.</p>
            <a href="menu.php?id=<?= (int)$menu['id'] ?>" class="btn btn-outline-dark w-100 mt-auto">Composer ce menu</a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<div class="text-center mt-4">
    <a href="menus.php" class="btn btn-dark">Voir tous les menus</a>
</div>
<?php endif; ?>
</section>

<!-- AVIS -->
<section class="bg-dark text-white py-5">
<div class="container text-center">

<h2 class="mb-3">Avis clients</h2>

<p class="mb-5"><?= $moyenne ? round($moyenne, 1) : 0 ?> / 5</p>

<div class="row g-4">

<?php foreach ($avisList as $a): ?>
<div class="col-md-4">
    <div class="review-card">
        <p>"<?= htmlspecialchars($a['commentaire'] ?? '') ?>"</p>
        <h6><?= str_repeat('*', (int)($a['note'] ?? 0)) ?> - <?= htmlspecialchars($a['nom'] ?? '') ?></h6>
    </div>
</div>
<?php endforeach; ?>

</div>

<div class="text-center mt-4">
<a href="avis.php" class="btn btn-outline-light btn-sm">Voir tous les avis</a>
</div>

</div>
</section>

<?php include 'includes/footer.php'; ?>
