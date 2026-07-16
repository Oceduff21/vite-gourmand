<?php
require 'includes/db.php';

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

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">

    <div class="hero-slide active" style="background-image:url('assets/images/presentation.jpg')"></div>
    <div class="hero-slide" style="background-image:url('assets/images/hero1.jpg')"></div>
    <div class="hero-slide" style="background-image:url('assets/images/hero2.jpg')"></div>
    <div class="hero-slide" style="background-image:url('assets/images/hero3.jpg')"></div>

    <div class="hero-overlay"></div>

    <button class="hero-btn prev" aria-label="Image precedente">&#10094;</button>
    <button class="hero-btn next" aria-label="Image suivante">&#10095;</button>

    <div class="hero-content">
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
        <img src="assets/images/chef.jpg" alt="Chef experimente en cuisine">
        <div class="p-3">
            <h5>Chef experimente</h5>
            <p>15 ans d'experience en gastronomie.</p>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="team-card">
        <img src="assets/images/chef.jpg" alt="Service traiteur">
        <div class="p-3">
            <h5>Service haut de gamme</h5>
            <p>Professionnalisme et discretion.</p>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="team-card">
        <img src="assets/images/presentation.jpg" alt="Organisation evenementielle">
        <div class="p-3">
            <h5>Organisation</h5>
            <p>Accompagnement sur mesure.</p>
        </div>
    </div>
</div>

</div>
</div>
</section>

<!-- MENUS -->
<section class="container py-5">

<h2 class="text-center mb-5">Nos Menus</h2>

<div class="row g-4">

<div class="col-md-4">
    <div class="menu-card">
        <img src="assets/images/menu-noel-entree.jpg" alt="Menu Noel">
        <div class="p-3">
            <h5>Menu Noel</h5>
            <a href="menus.php" class="btn btn-outline-dark w-100">Voir</a>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="menu-card">
        <img src="assets/images/menu-gastronomique.jpg" alt="Menu Mariage">
        <div class="p-3">
            <h5>Menu Mariage</h5>
            <a href="menus.php" class="btn btn-outline-dark w-100">Voir</a>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="menu-card">
        <img src="assets/images/salade.jpg" alt="Menu Vegan">
        <div class="p-3">
            <h5>Menu Vegan</h5>
            <a href="menus.php" class="btn btn-outline-dark w-100">Voir</a>
        </div>
    </div>
</div>

</div>
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

</div>
</section>

<?php include 'includes/footer.php'; ?>
