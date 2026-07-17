<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/menu-helpers.php';
require 'includes/flash.php';

$postedMenuId = (int)($_POST['menu_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['data'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide.');
    }
    $cart = normalizeCartFromPost($_POST['data']);
    if ($postedMenuId > 0 && !empty($cart)) {
        $_SESSION['menu_cart'] = $cart;
        $_SESSION['menu_cart_menu_id'] = $postedMenuId;
        unset($_SESSION['commande_recap_confirmed']);
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=' . urlencode('commande.php?menu_id=' . $postedMenuId));
        exit();
    }
    header('Location: commande.php?menu_id=' . $postedMenuId);
    exit();
}

$menu_id = (int)($_GET['menu_id'] ?? $_GET['id'] ?? 0);
if (!$menu_id) {
    header('Location: menus.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $loginRedirect = !empty($_SESSION['menu_cart']) && (int)($_SESSION['menu_cart_menu_id'] ?? 0) === $menu_id
        ? 'commande.php?menu_id=' . $menu_id
        : 'menu.php?id=' . $menu_id;
    header('Location: login.php?redirect=' . urlencode($loginRedirect));
    exit();
}

$step = $_GET['step'] ?? 'recap';
if ($step === 'recap') {
    unset($_SESSION['commande_recap_confirmed']);
}
if ($step === 'livraison' && empty($_SESSION['commande_recap_confirmed'])) {
    header('Location: commande.php?menu_id=' . $menu_id);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm_recap') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide.');
    }
    $_SESSION['commande_recap_confirmed'] = true;
    header('Location: commande.php?menu_id=' . $menu_id . '&step=livraison');
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM menus WHERE id = ?');
$stmt->execute([$menu_id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$menu) {
    die('Menu introuvable');
}

$min = (int)$menu['min_personnes'];
$prix = (float)$menu['prix'];
$stock = (int)($menu['stock'] ?? 0);
$delai = (int)($menu['delai_jours'] ?? 7);

$cart = [];
if (!empty($_SESSION['menu_cart']) && (int)($_SESSION['menu_cart_menu_id'] ?? 0) === $menu_id) {
    $cart = $_SESSION['menu_cart'];
}

$quantiteDefault = max($min, (int)($cart['invites'] ?? $min));
$nbEnfants = min($quantiteDefault, max(0, (int)($cart['enfants'] ?? 0)));
$adultes = max(0, $quantiteDefault - $nbEnfants);

$prixEnfant = $prix;
$stmtEnf = $pdo->query("SELECT prix FROM menus WHERE LOWER(theme) = 'enfant' OR LOWER(titre) LIKE '%enfant%' ORDER BY id LIMIT 1");
if ($rowEnf = $stmtEnf->fetch(PDO::FETCH_ASSOC)) {
    $prixEnfant = (float)$rowEnf['prix'];
}

if (empty($cart) || !cartHasValidSelection($cart, $quantiteDefault)) {
    $_SESSION['menu_cart_error'] = 'Veuillez d\'abord selectionner et repartir les plats pour ce menu.';
    header('Location: menu.php?id=' . $menu_id);
    exit();
}

$cartError = validatePlatSelection($pdo, $menu_id, $cart, $quantiteDefault, $min);
if ($cartError) {
    $_SESSION['menu_cart_error'] = $cartError;
    header('Location: menu.php?id=' . $menu_id);
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$platLabels = [];
$platIds = [];
foreach (['entree', 'plat', 'dessert'] as $type) {
    if (!empty($cart[$type]) && is_array($cart[$type])) {
        foreach ($cart[$type] as $platId => $qty) {
            if ((int)$qty > 0) {
                $platIds[] = (int)$platId;
            }
        }
    }
}
if ($platIds) {
    $placeholders = implode(',', array_fill(0, count($platIds), '?'));
    $stmt = $pdo->prepare("SELECT id, nom, type FROM plats WHERE id IN ($placeholders)");
    $stmt->execute($platIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $plat) {
        $platLabels[(int)$plat['id']] = $plat;
    }
}

$boissonLabels = [];
$totalBoissons = calculateBoissonsTotal($pdo, $menu_id, $cart);
if (!empty($cart['boissons'])) {
    $bIds = array_keys($cart['boissons']);
    if ($bIds) {
        $ph = implode(',', array_fill(0, count($bIds), '?'));
        $stmt = $pdo->prepare("SELECT id, nom, prix FROM boissons WHERE id IN ($ph)");
        $stmt->execute($bIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
            $boissonLabels[(int)$b['id']] = $b;
        }
    }
}

$totalMenu = calculateMenuPersonnesTotal($prix, $prixEnfant, $quantiteDefault, $nbEnfants);
$dateMinLivraison = getDateMinLivraison($delai);
$cartFlash = $_SESSION['menu_cart_error'] ?? null;
unset($_SESSION['menu_cart_error']);

$pageTitle = ($step === 'livraison' ? 'Livraison — ' : 'Recapitulatif — ') . ($menu['titre'] ?? '') . ' | Vite & Gourmand';
include 'includes/header.php';
?>

<div class="container py-5">
<?php showFlash(); ?>

<?php if ($cartFlash): ?>
<div class="alert alert-danger" role="alert"><?= htmlspecialchars($cartFlash) ?></div>
<?php endif; ?>

<?php if ($stock <= 0): ?>
<div class="alert alert-danger">Ce menu n'est plus disponible.</div>
<a href="menus.php" class="btn btn-outline-dark">Retour aux menus</a>

<?php elseif ($step === 'recap'): ?>

<h1 class="mb-2">Recapitulatif de votre commande</h1>
<p class="text-muted mb-4">Verifiez votre menu et la repartition des plats avant de continuer.</p>

<div class="card-custom mb-4">
    <h2 class="h5 mb-3"><?= htmlspecialchars($menu['titre']) ?></h2>
    <p class="text-muted mb-3"><?= number_format($prix, 2) ?> EUR/pers. — <?= (int)$quantiteDefault ?> invite(s)<?= $nbEnfants > 0 ? ', dont ' . $nbEnfants . ' enfant(s)' : '' ?></p>

    <?php foreach (['entree' => 'Entrees', 'plat' => 'Plats', 'dessert' => 'Desserts'] as $type => $label): ?>
    <?php if (!empty($cart[$type])): ?>
    <h3 class="h6 text-uppercase text-muted mt-3"><?= $label ?></h3>
    <ul class="list-group list-group-flush mb-2">
        <?php foreach ($cart[$type] as $platId => $qty): ?>
        <?php if ((int)$qty > 0 && isset($platLabels[(int)$platId])): ?>
        <li class="list-group-item d-flex justify-content-between px-0">
            <span><?= htmlspecialchars($platLabels[(int)$platId]['nom']) ?></span>
            <strong><?= (int)$qty ?> invite(s)</strong>
        </li>
        <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <?php endforeach; ?>

    <?php if (!empty($cart['boissons'])): ?>
    <h3 class="h6 text-uppercase text-muted mt-3">Boissons</h3>
    <ul class="list-group list-group-flush mb-2">
        <?php foreach ($cart['boissons'] as $bId => $qty): ?>
        <?php if ((int)$qty > 0 && isset($boissonLabels[(int)$bId])): ?>
        <li class="list-group-item d-flex justify-content-between px-0">
            <span><?= htmlspecialchars($boissonLabels[(int)$bId]['nom']) ?></span>
            <strong><?= (int)$qty ?> x <?= number_format((float)$boissonLabels[(int)$bId]['prix'], 2) ?> EUR</strong>
        </li>
        <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<div class="recap-totals p-4 bg-light rounded mb-4">
    <div class="d-flex justify-content-between mb-1"><span>Menu (<?= $adultes ?> adulte(s)<?= $nbEnfants > 0 ? ', ' . $nbEnfants . ' enfant(s)' : '' ?>)</span><span><?= number_format($totalMenu, 2) ?> EUR</span></div>
    <div class="d-flex justify-content-between mb-1"><span>Boissons</span><span><?= number_format($totalBoissons, 2) ?> EUR</span></div>
    <hr class="my-2">
    <div class="d-flex justify-content-between fw-bold"><span>Total estime (hors livraison)</span><span><?= number_format($totalMenu + $totalBoissons, 2) ?> EUR</span></div>
    <?php if ($quantiteDefault >= $min + 5): ?>
    <p class="small text-success mb-0 mt-2">Reduction de 10% applicable a l'etape livraison (<?= $min + 5 ?> pers. ou plus).</p>
    <?php endif; ?>
</div>

<div class="d-flex flex-wrap gap-2">
    <a href="menu.php?id=<?= $menu_id ?>" class="btn btn-outline-secondary">Modifier la selection</a>
    <form method="POST" class="d-inline">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="confirm_recap">
        <button type="submit" class="btn btn-success btn-lg">Confirmer et continuer</button>
    </form>
</div>

<?php else: /* livraison */ ?>

<h1 class="mb-2">Informations de livraison</h1>
<p class="text-muted mb-4">Completez vos coordonnees et l'adresse de la prestation pour finaliser la commande.</p>

<form method="POST" action="valider-commande.php" id="form-commande">
    <?= csrfField() ?>
    <input type="hidden" name="menu_id" value="<?= $menu_id ?>">
    <input type="hidden" name="cart_json" value="<?= htmlspecialchars(json_encode($cart), ENT_QUOTES) ?>">
    <input type="hidden" name="quantite" id="quantite" value="<?= (int)$quantiteDefault ?>">

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card p-4 h-100">
                <h2 class="h5 mb-3">Vos informations</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="cmd-readonly-nom">Nom</label>
                        <input type="text" id="cmd-readonly-nom" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" readonly aria-readonly="true">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="cmd-readonly-prenom">Prenom</label>
                        <input type="text" id="cmd-readonly-prenom" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" readonly aria-readonly="true">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="cmd-readonly-email">Email</label>
                        <input type="email" id="cmd-readonly-email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly aria-readonly="true">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="cmd-gsm">Telephone (GSM) <span class="text-danger">*</span></label>
                        <input type="tel" name="gsm" id="cmd-gsm" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? $user['gsm'] ?? '') ?>" required autocomplete="tel">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-4 h-100">
                <h2 class="h5 mb-3">Adresse et horaire</h2>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="cmd-rue">Rue <span class="text-danger">*</span></label>
                        <input type="text" name="rue" id="cmd-rue" class="form-control" value="<?= htmlspecialchars($user['rue'] ?? '') ?>" required autocomplete="street-address">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="cmd-numero">Numero <span class="text-danger">*</span></label>
                        <input type="text" name="numero" id="cmd-numero" class="form-control" value="<?= htmlspecialchars($user['numero'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="code_postal">Code postal <span class="text-danger">*</span></label>
                        <input type="text" name="code_postal" id="code_postal" class="form-control" value="<?= htmlspecialchars($user['code_postal'] ?? '') ?>" required autocomplete="postal-code">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="ville">Ville <span class="text-danger">*</span></label>
                        <input type="text" name="ville" id="ville" class="form-control" value="<?= htmlspecialchars($user['ville'] ?? '') ?>" required autocomplete="address-level2">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="cmd-date">Date de livraison <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="cmd-date" class="form-control" required min="<?= htmlspecialchars($dateMinLivraison) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="cmd-heure">Heure souhaitee <span class="text-danger">*</span></label>
                        <input type="time" name="heure" id="cmd-heure" class="form-control" required>
                    </div>
                </div>
                <p class="small text-muted mt-3 mb-0">Livraison gratuite a Bordeaux. Hors Bordeaux : 5,00 EUR + 0,59 EUR/km.</p>
            </div>
        </div>
    </div>

    <div class="card p-4 mt-4 bg-light" aria-live="polite">
        <h2 class="h5 mb-3">Detail du prix</h2>
        <div class="d-flex justify-content-between mb-1"><span>Menu</span><span><span id="prixMenu"><?= number_format($totalMenu, 2) ?></span> EUR</span></div>
        <div class="d-flex justify-content-between mb-1"><span>Boissons</span><span><span id="prixBoissons"><?= number_format($totalBoissons, 2) ?></span> EUR</span></div>
        <div class="d-flex justify-content-between mb-1"><span>Livraison</span><span><span id="prixLivraison">0.00</span> EUR</span></div>
        <div class="d-flex justify-content-between mb-1 text-success"><span>Reduction (-10% si +5 pers.)</span><span><span id="reduction">0.00</span> EUR</span></div>
        <hr>
        <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span><span id="total">0.00</span> EUR</span></div>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="commande.php?menu_id=<?= $menu_id ?>" class="btn btn-outline-secondary">Retour au recapitulatif</a>
            <button type="submit" class="btn btn-success btn-lg">Valider la commande</button>
        </div>
        <p class="small text-muted mt-3 mb-0"><i class="fa-solid fa-envelope me-1"></i> Un email de confirmation vous sera envoye.</p>
    </div>
</form>

<script>
const prixMenuUnitaire = <?= $totalMenu ?>;
const prixBoissonsFixe = <?= $totalBoissons ?>;
const minPers = <?= $min ?>;
const quantite = <?= (int)$quantiteDefault ?>;

function calculPrix() {
    const ville = (document.getElementById('ville').value || '').toLowerCase();
    const cp = document.getElementById('code_postal').value || '';
    let livraison = 0;
    if (ville !== 'bordeaux') {
        livraison = 5;
        if (cp.startsWith('33')) livraison += 8 * 0.59;
        else if (cp.startsWith('24') || cp.startsWith('47')) livraison += 15 * 0.59;
        else if (cp) livraison += 25 * 0.59;
        livraison = Math.round(livraison * 100) / 100;
    }
    const reduction = quantite >= (minPers + 5) ? prixMenuUnitaire * 0.10 : 0;
    document.getElementById('prixLivraison').textContent = livraison.toFixed(2);
    document.getElementById('reduction').textContent = '-' + reduction.toFixed(2);
    document.getElementById('total').textContent = (prixMenuUnitaire + prixBoissonsFixe + livraison - reduction).toFixed(2);
}
['ville', 'code_postal'].forEach(id => document.getElementById(id).addEventListener('input', calculPrix));
calculPrix();
</script>

<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
