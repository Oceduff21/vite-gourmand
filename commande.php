<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/menu-helpers.php';

$postedMenuId = (int)($_POST['menu_id'] ?? $_GET['menu_id'] ?? $_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['data'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide.');
    }
    $cart = normalizeCartFromPost($_POST['data']);
    if ($postedMenuId > 0 && !empty($cart)) {
        $_SESSION['menu_cart'] = $cart;
        $_SESSION['menu_cart_menu_id'] = $postedMenuId;
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=' . urlencode('menu.php?id=' . $postedMenuId . '&resume=commande'));
        exit();
    }
    header('Location: commande.php?menu_id=' . $postedMenuId);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $loginRedirect = ($menu_id = (int)($_GET['menu_id'] ?? $_GET['id'] ?? 0)) > 0
        && !empty($_SESSION['menu_cart'])
        && (int)($_SESSION['menu_cart_menu_id'] ?? 0) === $menu_id
        ? 'menu.php?id=' . $menu_id . '&resume=commande'
        : $_SERVER['REQUEST_URI'];
    header('Location: login.php?redirect=' . urlencode($loginRedirect));
    exit();
}

$menu_id = (int)($_GET['menu_id'] ?? $_GET['id'] ?? 0);
if (!$menu_id) {
    header('Location: menus.php');
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
$cartError = null;

if (empty($cart) || !cartHasValidSelection($cart, $quantiteDefault)) {
    $cartError = 'Veuillez d\'abord selectionner et repartir les plats pour ce menu.';
} else {
    $cartError = validatePlatSelection($pdo, $menu_id, $cart, $quantiteDefault, $min);
}

if ($cartError) {
    $_SESSION['menu_cart_error'] = $cartError;
    header('Location: menu.php?id=' . $menu_id);
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$platLabels = [];
if ($cart) {
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

$delai = (int)($menu['delai_jours'] ?? 7);
$dateMinLivraison = getDateMinLivraison($delai);

include 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">Commander : <?= htmlspecialchars($menu['titre']) ?></h1>

    <?php if (!empty($menu['conditions'])): ?>
    <div class="alert alert-warning">
        <strong>Conditions importantes :</strong><br>
        <?= nl2br(htmlspecialchars($menu['conditions'])) ?>
    </div>
    <?php endif; ?>

    <?php if ($stock <= 0): ?>
    <div class="alert alert-danger">Ce menu n'est plus disponible (stock epuise).</div>
    <?php else: ?>

    <form method="POST" action="valider-commande.php" id="form-commande">
        <?= csrfField() ?>
        <input type="hidden" name="menu_id" value="<?= $menu_id ?>">
        <input type="hidden" name="cart_json" value="<?= htmlspecialchars(json_encode($cart), ENT_QUOTES) ?>">

        <?php if ($cart): ?>
        <div class="alert alert-success mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong>Plats selectionnes (<?= (int)$quantiteDefault ?> invites<?= $nbEnfants > 0 ? ', dont ' . $nbEnfants . ' enfant(s)' : '' ?>) :</strong>
                    <ul class="mb-0 mt-2">
                    <?php foreach (['entree', 'plat', 'dessert'] as $type): ?>
                        <?php if (!empty($cart[$type]) && is_array($cart[$type])): ?>
                            <?php foreach ($cart[$type] as $platId => $qty): ?>
                                <?php if ((int)$qty > 0 && isset($platLabels[(int)$platId])): ?>
                                <li><?= htmlspecialchars(ucfirst($type)) ?> : <?= htmlspecialchars($platLabels[(int)$platId]['nom']) ?> &mdash; <?= (int)$qty ?> invite(s)</li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                    <?php if (!empty($cart['boissons'])): ?>
                    <strong class="d-block mt-2">Boissons :</strong>
                    <ul class="mb-0">
                    <?php foreach ($cart['boissons'] as $bId => $qty): ?>
                        <?php if ((int)$qty > 0 && isset($boissonLabels[(int)$bId])): ?>
                        <li><?= htmlspecialchars($boissonLabels[(int)$bId]['nom']) ?> &mdash; <?= (int)$qty ?> &times; <?= number_format((float)$boissonLabels[(int)$bId]['prix'], 2) ?> EUR</li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <?php if ($nbEnfants > 0): ?>
                    <strong class="d-block mt-2">Menus enfant :</strong>
                    <p class="mb-0 small text-muted"><?= $nbEnfants ?> menu(x) enfant a <?= number_format($prixEnfant, 2) ?> EUR/pers.</p>
                    <?php endif; ?>
                </div>
                <a href="menu.php?id=<?= $menu_id ?>" class="btn btn-sm btn-outline-primary">Modifier la selection</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card p-4">
                    <h4>Vos informations</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prenom</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="cmd-gsm">Telephone <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="tel" name="gsm" id="cmd-gsm" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? $user['gsm'] ?? '') ?>" required autocomplete="tel">
                        </div>
                    </div>
                </div>

                <div class="card p-4 mt-3">
                    <h4>Adresse de livraison</h4>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="cmd-rue">Rue <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="text" name="rue" id="cmd-rue" class="form-control" value="<?= htmlspecialchars($user['rue'] ?? '') ?>" required autocomplete="street-address">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cmd-numero">Numero <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="text" name="numero" id="cmd-numero" class="form-control" value="<?= htmlspecialchars($user['numero'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label" for="cmd-complement">Complement</label>
                            <input type="text" name="complement" id="cmd-complement" class="form-control" value="<?= htmlspecialchars($user['complement'] ?? '') ?>" autocomplete="address-line2">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="code_postal">Code postal <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="text" name="code_postal" id="code_postal" class="form-control" value="<?= htmlspecialchars($user['code_postal'] ?? '') ?>" required autocomplete="postal-code">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="ville">Ville <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="text" name="ville" id="ville" class="form-control" value="<?= htmlspecialchars($user['ville'] ?? '') ?>" required autocomplete="address-level2">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-4">
                    <h4>Prestation</h4>
                    <div class="mb-3">
                        <label class="form-label">Menu selectionne</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($menu['titre']) ?> — <?= number_format($prix, 2) ?> EUR/pers." readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="quantite">Nombre de personnes (min. <?= $min ?>)</label>
                        <input type="number" name="quantite" id="quantite" class="form-control" min="<?= $min ?>" value="<?= max($min, $quantiteDefault) ?>" required readonly aria-readonly="true" aria-describedby="quantite-help">
                        <small class="text-muted" id="quantite-help">Defini lors de la selection des plats</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="cmd-date">Date de livraison <span class="text-danger" aria-hidden="true">*</span></label>
                        <input type="date" name="date" id="cmd-date" class="form-control" required min="<?= htmlspecialchars($dateMinLivraison) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="cmd-heure">Heure souhaitee <span class="text-danger" aria-hidden="true">*</span></label>
                        <input type="time" name="heure" id="cmd-heure" class="form-control" required>
                    </div>
                </div>

                <div class="card p-4 mt-3 bg-light" aria-live="polite" aria-atomic="true">
                    <h2 class="h4">Recapitulatif prix</h2>
                    <div class="d-flex justify-content-between"><span>Adultes (<span id="recap-adultes-n"><?= $adultes ?></span> pers.)</span><span><span id="prixAdultes">0.00</span> EUR</span></div>
                    <div class="d-flex justify-content-between<?= $nbEnfants > 0 ? '' : ' d-none' ?>" id="recap-enfants-price-line"><span>Enfants (<span id="recap-enfants-n"><?= $nbEnfants ?></span> pers.)</span><span><span id="prixEnfants">0.00</span> EUR</span></div>
                    <div class="d-flex justify-content-between fw-semibold"><span>Total menu</span><span><span id="prixMenu">0.00</span> EUR</span></div>
                    <div class="d-flex justify-content-between"><span>Boissons</span><span><span id="prixBoissons"><?= number_format($totalBoissons, 2) ?></span> EUR</span></div>
                    <div class="d-flex justify-content-between"><span>Livraison</span><span><span id="prixLivraison">0.00</span> EUR</span></div>
                    <div class="d-flex justify-content-between text-success"><span>Reduction (-10% si +5 pers.)</span><span><span id="reduction">0.00</span> EUR</span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span><span id="total">0.00</span> EUR</span></div>
                    <button type="submit" class="btn btn-success w-100 mt-3">Valider la commande</button>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
const prixMenuUnitaire = <?= $prix ?>;
const prixEnfantUnitaire = <?= $prixEnfant ?>;
const nbEnfantsFixe = <?= $nbEnfants ?>;
const minPers = <?= $min ?>;
const prixBoissonsFixe = <?= $totalBoissons ?>;

function calculPrix() {
    const quantite = parseInt(document.getElementById('quantite').value) || minPers;
    const adultes = Math.max(0, quantite - nbEnfantsFixe);
    const ville = (document.getElementById('ville').value || '').toLowerCase();
    const cp = document.getElementById('code_postal').value || '';
    const prixAdultes = adultes * prixMenuUnitaire;
    const prixEnfants = nbEnfantsFixe * prixEnfantUnitaire;
    let totalMenu = prixAdultes + prixEnfants;
    let livraison = 0;
    if (ville !== 'bordeaux') {
        livraison = 5;
        if (cp.startsWith('33')) livraison += 8 * 0.59;
        else if (cp.startsWith('24') || cp.startsWith('47')) livraison += 15 * 0.59;
        else if (cp) livraison += 25 * 0.59;
        livraison = Math.round(livraison * 100) / 100;
    }
    let reduction = quantite >= (minPers + 5) ? totalMenu * 0.10 : 0;
    const elAdultes = document.getElementById('prixAdultes');
    const elEnfants = document.getElementById('prixEnfants');
    if (elAdultes) elAdultes.textContent = prixAdultes.toFixed(2);
    if (elEnfants) elEnfants.textContent = prixEnfants.toFixed(2);
    document.getElementById('prixMenu').textContent = totalMenu.toFixed(2);
    document.getElementById('prixBoissons').textContent = prixBoissonsFixe.toFixed(2);
    document.getElementById('prixLivraison').textContent = livraison.toFixed(2);
    document.getElementById('reduction').textContent = '-' + reduction.toFixed(2);
    document.getElementById('total').textContent = (totalMenu + prixBoissonsFixe + livraison - reduction).toFixed(2);
}

['quantite', 'ville', 'code_postal'].forEach(id => {
    document.getElementById(id).addEventListener('input', calculPrix);
});
calculPrix();
</script>

<?php include 'includes/footer.php'; ?>
