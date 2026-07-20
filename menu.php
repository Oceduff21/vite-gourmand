<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/menu-helpers.php';
require 'includes/user-helpers.php';
require 'includes/flash.php';
require_once __DIR__ . '/back/autoload.php';

use ViteGourmand\Models\Menu;

$id = (int)($_GET['id'] ?? $_POST['menu_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_favori') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=' . urlencode('menu.php?id=' . $id));
        exit();
    }
    $redirect = 'menu.php?id=' . $id;
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('Session expiree. Rechargez la page et reessayez.');
        header('Location: ' . $redirect);
        exit();
    }
    $menuId = (int)($_POST['menu_id'] ?? 0);
    if ($menuId > 0) {
        $result = toggleUserFavori($pdo, (int)$_SESSION['user_id'], $menuId);
        if ($result === null) {
            setFlash('Favoris indisponibles : executez migration-user-space.sql dans phpMyAdmin.');
        } elseif ($result) {
            setFlash('Menu ajoute a vos favoris.');
        } else {
            setFlash('Menu retire de vos favoris.');
        }
    }
    header('Location: ' . $redirect);
    exit();
}

$id = (int)($_GET['id'] ?? 0);

$menuModel = Menu::findById($pdo, $id);
$menu = $menuModel?->toArray();

if (!$menu) {
    http_response_code(404);
    $pageTitle = 'Menu introuvable — Vite & Gourmand';
    include 'includes/header.php';
    echo '<div class="container py-5"><div class="alert alert-warning">Ce menu n\'existe pas ou n\'est plus disponible.</div>';
    echo '<a href="menus.php" class="btn btn-primary">Retour aux menus</a></div>';
    include 'includes/footer.php';
    exit();
}

$prix = $menuModel->prix();
$min = $menuModel->minPersonnes();
$stock = (int)($menu['stock'] ?? 0);
$delai = (int)($menu['delai_jours'] ?? 7);
$group = getMenuPlatsByType($pdo, $id);
$hasOptions = array_sum(array_map('count', $group)) > 0;
$boissonsByCat = getMenuBoissons($pdo, $id);
$boissonLabels = getBoissonCategories();
$hasBoissons = !empty($boissonsByCat);
$isFavori = isset($_SESSION['user_id']) ? isMenuFavori($pdo, (int)$_SESSION['user_id'], $id) : false;
$isMenuEnfant = stripos($menu['theme'] ?? '', 'enfant') !== false || stripos($menu['titre'] ?? '', 'enfant') !== false;

$menuEnfantInfo = null;
if (!$isMenuEnfant) {
    $stmtEnf = $pdo->query("SELECT id, titre, prix FROM menus WHERE LOWER(theme) = 'enfant' OR LOWER(titre) LIKE '%enfant%' ORDER BY id LIMIT 1");
    $menuEnfantInfo = $stmtEnf->fetch(PDO::FETCH_ASSOC) ?: null;
}

$platNamesJs = [];
foreach ($group as $type => $items) {
    foreach ($items as $item) {
        $platNamesJs[(int)$item['id']] = [
            'nom' => $item['nom'],
            'type' => $type,
        ];
    }
}

$boissonNamesJs = [];
foreach ($boissonsByCat as $items) {
    foreach ($items as $b) {
        $boissonNamesJs[(int)$b['id']] = [
            'nom' => $b['nom'],
            'prix' => (float)$b['prix'],
        ];
    }
}

$wizardSteps = [
    ['id' => 'invites', 'label' => 'Invites', 'num' => 1],
    ['id' => 'entree', 'label' => 'Entrees', 'num' => 2],
    ['id' => 'plat', 'label' => 'Plats', 'num' => 3],
    ['id' => 'dessert', 'label' => 'Desserts', 'num' => 4],
];
if ($hasBoissons) {
    $wizardSteps[] = ['id' => 'boissons', 'label' => 'Boissons', 'num' => count($wizardSteps) + 1];
}
$wizardSteps[] = ['id' => 'recap', 'label' => 'Commande', 'num' => count($wizardSteps) + 1];

