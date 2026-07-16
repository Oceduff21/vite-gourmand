<?php
$pageTitle = 'FAQ';
include 'includes/header.php';

$faqs = [
    ['q' => 'Quel est le delai minimum pour commander ?', 'a' => 'Chaque menu indique un delai (generalement 5 a 21 jours). La date de livraison est calculee automatiquement lors de la commande.'],
    ['q' => 'Comment fonctionne la selection des plats ?', 'a' => 'Pour chaque invite, repartissez les choix entre 3 entrees, 3 plats et 3 desserts proposes. Vous pouvez utiliser la repartition automatique.'],
    ['q' => 'Proposez-vous des menus vegan ou sans gluten ?', 'a' => 'Oui. Chaque menu propose des options classiques, vegetariennes, vegan, sans gluten, sans lactose, halal ou pescetariennes selon le theme.'],
    ['q' => 'La livraison est-elle incluse ?', 'a' => 'La livraison est gratuite a Bordeaux. Hors Bordeaux, un forfait kilometrique s\'applique selon votre code postal.'],
    ['q' => 'Puis-je modifier ou annuler ma commande ?', 'a' => 'Depuis votre espace client, vous pouvez suivre, modifier (selon delai) ou annuler une commande en attente.'],
    ['q' => 'Les boissons sont-elles incluses ?', 'a' => 'Les boissons sont en supplement. Vous les selectionnez lors de la composition du menu, par categorie (vins, softs, champagne...).'],
];
?>

<section class="py-5">
    <div class="container">
        <h1 class="text-center mb-5">Questions frequentes</h1>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <?php foreach ($faqs as $i => $faq): ?>
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq-<?= $i ?>">
                                <?= htmlspecialchars($faq['q']) ?>
                            </button>
                        </h2>
                        <div id="faq-<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted"><?= htmlspecialchars($faq['a']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-5">
                    <p class="text-muted">Une autre question ?</p>
                    <a href="contact.php" class="btn btn-dark">Nous contacter</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
