<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/menu-helpers.php';
require 'includes/user-helpers.php';
require 'includes/flash.php';

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

$stmt = $pdo->prepare('SELECT * FROM menus WHERE id = ?');
$stmt->execute([$id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$menu) {
    http_response_code(404);
    $pageTitle = 'Menu introuvable — Vite & Gourmand';
    include 'includes/header.php';
    echo '<div class="container py-5"><div class="alert alert-warning">Ce menu n\'existe pas ou n\'est plus disponible.</div>';
    echo '<a href="menus.php" class="btn btn-primary">Retour aux menus</a></div>';
    include 'includes/footer.php';
    exit();
}

$prix = (float)$menu['prix'];
$min = (int)$menu['min_personnes'];
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

let cart = { invites: MIN_GUESTS, enfants: 0, entree: {}, plat: {}, dessert: {}, boissons: {} };
let currentStepIndex = 0;

function sumBoissons() {
    let total = 0;
    document.querySelectorAll('.boisson-qty').forEach(input => {
        const qty = parseInt(input.value, 10) || 0;
        total += qty * (parseFloat(input.dataset.price) || 0);
    });
    return total;
}

function syncBoissonsCart() {
    cart.boissons = {};
    document.querySelectorAll('.boisson-qty').forEach(input => {
        const qty = parseInt(input.value, 10) || 0;
        if (qty > 0) cart.boissons[input.dataset.id] = qty;
    });
}

function sumType(type) {
    return Object.values(cart[type] || {}).reduce((a, b) => a + b, 0);
}

function sumTypeExcept(type, excludeId) {
    let sum = 0;
    document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(s => {
        if (String(s.dataset.id) !== String(excludeId)) {
            sum += parseInt(s.value, 10) || 0;
        }
    });
    return sum;
}

function updateSliderLimits(type) {
    document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(s => {
        const id = s.dataset.id;
        const others = sumTypeExcept(type, id);
        const maxAllowed = Math.max(0, cart.invites - others);
        s.max = maxAllowed;

        let val = parseInt(s.value, 10) || 0;
        if (val > maxAllowed) {
            val = maxAllowed;
            s.value = val;
            if (val <= 0) delete cart[type][id];
            else cart[type][id] = val;
            const lbl = document.getElementById('label-' + type + '-' + id);
            if (lbl) lbl.textContent = val;
        }

        const card = document.getElementById('card-' + type + '-' + id);
        if (card) card.classList.toggle('plat-slider-capped', maxAllowed === 0 && val === 0);
    });
}

function updateAllSliderLimits() {
    TYPES.forEach(updateSliderLimits);
}

function syncSlidersMax() {
    document.querySelectorAll('.guest-count-label').forEach(el => { el.textContent = cart.invites; });
    const rg = document.getElementById('recap-guests');
    if (rg) rg.textContent = cart.invites;
    const ra = document.getElementById('recap-adultes');
    if (ra) ra.textContent = Math.max(0, cart.invites - cart.enfants);
    updateAllSliderLimits();
}

function getRegimeBucket(regime) {
    const r = (regime || 'classique').toLowerCase();
    if (['vegan', 'vegetarien'].includes(r)) return 'veg';
    if (['sans gluten', 'sans lactose', 'halal'].includes(r)) return 'diet';
    return 'protein';
}

function getOrderedSliders(type) {
    const sliders = Array.from(document.querySelectorAll('.plat-slider[data-type="' + type + '"]'));
    const buckets = { veg: [], diet: [], protein: [] };
    sliders.forEach(s => buckets[getRegimeBucket(s.dataset.regime)].push(s));
    const ordered = [];
    const maxB = Math.max(buckets.veg.length, buckets.diet.length, buckets.protein.length, 1);
    for (let i = 0; i < maxB; i++) {
        if (buckets.veg[i]) ordered.push(buckets.veg[i]);
        if (buckets.diet[i]) ordered.push(buckets.diet[i]);
        if (buckets.protein[i]) ordered.push(buckets.protein[i]);
    }
    return ordered.length ? ordered : sliders;
}

function applyDistribution(type, ordered, total) {
    cart[type] = {};
    ordered.forEach(s => { s.value = 0; });
    let i = 0;
    while (total > 0 && ordered.length) {
        const s = ordered[i % ordered.length];
        s.value = (parseInt(s.value, 10) || 0) + 1;
        total--;
        i++;
    }
    ordered.forEach(s => {
        const val = parseInt(s.value, 10) || 0;
        const id = s.dataset.id;
        if (val <= 0) delete cart[type][id];
        else cart[type][id] = val;
        const lbl = document.getElementById('label-' + type + '-' + id);
        if (lbl) lbl.textContent = val;
    });
    updateAllSliderLimits();
}

function syncInvitesFromInput() {
    const inp = document.getElementById('invites');
    if (!inp) return;
    cart.invites = Math.max(MIN_GUESTS, parseInt(inp.value, 10) || MIN_GUESTS);
    inp.value = cart.invites;
    if (cart.enfants > cart.invites) {
        cart.enfants = cart.invites;
        const nbE = document.getElementById('nb-enfants');
        if (nbE) nbE.value = cart.enfants;
    }
    syncSlidersMax();
}

function restoreSavedCart(saved) {
    if (!saved || typeof saved !== 'object') return false;

    cart.invites = Math.max(MIN_GUESTS, parseInt(saved.invites, 10) || MIN_GUESTS);
    const invitesInput = document.getElementById('invites');
    if (invitesInput) invitesInput.value = cart.invites;

    cart.enfants = Math.min(cart.invites, Math.max(0, parseInt(saved.enfants, 10) || 0));
    if (HAS_ENFANT_OPTION) {
        const hasEnfants = document.getElementById('has-enfants');
        const nbEnfants = document.getElementById('nb-enfants');
        if (hasEnfants) hasEnfants.checked = cart.enfants > 0;
        if (nbEnfants) nbEnfants.value = cart.enfants;
    }

    cart.entree = {};
    cart.plat = {};
    cart.dessert = {};
    cart.boissons = {};
    document.querySelectorAll('.plat-slider').forEach(s => { s.value = 0; });
    document.querySelectorAll('.boisson-qty').forEach(input => { input.value = 0; });

    TYPES.forEach(type => {
        if (!saved[type] || typeof saved[type] !== 'object') return;
        Object.entries(saved[type]).forEach(([platId, qty]) => {
            qty = parseInt(qty, 10) || 0;
            if (qty <= 0) return;
            const slider = document.getElementById(type + '-' + platId);
            if (!slider) return;
            slider.value = qty;
            cart[type][platId] = qty;
            const lbl = document.getElementById('label-' + type + '-' + platId);
            if (lbl) lbl.textContent = qty;
        });
    });

    if (saved.boissons && typeof saved.boissons === 'object') {
        Object.entries(saved.boissons).forEach(([boissonId, qty]) => {
            qty = parseInt(qty, 10) || 0;
            if (qty <= 0) return;
            const input = document.querySelector('.boisson-qty[data-id="' + boissonId + '"]');
            if (!input) return;
            input.value = qty;
            cart.boissons[boissonId] = qty;
        });
    }

    syncSlidersMax();
    updateUI();
    return true;
}

function autoFillCategory(type) {
    applyDistribution(type, getOrderedSliders(type), cart.invites);
    updateUI();
}

function syncEnfantsUI() {
    if (!HAS_ENFANT_OPTION) return;
    const has = document.getElementById('has-enfants')?.checked;
    const wrap = document.getElementById('enfants-wrap');
    const info = document.getElementById('enfants-info');
    if (wrap) wrap.style.display = has ? '' : 'none';
    if (info) info.style.display = has ? '' : 'none';
    if (!has) {
        cart.enfants = 0;
        const inp = document.getElementById('nb-enfants');
        if (inp) inp.value = 0;
    } else {
        cart.enfants = Math.min(cart.invites, parseInt(document.getElementById('nb-enfants')?.value, 10) || 0);
    }
    const sum = document.getElementById('enfants-summary');
    if (sum) sum.textContent = cart.enfants;
    const line = document.getElementById('recap-enfants-line');
    if (line) line.classList.toggle('d-none', cart.enfants <= 0);
}

function updateChoice(type, id, val) {
    val = parseInt(val, 10) || 0;
    const maxAllowed = Math.max(0, cart.invites - sumTypeExcept(type, id));
    if (val > maxAllowed) val = maxAllowed;

    const slider = document.getElementById(type + '-' + id);
    if (slider) {
        slider.value = val;
        slider.setAttribute('aria-valuenow', String(val));
        slider.setAttribute('aria-valuetext', val + (val > 1 ? ' invites' : ' invite'));
    }

    if (val <= 0) delete cart[type][id];
    else cart[type][id] = val;

    const lbl = document.getElementById('label-' + type + '-' + id);
    if (lbl) lbl.textContent = val;

    updateAllSliderLimits();
    updateUI();
}

function validateStep(stepId) {
    if (stepId === 'invites') {
        return cart.invites >= MIN_GUESTS && cart.enfants >= 0 && cart.enfants <= cart.invites;
    }
    if (TYPES.includes(stepId)) {
        return sumType(stepId) === cart.invites;
    }
    if (stepId === 'boissons') return true;
    if (stepId === 'recap') {
        return TYPES.every(t => sumType(t) === cart.invites);
    }
    return true;
}

function stepMessage(stepId) {
    if (stepId === 'invites') return 'Indiquez au moins ' + MIN_GUESTS + ' invites (enfants inclus, max ' + cart.invites + ').';
    if (TYPES.includes(stepId)) {
        const sum = sumType(stepId);
        return 'Repartissez exactement ' + cart.invites + ' invites pour les ' + (PLAT_TYPE_LABELS[stepId] || stepId).toLowerCase() + ' (' + sum + '/' + cart.invites + ').';
    }
    return 'Completez les etapes precedentes.';
}

function buildDetailedRecap() {
    let html = '';
    TYPES.forEach(type => {
        html += '<div class="recap-block mb-3"><h6 class="fw-bold text-uppercase small text-muted mb-2">' + PLAT_TYPE_LABELS[type] + '</h6><ul class="list-group list-group-flush">';
        let hasItems = false;
        document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(slider => {
            const qty = parseInt(slider.value, 10) || 0;
            if (qty > 0) {
                hasItems = true;
                html += '<li class="list-group-item d-flex justify-content-between px-0"><span>' + (slider.dataset.nom || 'Plat') + '</span><strong>' + qty + ' invite(s)</strong></li>';
            }
        });
        if (!hasItems) html += '<li class="list-group-item px-0 text-muted">Aucune selection</li>';
        html += '</ul></div>';
    });
    const boissonExtra = sumBoissons();
    html += '<div class="recap-block mb-2"><h6 class="fw-bold text-uppercase small text-muted mb-2">Boissons</h6><ul class="list-group list-group-flush">';
    let hasBoisson = false;
    document.querySelectorAll('.boisson-qty').forEach(input => {
        const qty = parseInt(input.value, 10) || 0;
        if (qty > 0) {
            hasBoisson = true;
            const prix = (parseFloat(input.dataset.price) || 0) * qty;
            html += '<li class="list-group-item d-flex justify-content-between px-0"><span>' + (input.dataset.nom || 'Boisson') + '</span><strong>' + qty + ' x ' + prix.toFixed(2) + ' EUR</strong></li>';
        }
    });
    if (!hasBoisson) html += '<li class="list-group-item px-0 text-muted">Aucune (optionnel)</li>';
    html += '</ul></div>';
    if (cart.enfants > 0 && HAS_ENFANT_OPTION) {
        html += '<div class="recap-block mb-2 alert alert-info py-2 small"><i class="fa-solid fa-child me-1"></i> '
            + cart.enfants + ' menu(s) enfant a prevoir (' + (cart.enfants * PRIX_ENFANT).toFixed(2) + ' EUR)</div>';
    }
    return html;
}

function setActionButtonState(btn, ok) {
    if (!btn) return;
    btn.disabled = false;
    btn.setAttribute('aria-disabled', ok ? 'false' : 'true');
    if (btn.id === 'btn-commander') {
        btn.classList.toggle('btn-success', ok);
        btn.classList.toggle('btn-secondary', !ok);
        btn.title = ok ? '' : 'Cliquez pour revenir au champ manquant';
    } else {
        btn.classList.toggle('btn-primary', ok);
        btn.classList.toggle('btn-secondary', !ok);
    }
}

function showValidationMsg(text) {
    const msg = document.getElementById('validation-msg');
    if (!msg) return;
    msg.textContent = text;
    msg.classList.remove('d-none');
}

function hideValidationMsg() {
    document.getElementById('validation-msg')?.classList.add('d-none');
}

function clearFieldErrors() {
    document.querySelectorAll('.wizard-field-error').forEach(el => el.classList.remove('wizard-field-error'));
}

function markFieldError(el) {
    if (!el) return;
    el.classList.add('wizard-field-error');
    el.addEventListener('input', () => el.classList.remove('wizard-field-error'), { once: true });
    el.addEventListener('change', () => el.classList.remove('wizard-field-error'), { once: true });
}

function focusCategoryField(type) {
    const sum = sumType(type);
    const needed = cart.invites - sum;
    let target = null;

    document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(slider => {
        if (target) return;
        const val = parseInt(slider.value, 10) || 0;
        const maxAllowed = parseInt(slider.max, 10) || 0;
        if (needed > 0 && val < maxAllowed) target = slider;
        else if (needed < 0 && val > 0) target = slider;
    });

    if (!target) {
        target = document.querySelector('.plat-slider[data-type="' + type + '"]');
    }

    if (target) {
        markFieldError(target);
        setTimeout(() => {
            target.focus();
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 250);
    }
}

function focusStepField(stepId) {
    if (stepId === 'invites') {
        const inp = document.getElementById('invites');
        markFieldError(inp);
        inp?.focus();
        inp?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    if (TYPES.includes(stepId)) {
        focusCategoryField(stepId);
        return;
    }
    document.getElementById('step-' + stepId)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function shakePanel(stepId) {
    const panel = document.getElementById('step-' + stepId);
    if (!panel) return;
    panel.classList.add('wizard-shake');
    setTimeout(() => panel.classList.remove('wizard-shake'), 600);
}

function focusFirstInvalidField() {
    clearFieldErrors();
    if (!validateStep('invites')) {
        goToStep(WIZARD_STEPS.indexOf('invites'));
        showValidationMsg(stepMessage('invites'));
        shakePanel('invites');
        focusStepField('invites');
        return false;
    }
    for (const type of TYPES) {
        if (sumType(type) !== cart.invites) {
            goToStep(WIZARD_STEPS.indexOf(type));
            showValidationMsg(stepMessage(type));
            shakePanel(type);
            focusStepField(type);
            return false;
        }
    }
    hideValidationMsg();
    return true;
}

function updateWizardNextButtons() {
    document.querySelectorAll('.btn-wizard-next').forEach(btn => {
        const stepId = btn.dataset.validate || btn.closest('.wizard-panel')?.id?.replace('step-', '') || '';
        const ok = stepId ? validateStep(stepId) : false;
        setActionButtonState(btn, ok);
    });
    const sticky = document.getElementById('sticky-next');
    if (sticky) {
        if (currentStepIndex >= WIZARD_STEPS.length - 1) {
            sticky.classList.add('d-none');
        } else {
            sticky.classList.remove('d-none');
            const ok = validateStep(WIZARD_STEPS[currentStepIndex]);
            setActionButtonState(sticky, ok);
        }
    }
}

function updateStepperUI() {
    document.querySelectorAll('.wizard-step-btn').forEach(btn => {
        const idx = parseInt(btn.dataset.step, 10);
        btn.classList.remove('active', 'done', 'locked');
        btn.removeAttribute('aria-current');
        if (idx === currentStepIndex) {
            btn.classList.add('active');
            btn.setAttribute('aria-current', 'step');
        } else if (idx < currentStepIndex || (idx > 0 && WIZARD_STEPS.slice(0, idx).every(s => validateStep(s)))) {
            btn.classList.add('done');
        } else if (idx > currentStepIndex + 1) {
            btn.classList.add('locked');
        }
        const locked = btn.classList.contains('locked');
        btn.setAttribute('aria-disabled', locked ? 'true' : 'false');
        btn.tabIndex = locked ? -1 : 0;
    });
    const stepId = WIZARD_STEPS[currentStepIndex];
    const stickyLabel = document.getElementById('sticky-step-label');
    if (stickyLabel) {
        stickyLabel.textContent = 'Etape ' + (currentStepIndex + 1) + ' — ' + (WIZARD_LABELS[stepId] || stepId);
    }
    document.querySelectorAll('.wizard-panel').forEach(p => {
        const isActive = p.id === 'step-' + stepId;
        p.classList.toggle('wizard-panel-active', isActive);
        p.toggleAttribute('inert', !isActive);
        p.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    });
    updateWizardNextButtons();
}

function goToStep(index, scroll = true) {
    if (index < 0 || index >= WIZARD_STEPS.length) return;
    currentStepIndex = index;
    updateStepperUI();
    const stepId = WIZARD_STEPS[index];
    const el = document.getElementById('step-' + stepId);
    if (scroll && el) {
        setTimeout(() => {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 80);
    }
    if (stepId === 'invites') {
        const inp = document.getElementById('invites');
        if (inp) setTimeout(() => inp.focus(), 400);
    }
}

function tryGoNext(fromBtn) {
    const currentId = WIZARD_STEPS[currentStepIndex];
    if (!validateStep(currentId)) {
        showValidationMsg(stepMessage(currentId));
        shakePanel(currentId);
        focusStepField(currentId);
        return;
    }
    hideValidationMsg();
    let nextIndex = currentStepIndex + 1;
    if (fromBtn && fromBtn.dataset.next) {
        const target = fromBtn.dataset.next.replace('step-', '');
        const idx = WIZARD_STEPS.indexOf(target);
        if (idx >= 0) nextIndex = idx;
    }
    goToStep(nextIndex);
}

function updateUI() {
    syncBoissonsCart();
    syncEnfantsUI();
    const adultes = Math.max(0, cart.invites - cart.enfants);
    const menuAdultes = adultes * BASE_PRICE;
    const menuEnfants = cart.enfants * PRIX_ENFANT;
    const boissonsExtra = sumBoissons();
    const total = menuAdultes + menuEnfants + boissonsExtra;
    let allValid = true;

    TYPES.forEach(type => {
        const sum = sumType(type);
        const pct = cart.invites > 0 ? Math.min(100, (sum / cart.invites) * 100) : 0;
        const ok = sum === cart.invites;
        const over = sum > cart.invites;
        if (!ok) allValid = false;

        const badge = document.getElementById('status-' + type);
        if (badge) {
            if (ok) {
                badge.innerHTML = '<span class="badge bg-success">' + sum + ' / ' + cart.invites + ' OK</span>';
            } else if (over) {
                badge.innerHTML = '<span class="badge bg-danger">' + sum + ' / ' + cart.invites + ' (trop)</span>';
            } else {
                badge.innerHTML = '<span class="badge bg-warning text-dark">' + sum + ' / ' + cart.invites + ' (manque ' + (cart.invites - sum) + ')</span>';
            }
        }
        const bar = document.getElementById('progress-' + type);
        if (bar) {
            bar.style.width = pct + '%';
            bar.className = 'progress-bar ' + (ok ? 'bg-success' : (over ? 'bg-danger' : 'bg-warning'));
        }
        document.querySelectorAll('.plat-slider[data-type="' + type + '"]').forEach(slider => {
            const card = document.getElementById('card-' + type + '-' + slider.dataset.id);
            const v = parseInt(slider.value, 10) || 0;
            if (card) {
                card.classList.toggle('plat-active', v > 0);
            }
        });

        const hint = document.getElementById('hint-' + type);
        if (hint) {
            if (ok) {
                hint.textContent = 'Quota atteint — vous pouvez reajuster entre les plats.';
                hint.className = 'small text-success mb-2';
            } else if (over) {
                hint.textContent = 'Quota depasse — reduisez une selection.';
                hint.className = 'small text-danger mb-2';
            } else {
                hint.textContent = 'Il reste ' + (cart.invites - sum) + ' invite(s) a repartir.';
                hint.className = 'small text-muted mb-2';
            }
        }
    });

    document.getElementById('total').textContent = total.toFixed(2);
    document.getElementById('sticky-total').textContent = total.toFixed(2);
    const elAdultesTotal = document.getElementById('recap-adultes-total');
    if (elAdultesTotal) elAdultesTotal.textContent = menuAdultes.toFixed(2);
    const elEnfantsTotal = document.getElementById('recap-enfants-total');
    const elEnfantsN = document.getElementById('recap-enfants-n');
    if (elEnfantsTotal) elEnfantsTotal.textContent = menuEnfants.toFixed(2);
    if (elEnfantsN) elEnfantsN.textContent = cart.enfants;
    document.getElementById('recap-boissons-total').textContent = boissonsExtra.toFixed(2);
    document.getElementById('recap-detail').innerHTML = buildDetailedRecap();

    const btn = document.getElementById('btn-commander');
    const orderOk = TYPES.every(t => sumType(t) === cart.invites);
    if (btn) {
        setActionButtonState(btn, orderOk);
        if (WIZARD_STEPS[currentStepIndex] === 'recap') {
            if (orderOk) hideValidationMsg();
            else showValidationMsg('Repartissez exactement ' + cart.invites + ' invites pour chaque categorie.');
        }
    }
    updateStepperUI();
}

function autoFill() {
    syncInvitesFromInput();
    TYPES.forEach(type => autoFillCategory(type));
    hideValidationMsg();
    const recapIdx = WIZARD_STEPS.indexOf('recap');
    if (recapIdx >= 0) {
        goToStep(recapIdx);
    }
}

function submitOrder() {
    if (!TYPES.every(t => sumType(t) === cart.invites)) return;
    const f = document.createElement('form');
    f.method = 'POST';
    f.action = 'commande.php';
    const menuInput = document.createElement('input');
    menuInput.type = 'hidden';
    menuInput.name = 'menu_id';
    menuInput.value = MENU_ID;
    const dataInput = document.createElement('input');
    dataInput.type = 'hidden';
    dataInput.name = 'data';
    dataInput.value = JSON.stringify(cart);
    f.appendChild(menuInput);
    f.appendChild(dataInput);
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = CSRF_TOKEN;
    f.appendChild(csrfInput);
    document.body.appendChild(f);
    f.submit();
}

document.getElementById('invites').addEventListener('input', e => {
    cart.invites = Math.max(MIN_GUESTS, parseInt(e.target.value, 10) || MIN_GUESTS);
    e.target.value = cart.invites;
    if (cart.enfants > cart.invites) cart.enfants = cart.invites;
    const nbE = document.getElementById('nb-enfants');
    if (nbE) nbE.max = cart.invites;
    cart.entree = {}; cart.plat = {}; cart.dessert = {};
    document.querySelectorAll('.plat-slider').forEach(s => { s.value = 0; });
    syncSlidersMax();
    updateUI();
});
document.getElementById('invites').addEventListener('blur', e => {
    if ((parseInt(e.target.value, 10) || 0) < MIN_GUESTS) {
        e.target.value = MIN_GUESTS;
        cart.invites = MIN_GUESTS;
        syncSlidersMax();
        updateUI();
    }
});

if (HAS_ENFANT_OPTION) {
    document.getElementById('has-enfants')?.addEventListener('change', updateUI);
    document.getElementById('nb-enfants')?.addEventListener('input', e => {
        cart.enfants = Math.min(cart.invites, Math.max(0, parseInt(e.target.value, 10) || 0));
        e.target.value = cart.enfants;
        updateUI();
    });
}

document.querySelectorAll('.btn-autofill-cat').forEach(btn => {
    btn.addEventListener('click', () => autoFillCategory(btn.dataset.type));
});

document.querySelectorAll('.plat-slider').forEach(s => {
    s.addEventListener('input', () => updateChoice(s.dataset.type, s.dataset.id, s.value));
});
document.querySelectorAll('.boisson-qty').forEach(input => {
    input.addEventListener('input', updateUI);
});

document.querySelectorAll('.btn-wizard-next').forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.getAttribute('aria-disabled') === 'true') {
            const stepId = btn.dataset.validate || WIZARD_STEPS[currentStepIndex];
            showValidationMsg(stepMessage(stepId));
            shakePanel(stepId);
            focusStepField(stepId);
            return;
        }
        tryGoNext(btn);
    });
});
document.querySelectorAll('.btn-wizard-prev').forEach(btn => {
    btn.addEventListener('click', () => {
        const prev = btn.dataset.prev?.replace('step-', '');
        const idx = WIZARD_STEPS.indexOf(prev);
        goToStep(idx >= 0 ? idx : Math.max(0, currentStepIndex - 1));
    });
});
document.querySelectorAll('.wizard-step-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.classList.contains('locked') || btn.getAttribute('aria-disabled') === 'true') {
            focusFirstInvalidField();
            return;
        }
        const idx = parseInt(btn.dataset.step, 10);
        if (idx <= currentStepIndex) {
            goToStep(idx);
            return;
        }
        for (let i = 0; i < idx; i++) {
            if (!validateStep(WIZARD_STEPS[i])) {
                const msg = document.getElementById('validation-msg');
                if (msg) { msg.textContent = stepMessage(WIZARD_STEPS[i]); msg.classList.remove('d-none'); }
                goToStep(i);
                return;
            }
        }
        goToStep(idx);
    });
});