$savedCart = null;
if (!empty($_SESSION['menu_cart']) && (int)($_SESSION['menu_cart_menu_id'] ?? 0) === $id) {
    $savedCart = $_SESSION['menu_cart'];
}
$resumeCommande = isset($_GET['resume']) && $_GET['resume'] === 'commande' && isset($_SESSION['user_id']) && !empty($savedCart);
$activeCartTypes = array_values(array_filter(MENU_TYPES, static fn(string $t): bool => !empty($group[$t])));

$galleryImages = getMenuGalleryImages($menu, $group, 10);
$menuPriceLabel = formatMenuPriceLabel($menu);

$pageTitle = htmlspecialchars($menu['titre']) . ' — Menu traiteur | Vite & Gourmand';
$pageDescription = mb_substr(strip_tags($menu['description'] ?? 'Menu traiteur evenementiel a Bordeaux.'), 0, 160);
$pageOgImage = getMenuCoverImage($menu);

include 'includes/header.php';
?>

<div class="container py-5 menu-order-page">
<?php showFlash(); ?>

<?php if (!empty($galleryImages)): ?>
<?php if (function_exists('renderMenuCarousel')): ?>
<?= renderMenuCarousel($galleryImages, 'menuCarousel-' . $id) ?>
<?php else: ?>
<div class="mb-4">
    <img src="<?= htmlspecialchars(assetImageUrl('assets/images/' . $galleryImages[0]['src'])) ?>" class="img-fluid rounded w-100 menu-carousel-img" alt="<?= htmlspecialchars($galleryImages[0]['label']) ?>">
</div>
<?php endif; ?>
<?php endif; ?>

<div class="menu-order-header text-center mb-4">
    <h1><?= htmlspecialchars($menu['titre']) ?></h1>
    <?php if (!empty($menu['description'])): ?>
    <p class="menu-order-lead text-muted mb-3"><?= htmlspecialchars($menu['description']) ?></p>
    <?php endif; ?>
    <p class="menu-price mb-3" aria-label="Tarif"><?= $menuPriceLabel ?></p>
    <ul class="menu-meta-badges list-unstyled d-flex flex-wrap justify-content-center gap-2 mb-0" aria-label="Caracteristiques du menu">
        <?php
        $themeKey = resolveMenuThemeKey($menu);
        $regimeKey = resolveMenuRegimeKey($menu);
        ?>
        <li><span class="badge menu-badge-theme"><?= htmlspecialchars(ucfirst($themeKey)) ?></span></li>
        <li><span class="badge menu-badge-regime"><?= htmlspecialchars(ucfirst($regimeKey)) ?></span></li>
        <li><span class="badge menu-badge-dark">Min. <?= $min ?> personnes</span></li>
        <li><span class="badge menu-badge-info">Delai <?= $delai ?> jours</span></li>
        <li><span class="badge menu-badge-ok">Reduction -10% si +5 pers.</span></li>
        <?php if ($stock > 0): ?>
        <li><span class="badge menu-badge-ok"><?= $stock ?> commande(s) possible(s)</span></li>
        <?php else: ?>
        <li><span class="badge menu-badge-danger">Rupture de stock</span></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id'])): ?>
        <li>
            <form method="POST" action="menu.php?id=<?= $id ?>" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="toggle_favori">
                <input type="hidden" name="menu_id" value="<?= $id ?>">
                <button type="submit" class="btn btn-sm <?= $isFavori ? 'btn-danger' : 'btn-outline-danger' ?>" aria-pressed="<?= $isFavori ? 'true' : 'false' ?>">
                    <i class="fa-<?= $isFavori ? 'solid' : 'regular' ?> fa-heart me-1" aria-hidden="true"></i><?= $isFavori ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>
                </button>
            </form>
        </li>
        <?php endif; ?>
    </ul>
</div>

<?php if ($stock > 0 && $hasOptions): ?>
<a href="#step-invites" class="visually-hidden-focusable btn btn-sm btn-outline-primary d-inline-block mb-4">Aller au formulaire de commande</a>
<?php endif; ?>

