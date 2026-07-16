<?php
session_start();
require 'includes/db.php';
require 'includes/menu-helpers.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM menus WHERE id = ?');
$stmt->execute([$id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$menu) {
    die('Menu introuvable');
}

$prix = (float)$menu['prix'];
$min = (int)$menu['min_personnes'];
$stock = (int)($menu['stock'] ?? 0);
$delai = (int)($menu['delai_jours'] ?? 7);
$group = getMenuPlatsByType($pdo, $id);
$hasOptions = array_sum(array_map('count', $group)) > 0;

function platBadge(string $regime): string
{
    return match ($regime) {
        'vegan' => '<span class="badge bg-success">Vegan</span>',
        'vegetarien' => '<span class="badge bg-warning text-dark">Vegetarien</span>',
        default => '<span class="badge bg-secondary">Classique</span>',
    };
}

include 'includes/header.php';
?>

<div class="container py-5 menu-order-page">

<div class="menu-order-header text-center mb-4">
    <h1><?= htmlspecialchars($menu['titre']) ?></h1>
    <?php if (!empty($menu['description'])): ?>
    <p class="text-muted mb-2"><?= htmlspecialchars($menu['description']) ?></p>
    <?php endif; ?>
    <p class="menu-price mb-3"><?= number_format($prix, 2) ?> &euro; <small class="text-muted">/ personne</small></p>
    <div class="d-flex flex-wrap justify-content-center gap-2">
        <span class="badge bg-dark">Min. <?= $min ?> personnes</span>
        <span class="badge bg-info text-dark">Delai <?= $delai ?> jours</span>
        <?php if ($stock > 0): ?>
        <span class="badge bg-success"><?= $stock ?> menu(x) disponible(s)</span>
        <?php else: ?>
        <span class="badge bg-danger">Rupture de stock</span>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($menu['conditions'])): ?>
<div class="alert alert-warning">
    <strong>Conditions :</strong> <?= nl2br(htmlspecialchars($menu['conditions'])) ?>
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
<div class="alert alert-danger"><?= htmlspecialchars($cartFlash) ?></div>
<?php endif; ?>

<div class="menu-steps card-custom mb-4">
    <div class="row text-center g-3">
        <div class="col-md-4"><span class="step-badge">1</span> Nombre d'invites</div>
        <div class="col-md-4"><span class="step-badge">2</span> Repartir les plats</div>
        <div class="col-md-4"><span class="step-badge">3</span> Valider la commande</div>
    </div>
</div>

<div class="card p-4 mb-4">
    <label class="form-label fw-bold" for="invites">Nombre d'invites</label>
    <div class="row align-items-end g-3">
        <div class="col-md-4">
            <input type="number" id="invites" class="form-control form-control-lg" value="<?= $min ?>" min="<?= $min ?>" max="500">
        </div>
        <div class="col-md-8">
            <p class="text-muted mb-0 small">Chaque categorie (entree, plat, dessert) doit totaliser exactement ce nombre d'invites.</p>
        </div>
    </div>
</div>

<?php
$labels = ['entree' => 'Entrees', 'plat' => 'Plats', 'dessert' => 'Desserts'];
foreach ($group as $type => $items):
    if (empty($items)) {
        continue;
    }
?>
<section class="menu-category mb-4" data-type="<?= $type ?>">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3 class="mb-0"><?= $labels[$type] ?></h3>
        <div class="category-status" id="status-<?= $type ?>">
            <span class="badge bg-secondary">0 / <?= $min ?></span>
        </div>
    </div>
    <div class="progress mb-3" style="height:8px">
        <div class="progress-bar" id="progress-<?= $type ?>" role="progressbar" style="width:0%"></div>
    </div>
    <div class="row g-3">
        <?php foreach ($items as $item): ?>
        <div class="col-md-4">
            <div class="plat-select-card card h-100" id="card-<?= $type ?>-<?= (int)$item['id'] ?>">
                <img src="assets/images/<?= htmlspecialchars($item['image'] ?? 'default.jpg') ?>" class="card-img-top plat-img" alt="<?= htmlspecialchars($item['nom']) ?>">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($item['nom']) ?></h5>
                    <?= platBadge($item['regime'] ?? 'classique') ?>
                    <?php if (!empty($item['description'])): ?>
                    <p class="small text-muted mt-2 mb-2"><?= htmlspecialchars($item['description']) ?></p>
                    <?php endif; ?>
                    <label class="form-label small mt-2" for="<?= $type ?>-<?= (int)$item['id'] ?>">Invites pour ce plat</label>
                    <input type="range" class="form-range plat-slider" min="0" max="<?= $min ?>" value="0"
                        id="<?= $type ?>-<?= (int)$item['id'] ?>"
                        data-type="<?= $type ?>" data-id="<?= (int)$item['id'] ?>">
                    <div class="text-center fw-bold plat-qty">
                        <span id="label-<?= $type ?>-<?= (int)$item['id'] ?>">0</span> invite(s)
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