document.getElementById('sticky-next').addEventListener('click', () => {
    const sticky = document.getElementById('sticky-next');
    if (currentStepIndex >= WIZARD_STEPS.length - 1) return;
    if (sticky?.getAttribute('aria-disabled') === 'true') {
        const stepId = WIZARD_STEPS[currentStepIndex];
        showValidationMsg(stepMessage(stepId));
        shakePanel(stepId);
        focusStepField(stepId);
        return;
    }
    tryGoNext({ dataset: { next: 'step-' + WIZARD_STEPS[currentStepIndex + 1] } });
});

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const msg = document.getElementById('validation-msg');
    if (msg && !msg.classList.contains('d-none')) {
        hideValidationMsg();
        clearFieldErrors();
        e.preventDefault();
        return;
    }
    if (currentStepIndex > 0) {
        goToStep(currentStepIndex - 1);
        e.preventDefault();
    }
});

document.querySelectorAll('.btn-autofill-all').forEach(btn => {
    btn.addEventListener('click', autoFill);
});
document.getElementById('btn-commander').addEventListener('click', () => {
    if (!focusFirstInvalidField()) return;
    submitOrder();
});

syncSlidersMax();
updateUI();
if (restoreSavedCart(SAVED_CART)) {
    const recapIdx = WIZARD_STEPS.indexOf('recap');
    if (recapIdx >= 0) {
        goToStep(recapIdx, RESUME_COMMANDE);
    }
} else {
    goToStep(0, false);
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