<?php if (!empty($menu['conditions'])): ?>
<div class="alert alert-warning border border-warning border-3 shadow-sm menu-conditions-alert mb-4" role="alert">
    <h2 class="h5 alert-heading"><i class="fa-solid fa-triangle-exclamation me-2"></i>Conditions importantes — a lire avant commande</h2>
    <?= nl2br(htmlspecialchars($menu['conditions'])) ?>
</div>
<?php endif; ?>

<?php if ($hasOptions): ?>
<div class="card-custom mb-4">
    <h2 class="h5 mb-3">Composition du menu</h2>
    <div class="row g-3">
        <?php foreach (['entree' => 'Entrees', 'plat' => 'Plats', 'dessert' => 'Desserts'] as $type => $label): ?>
        <?php if (!empty($group[$type])): ?>
        <div class="col-md-4">
            <h3 class="h6 text-uppercase text-muted"><?= $label ?></h3>
            <ul class="list-unstyled small mb-0">
                <?php foreach ($group[$type] as $item): ?>
                <li class="mb-2 pb-2 border-bottom">
                    <strong><?= htmlspecialchars($item['nom']) ?></strong>
                    <?= platBadge($item['regime'] ?? 'classique') ?>
                    <div class="plat-allergenes mt-1"><?= renderAllergenesBadges($item['allergenes'] ?? null) ?></div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($stock <= 0): ?>
<div class="alert alert-danger">Ce menu n'est plus disponible a la commande.</div>
<a href="menus.php" class="btn btn-outline-dark">Retour aux menus</a>

<?php elseif (!$hasOptions): ?>
<div class="alert alert-danger">Ce menu n'a pas encore de plats configures. Contactez le traiteur.</div>
<a href="menus.php" class="btn btn-outline-dark">Retour aux menus</a>

<?php else: ?>
<?php
$cartFlash = $_SESSION['menu_cart_error'] ?? null;
unset($_SESSION['menu_cart_error']);
if ($cartFlash):
?>
<div class="alert alert-danger" role="alert"><?= htmlspecialchars($cartFlash) ?></div>
<?php endif; ?>

<?php if ($resumeCommande && $savedCart): ?>
<div class="alert alert-success" role="status">Connecte — votre selection a ete restauree. Verifiez le recapitulatif puis cliquez sur « Continuer la commande ».</div>
<?php endif; ?>

<div class="menu-wizard card-custom mb-4" id="wizard-stepper">
    <nav class="wizard-stepper d-flex flex-wrap justify-content-center gap-1 gap-md-2" aria-label="Etapes de composition du menu">
        <?php foreach ($wizardSteps as $i => $ws): ?>
        <button type="button" class="wizard-step-btn" data-step="<?= (int)$i ?>" data-target="step-<?= htmlspecialchars($ws['id']) ?>" <?= $i === 0 ? 'data-active="1" aria-current="step"' : '' ?>
            aria-label="Etape <?= (int)$ws['num'] ?> : <?= htmlspecialchars($ws['label']) ?>">
            <span class="step-badge"><?= (int)$ws['num'] ?></span>
            <span class="wizard-step-label"><?= htmlspecialchars($ws['label']) ?></span>
        </button>
        <?php if ($i < count($wizardSteps) - 1): ?>
        <span class="wizard-step-sep d-none d-md-inline" aria-hidden="true">›</span>
        <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</div>

<div class="d-flex justify-content-center mb-4">
    <button type="button" class="btn btn-outline-primary btn-autofill-all">
        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Tout remplir auto
    </button>
</div>

