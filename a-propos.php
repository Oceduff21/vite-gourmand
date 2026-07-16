<?php
$pageTitle = 'A propos';
require 'includes/menu-helpers.php';
include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5 mb-5">
            <div class="col-lg-6">
                <h1 class="display-5 fw-bold mb-3">Vite &amp; Gourmand</h1>
                <p class="lead text-muted">Traiteur premium a Bordeaux, specialiste des receptions sur mesure depuis plus de 15 ans.</p>
                <p>Nous concevons des experiences gastronomiques elegantes pour mariages, seminaires, fetes privees et evenements d'entreprise. Chaque menu est compose avec des produits frais, locaux et de saison.</p>
                <a href="menus.php" class="btn btn-warning btn-lg mt-2">Decouvrir nos menus</a>
            </div>
            <div class="col-lg-6">
                <img src="<?= htmlspecialchars(assetImageUrl('assets/images/chef.jpg')) ?>" alt="Notre chef" class="img-fluid rounded-4 shadow">
            </div>
        </div>

        <div class="row g-4 text-center mb-5">
            <div class="col-md-3">
                <div class="stat-card p-4">
                    <div class="stat-number">15+</div>
                    <div class="text-muted">Annees d'experience</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-4">
                    <div class="stat-number">1200+</div>
                    <div class="text-muted">Evenements servis</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-4">
                    <div class="stat-number">14</div>
                    <div class="text-muted">Menus thematiques</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card p-4">
                    <div class="stat-number">4.8/5</div>
                    <div class="text-muted">Satisfaction clients</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <i class="fa-solid fa-leaf fa-2x text-warning mb-3"></i>
                    <h4>Produits responsables</h4>
                    <p class="text-muted mb-0">Circuit court, saisonnalite et respect des regimes alimentaires (vegan, halal, sans gluten).</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <i class="fa-solid fa-champagne-glasses fa-2x text-warning mb-3"></i>
                    <h4>Service evenementiel</h4>
                    <p class="text-muted mb-0">Livraison, mise en place, service et vaisselle sur demande pour un evenement sans stress.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <i class="fa-solid fa-wand-magic-sparkles fa-2x text-warning mb-3"></i>
                    <h4>Personnalisation</h4>
                    <p class="text-muted mb-0">3 choix par categorie et par invite : entree, plat, dessert — adaptes a chaque regime.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