<div class="menu-summary card p-4 sticky-summary">
    <div class="row align-items-center g-3">
        <div class="col-lg-7">
            <h4 class="mb-2">Recapitulatif</h4>
            <div id="cart-summary" class="small text-muted"></div>
            <div id="validation-msg" class="alert alert-warning mt-2 mb-0 py-2 small d-none"></div>
        </div>
        <div class="col-lg-5 text-lg-end">
            <p class="mb-1">Total estime</p>
            <p class="fs-3 fw-bold text-danger mb-3"><span id="total">0.00</span> &euro;</p>
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-lg-end">
                <button type="button" class="btn btn-outline-primary" id="btn-autofill">Repartition auto</button>
                <button type="button" class="btn btn-success btn-lg" id="btn-commander" disabled>Continuer la commande</button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>
</div>

<?php if ($stock > 0 && $hasOptions): ?>
<script>
const MENU_ID = <?= $id ?>;
const BASE_PRICE = <?= $prix ?>;
const MIN_GUESTS = <?= $min ?>;
const TYPES = ['entree', 'plat', 'dessert'];

let cart = { invites: MIN_GUESTS, entree: {}, plat: {}, dessert: {} };

function sumType(type) {
    return Object.values(cart[type] || {}).reduce((a, b) => a + b, 0);
}

function syncSlidersMax() {
    document.querySelectorAll('.plat-slider').forEach(s => { s.max = cart.invites; });
}

function updateChoice(type, id, val) {
    val = parseInt(val, 10) || 0;
    if (val <= 0) delete cart[type][id];
    else cart[type][id] = val;
    document.getElementById('label-' + type + '-' + id).textContent = val;
    updateUI();
}

function updateUI() {
    let html = '';
    let total = BASE_PRICE * cart.invites;
    let allValid = true;

    TYPES.forEach(type => {
        const sum = sumType(type);
        const remain = cart.invites - sum;
        const pct = cart.invites > 0 ? Math.min(100, (sum / cart.invites) * 100) : 0;
        const ok = sum === cart.invites;

        if (!ok) allValid = false;

        const badge = document.getElementById('status-' + type);
        if (badge) {
            badge.innerHTML = ok
                ? '<span class="badge bg-success">' + sum + ' / ' + cart.invites + ' OK</span>'
                : '<span class="badge bg-warning text-dark">' + sum + ' / ' + cart.invites + '</span>';
        }
        const bar = document.getElementById('progress-' + type);
        if (bar) {
            bar.style.width = pct + '%';
            bar.className = 'progress-bar ' + (ok ? 'bg-success' : 'bg-warning');
        }

        html += '<div>' + type.charAt(0).toUpperCase() + type.slice(1) + ' : <strong>' + sum + '/' + cart.invites + '</strong></div>';

        document.querySelectorAll('[data-type="' + type + '"]').forEach(slider => {
            const card = document.getElementById('card-' + type + '-' + slider.dataset.id);
            const v = parseInt(slider.value, 10) || 0;
            if (card) card.classList.toggle('plat-active', v > 0);
        });
    });

    document.getElementById('cart-summary').innerHTML = html;
    document.getElementById('total').textContent = total.toFixed(2);

    const msg = document.getElementById('validation-msg');
    const btn = document.getElementById('btn-commander');
    if (allValid) {
        msg.classList.add('d-none');
        btn.disabled = false;
        btn.textContent = 'Continuer la commande';
    } else {
        msg.classList.remove('d-none');
        msg.textContent = 'Repartissez exactement ' + cart.invites + ' invites pour chaque categorie avant de continuer.';
        btn.disabled = true;
    }
}

function autoFill() {
    TYPES.forEach(type => {
        cart[type] = {};
        const sliders = document.querySelectorAll('.plat-slider[data-type="' + type + '"]');
        if (!sliders.length) return;
        const each = Math.floor(cart.invites / sliders.length);
        sliders.forEach((s, i) => {
            const val = i === 0 ? cart.invites - each * (sliders.length - 1) : each;
            s.value = val;
            updateChoice(type, s.dataset.id, val);
        });
    });
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
    document.body.appendChild(f);
    f.submit();
}

document.getElementById('invites').addEventListener('input', e => {
    cart.invites = Math.max(MIN_GUESTS, parseInt(e.target.value, 10) || MIN_GUESTS);
    e.target.value = cart.invites;
    cart.entree = {}; cart.plat = {}; cart.dessert = {};
    document.querySelectorAll('.plat-slider').forEach(s => { s.value = 0; });
    syncSlidersMax();
    updateUI();
});

document.querySelectorAll('.plat-slider').forEach(s => {
    s.addEventListener('input', () => updateChoice(s.dataset.type, s.dataset.id, s.value));
});

document.getElementById('btn-autofill').addEventListener('click', autoFill);
document.getElementById('btn-commander').addEventListener('click', submitOrder);

syncSlidersMax();
updateUI();
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