<section id="step-invites" class="wizard-panel card-custom mb-4 menu-guests-card wizard-panel-active">
    <div class="wizard-panel-head">
        <h3 class="h5 mb-1"><span class="step-badge">1</span> Nombre d'invites</h3>
        <p class="text-muted small mb-0">Indiquez combien de personnes seront presentes.</p>
    </div>
    <div class="row align-items-center g-3 mt-2">
        <div class="col-sm-4 col-md-3">
            <label class="form-label small fw-bold" for="invites">Total invites</label>
            <input type="number" id="invites" class="form-control form-control-lg wizard-focus-field" value="<?= $min ?>" min="<?= $min ?>" max="500" autofocus>
        </div>
        <div class="col-sm-8 col-md-9">
            <p class="text-muted mb-0 small">Minimum <strong><?= $min ?></strong> personnes. Ce nombre sera reparti entre les entrees, plats et desserts.</p>
        </div>
    </div>
    <?php if ($menuEnfantInfo && !$isMenuEnfant): ?>
    <div class="row g-3 mt-3 pt-3 border-top">
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="has-enfants">
                <label class="form-check-label" for="has-enfants">Des enfants seront presents (menu enfant separe)</label>
            </div>
        </div>
        <div class="col-sm-4 col-md-3" id="enfants-wrap" style="display:none">
            <label class="form-label small fw-bold" for="nb-enfants">Nombre d'enfants</label>
            <input type="number" id="nb-enfants" class="form-control" value="0" min="0" max="500">
        </div>
        <div class="col-sm-8 col-md-9" id="enfants-info" style="display:none">
            <p class="small text-muted mb-0">
                <i class="fa-solid fa-child me-1"></i>
                <span id="enfants-summary">0</span> menu(x) enfant a <?= number_format((float)$menuEnfantInfo['prix'], 2) ?> EUR
                (<a href="menu.php?id=<?= (int)$menuEnfantInfo['id'] ?>" target="_blank"><?= htmlspecialchars($menuEnfantInfo['titre']) ?></a>)
            </p>
        </div>
    </div>
    <?php endif; ?>
    <div class="wizard-nav mt-4">
        <span></span>
        <button type="button" class="btn btn-primary btn-wizard-next" data-next="step-entree" data-validate="invites">Suite — Entrees <i class="fa-solid fa-arrow-down ms-1"></i></button>
    </div>
</section>

<?php
$labels = ['entree' => 'Entrees', 'plat' => 'Plats', 'dessert' => 'Desserts'];
$stepNums = ['entree' => 2, 'plat' => 3, 'dessert' => 4];
$stepOrder = ['entree', 'plat', 'dessert'];
foreach ($group as $type => $items):
    if (empty($items)) {
        continue;
    }
    $idx = array_search($type, $stepOrder, true);
    $prevStep = $idx > 0 ? 'step-' . $stepOrder[$idx - 1] : 'step-invites';
    $nextStep = $idx < count($stepOrder) - 1 ? 'step-' . $stepOrder[$idx + 1] : ($hasBoissons ? 'step-boissons' : 'step-recap');
