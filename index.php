<?php include 'includes/header.php'; ?>
<?php require 'includes/db.php'; ?>

<?php
$avis = $pdo->query("
SELECT avis.*, users.nom 
FROM avis
JOIN users ON avis.user_id = users.id
WHERE is_validated = 1
ORDER BY created_at DESC
LIMIT 3
");

$moyenne = $pdo->query("
SELECT AVG(note) FROM avis WHERE is_validated = 1
")->fetchColumn();
?>

<!-- HERO -->
<section class="hero">

    <div class="hero-slide active" style="background-image:url('assets/images/presentation.jpg')"></div>
    <div class="hero-slide" style="background-image:url('assets/images/hero1.jpg')"></div>
    <div class="hero-slide" style="background-image:url('assets/images/hero2.jpg')"></div>
    <div class="hero-slide" style="background-image:url('assets/images/hero3.jpg')"></div>

    <div class="hero-overlay"></div>

    <button class="hero-btn prev" aria-label="Image precedente">❮</button>
    <button class="hero-btn next" aria-label="Image suivante">❯</button>

    <div class="hero-content">
        <h1 id="hero-title">Une cuisine élégante</h1>

        <p id="hero-text">
            Vite & Gourmand propose des prestations culinaires haut de gamme pour vos événements privés et professionnels.
        </p>

        <p class="hero-subtext">
            Notre équipe met son savoir-faire au service d’une expérience gastronomique unique.
        </p>

        <a href="menus.php" class="btn btn-hero mt-3">
            Découvrir nos menus
        </a>
    </div>

</section>

<!-- EQUIPE -->
<section class="bg-light py-5">
<div class="container text-center">

<h2 class="mb-5">Une équipe professionnelle</h2>

<div class="row g-4">

<div class="col-md-4">
    <div class="team-card">
        <img src="assets/images/chef.jpg" alt="Chef experimente en cuisine">
        <div class="p-3">
            <h5>Chef expérimenté</h5>
            <p>15 ans d'expérience en gastronomie.</p>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="team-card">
        <img src="assets/images/chef.jpg" alt="Photo equipe Vite et Gourmand">
        <div class="p-3">
            <h5>Service haut de gamme</h5>
            <p>Professionnalisme et discrétion.</p>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="team-card">
        <img src="assets/images/presentation.jpg" alt="Photo equipe Vite et Gourmand">
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
        <img src="assets/images/menu-noel-entree.jpg" alt="Photo equipe Vite et Gourmand">
        <div class="p-3">
            <h5>Menu Noël</h5>
            <a href="menus.php" class="btn btn-outline-dark w-100">Voir</a>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="menu-card">
        <img src="assets/images/menu-gastronomique.jpg" alt="Photo equipe Vite et Gourmand">
        <div class="p-3">
            <h5>Menu Mariage</h5>
            <a href="menus.php" class="btn btn-outline-dark w-100">Voir</a>
        </div>
    </div>
</div>

<div class="col-md-4">
    <div class="menu-card">
        <img src="assets/images/salade.jpg" alt="Photo equipe Vite et Gourmand">
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

<p class="mb-5"><?= $moyenne ? round($moyenne,1) : 0 ?> ⭐ sur 5</p>

<div class="row g-4">

<?php foreach($avis as $a): ?>
<div class="col-md-4">
    <div class="review-card">
        <p>"<?= htmlspecialchars($a['commentaire']) ?>"</p>
        <h6><?= str_repeat("⭐", $a['note']) ?> - <?= $a['nom'] ?></h6>
    </div>
</div>
<?php endforeach; ?>

</div>

</div>
</section>

<?php include 'includes/footer.php'; ?>