?>
<section id="step-<?= $type ?>" class="wizard-panel menu-category mb-4" data-type="<?= $type ?>">
    <div class="wizard-panel-head d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="h5 mb-1"><span class="step-badge"><?= $stepNums[$type] ?></span> <?= $labels[$type] ?></h3>
            <p class="text-muted small mb-0">Repartissez <strong class="guest-count-label"><?= $min ?></strong> invites entre les options.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="category-status" id="status-<?= $type ?>">
                <span class="badge bg-secondary">0 / <?= $min ?></span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary btn-autofill-cat" data-type="<?= $type ?>" title="Repartir en alternant veg, sans gluten/lactose et viande/poisson">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto
            </button>
        </div>
    </div>
    <div class="progress mb-2" style="height:8px">
        <div class="progress-bar" id="progress-<?= $type ?>" role="progressbar" style="width:0%"></div>
    </div>
    <p class="small text-muted mb-3" id="hint-<?= $type ?>">Repartissez les invites entre les options ci-dessous.</p>
    <div class="row g-3 plat-grid">
        <?php foreach ($items as $item): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="plat-select-card" id="card-<?= $type ?>-<?= (int)$item['id'] ?>">
                <div class="plat-img-wrap">
                    <img src="<?= htmlspecialchars(assetImageUrl('assets/images/' . ($item['image'] ?? 'default.jpg'))) ?>" class="plat-img" alt="<?= htmlspecialchars($item['nom']) ?>" loading="lazy">
                </div>
                <div class="plat-card-body">
                    <div class="plat-card-info">
                        <h5 class="plat-card-title"><?= htmlspecialchars($item['nom']) ?></h5>
                        <div class="plat-card-badges"><?= platBadge($item['regime'] ?? 'classique') ?></div>
                        <div class="plat-allergenes mt-1"><?= renderAllergenesBadges($item['allergenes'] ?? null) ?></div>
                        <?php if (!empty($item['description'])): ?>
                        <p class="plat-desc"><?= htmlspecialchars($item['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="plat-card-controls">
                        <label class="form-label small mb-1" for="<?= $type ?>-<?= (int)$item['id'] ?>">Invites</label>
                        <input type="range" class="form-range plat-slider" min="0" max="<?= $min ?>" value="0"
                            id="<?= $type ?>-<?= (int)$item['id'] ?>"
                            aria-valuemin="0" aria-valuemax="<?= $min ?>" aria-valuenow="0" aria-valuetext="0 invite"
                            data-type="<?= $type ?>" data-id="<?= (int)$item['id'] ?>"
                            data-nom="<?= htmlspecialchars($item['nom'], ENT_QUOTES) ?>"
                            data-regime="<?= htmlspecialchars($item['regime'] ?? 'classique', ENT_QUOTES) ?>">
                        <div class="plat-qty"><span id="label-<?= $type ?>-<?= (int)$item['id'] ?>">0</span> invite(s)</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="wizard-nav mt-4">
        <button type="button" class="btn btn-outline-secondary btn-wizard-prev" data-prev="<?= $prevStep ?>"><i class="fa-solid fa-arrow-up me-1"></i> Precedent</button>
        <button type="button" class="btn btn-primary btn-wizard-next" data-next="<?= $nextStep ?>" data-validate="<?= $type ?>">
            Suite<?= $nextStep === 'step-recap' ? ' — Recapitulatif' : '' ?> <i class="fa-solid fa-arrow-down ms-1"></i>
        </button>
    </div>
</section>
<?php endforeach; ?>

<?php if ($hasBoissons): ?>
<section id="step-boissons" class="wizard-panel menu-category mb-4">
    <div class="wizard-panel-head mb-3">
        <h3 class="h5 mb-1"><span class="step-badge"><?= count($wizardSteps) - 1 ?></span> Boissons</h3>
        <p class="text-muted small mb-0">Optionnel — selectionnez les quantites souhaitees.</p>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover boisson-list align-middle mb-0">
            <thead class="table-light">
                <tr><th>Boisson</th><th>Categorie</th><th class="text-end">Prix unit.</th><th style="width:100px">Quantite</th></tr>
            </thead>
            <tbody>
            <?php foreach ($boissonsByCat as $cat => $items): ?>
                <?php foreach ($items as $b): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($b['nom']) ?></strong>
                        <?php if (!empty($b['description'])): ?><br><span class="small text-muted"><?= htmlspecialchars($b['description']) ?></span><?php endif; ?>
                    </td>
                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($boissonLabels[$cat] ?? ucfirst($cat)) ?></span></td>
                    <td class="text-end"><?= number_format((float)$b['prix'], 2) ?> &euro;</td>
                    <td>
                        <input type="number" class="form-control form-control-sm boisson-qty" min="0" max="999" value="0"
                            id="boisson-<?= (int)$b['id'] ?>" data-id="<?= (int)$b['id'] ?>"
                            data-nom="<?= htmlspecialchars($b['nom'], ENT_QUOTES) ?>"
                            data-price="<?= (float)$b['prix'] ?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="wizard-nav mt-3">
        <button type="button" class="btn btn-outline-secondary btn-wizard-prev" data-prev="step-dessert"><i class="fa-solid fa-arrow-up me-1"></i> Precedent</button>
        <button type="button" class="btn btn-primary btn-wizard-next" data-next="step-recap" data-validate="boissons">Suite — Recapitulatif <i class="fa-solid fa-arrow-down ms-1"></i></button>
    </div>
</section>
<?php endif; ?>

<section id="step-recap" class="wizard-panel card-custom mb-5">
    <div class="wizard-panel-head mb-3">
        <h3 class="h5 mb-1"><span class="step-badge"><?= count($wizardSteps) ?></span> Recapitulatif</h3>
        <p class="text-muted small mb-0">Verifiez votre selection avant de continuer la commande.</p>
        <p class="small text-muted mb-0 mt-1"><i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Les allergenes sont indiques sur chaque plat. Signalez toute intolerance lors de la commande.</p>
    </div>
    <div id="recap-detail" class="recap-detail mb-4"></div>
    <div id="validation-msg" class="alert alert-warning py-2 small d-none mb-3" role="alert" aria-live="assertive"></div>
    <div class="recap-totals p-3 bg-light rounded mb-4">
        <div class="d-flex justify-content-between mb-1"><span>Adultes (<span id="recap-adultes"><?= $min ?></span> pers.)</span><span><span id="recap-adultes-total">0.00</span> EUR</span></div>
        <div class="d-flex justify-content-between mb-1 d-none" id="recap-enfants-line"><span>Enfants (<span id="recap-enfants-n">0</span> pers.)</span><span><span id="recap-enfants-total">0.00</span> EUR</span></div>
        <div class="d-flex justify-content-between mb-1"><span>Boissons</span><span><span id="recap-boissons-total">0.00</span> EUR</span></div>
        <hr class="my-2">
        <div class="d-flex justify-content-between fw-bold fs-5"><span>Total estime</span><span class="text-danger"><span id="total">0.00</span> EUR</span></div>
    </div>
    <div class="wizard-nav">
        <button type="button" class="btn btn-outline-secondary btn-wizard-prev" data-prev="<?= $hasBoissons ? 'step-boissons' : 'step-dessert' ?>"><i class="fa-solid fa-arrow-up me-1"></i> Precedent</button>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary btn-autofill-all">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Tout remplir auto
            </button>
            <button type="button" class="btn btn-success btn-lg" id="btn-commander" aria-disabled="true" aria-describedby="validation-msg">Continuer la commande</button>
        </div>
    </div>
</section>

<div class="wizard-sticky-bar" id="wizard-sticky">
    <div class="container d-flex align-items-center justify-content-between gap-3">
        <div class="small">
            <strong id="sticky-step-label">Etape 1 — Invites</strong>
            <span class="text-muted ms-2">Total : <span id="sticky-total">0.00</span> EUR</span>
        </div>
        <button type="button" class="btn btn-sm btn-primary" id="sticky-next" aria-disabled="true">Suite</button>
    </div>
</div>

<?php endif; ?>
</div>

<?php if ($stock > 0 && $hasOptions): ?>
<!-- BACK injecte la config ; FRONT = menu-wizard.js -->
<script>
const MENU_ID = <?= $id ?>;
const BASE_PRICE = <?= $prix ?>;
const PRIX_ENFANT = <?= $menuEnfantInfo ? (float)$menuEnfantInfo['prix'] : 0 ?>;
const HAS_ENFANT_OPTION = <?= ($menuEnfantInfo && !$isMenuEnfant) ? 'true' : 'false' ?>;
const MIN_GUESTS = <?= $min ?>;
const TYPES = <?= json_encode($activeCartTypes) ?>;
const HAS_BOISSONS = <?= $hasBoissons ? 'true' : 'false' ?>;
const CSRF_TOKEN = <?= json_encode(csrfToken()) ?>;
const WIZARD_STEPS = <?= json_encode(array_column($wizardSteps, 'id')) ?>;
const WIZARD_LABELS = <?= json_encode(array_combine(array_column($wizardSteps, 'id'), array_column($wizardSteps, 'label')), JSON_UNESCAPED_UNICODE) ?>;
const SAVED_CART = <?= json_encode($savedCart ?: null, JSON_UNESCAPED_UNICODE) ?>;
const RESUME_COMMANDE = <?= $resumeCommande ? 'true' : 'false' ?>;
const PLAT_TYPE_LABELS = { entree: 'Entrees', plat: 'Plats', dessert: 'Desserts' };
</script>
<script src="front/js/menu-wizard.js?v=20260720c"></script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
