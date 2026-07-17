<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/menu-helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare('
    SELECT c.*, m.titre AS menu_titre, m.prix AS menu_prix, m.min_personnes, m.delai_jours
    FROM commandes c
    JOIN menus m ON c.menu_id = m.id
    WHERE c.id = ? AND c.user_id = ?
');
$stmt->execute([$id, $userId]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    die('Commande introuvable');
}

if ($commande['statut'] !== 'en_attente') {
    die('Modification impossible : la commande a deja ete acceptee par notre equipe.');
}

$menuId = (int)$commande['menu_id'];
$min = (int)$commande['min_personnes'];
$delaiJours = (int)($commande['delai_jours'] ?? 7);
$dateMin = getDateMinLivraison($delaiJours, $commande['created_at'] ?? null);
$error = '';

$group = getMenuPlatsByType($pdo, $menuId);
$hasPlats = array_sum(array_map('count', $group)) > 0;
$activeTypes = array_values(array_filter(MENU_TYPES, static fn(string $t): bool => !empty($group[$t])));
$savedPlatCart = cartFromCommandeDetails($pdo, $id);

$stmtUser = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$labels = ['entree' => 'Entrees', 'plat' => 'Plats', 'dessert' => 'Desserts'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide.');
    }

    $nb = (int)($_POST['nb_personnes'] ?? 0);
    $date = trim($_POST['date'] ?? '');
    $heure = trim($_POST['heure'] ?? '');
    $rue = trim($_POST['rue'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complement = trim($_POST['complement'] ?? '');
    $codePostal = trim($_POST['code_postal'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');

    $platCart = normalizeCartFromPost($_POST['plat_cart'] ?? '');

    if ($nb < $min) {
        $error = 'Minimum ' . $min . ' personnes pour ce menu.';
    } elseif ($hasPlats) {
        $platError = validatePlatSelection($pdo, $menuId, $platCart, $nb, $min);
        if ($platError) {
            $error = $platError;
        }
    }

    if ($error === '' && ($rue === '' || $numero === '' || $codePostal === '' || $ville === '' || $heure === '')) {
        $error = 'Adresse, date et heure sont obligatoires.';
    }

    if ($error === '') {
        $delaiError = validateDateLivraisonMenu($date, $delaiJours, $commande['created_at'] ?? null);
        if ($delaiError) {
            $error = $delaiError;
        }
    }

    if ($error === '') {
        $nbEnfants = min($nb, max(0, (int)($commande['nb_enfants'] ?? 0)));
        $prixEnfant = (float)$commande['menu_prix'];
        $stmtEnf = $pdo->query("SELECT prix FROM menus WHERE LOWER(theme) = 'enfant' OR LOWER(titre) LIKE '%enfant%' ORDER BY id LIMIT 1");
        if ($rowEnf = $stmtEnf->fetch(PDO::FETCH_ASSOC)) {
            $prixEnfant = (float)$rowEnf['prix'];
        }

        $prixMenu = calculateMenuPersonnesTotal((float)$commande['menu_prix'], $prixEnfant, $nb, $nbEnfants);
        $livraison = calculateLivraisonPrice($ville, $codePostal);
        $reduction = calculateCommandeReduction($prixMenu, $nb, $min);

        $stmtBoissons = $pdo->prepare('SELECT COALESCE(SUM(quantite * prix_unitaire), 0) FROM commande_boissons WHERE commande_id = ?');
        $totalBoissons = 0.0;
        try {
            $stmtBoissons->execute([$id]);
            $totalBoissons = (float)$stmtBoissons->fetchColumn();
        } catch (Throwable $e) {
            $totalBoissons = 0.0;
        }

        $prixTotal = round($prixMenu + $totalBoissons + $livraison - $reduction, 2);

        $pdo->beginTransaction();
        try {
            $upd = $pdo->prepare('
                UPDATE commandes SET
                    nb_personnes = ?, nb_enfants = ?, date_livraison = ?, heure_livraison = ?,
                    rue = ?, numero = ?, complement = ?, code_postal = ?, ville = ?,
                    prix_menu = ?, prix_livraison = ?, reduction = ?, prix_total = ?
                WHERE id = ? AND user_id = ? AND statut = \'en_attente\'
            ');
            $upd->execute([
                $nb, $nbEnfants, $date, $heure,
                $rue, $numero, $complement, $codePostal, $ville,
                $prixMenu, $livraison, $reduction, $prixTotal,
                $id, $userId,
            ]);
        } catch (Throwable $e) {
            $upd = $pdo->prepare('
                UPDATE commandes SET
                    nb_personnes = ?, date_livraison = ?, heure_livraison = ?,
                    rue = ?, numero = ?, complement = ?, code_postal = ?, ville = ?,
                    prix_menu = ?, prix_livraison = ?, reduction = ?, prix_total = ?
                WHERE id = ? AND user_id = ? AND statut = \'en_attente\'
            ');
            $upd->execute([
                $nb, $date, $heure,
                $rue, $numero, $complement, $codePostal, $ville,
                $prixMenu, $livraison, $reduction, $prixTotal,
                $id, $userId,
            ]);
        }

        if ($hasPlats) {
            replaceCommandePlatDetails($pdo, $id, $platCart);
        }

        if ($gsm !== '') {
            $pdo->prepare('UPDATE users SET gsm = ?, telephone = COALESCE(telephone, ?) WHERE id = ?')
                ->execute([$gsm, $gsm, $userId]);
        }

        enregistrerHistorique($pdo, $id, 'en_attente', 'Modification par le client (repartition plats et livraison)');
        $pdo->commit();

        header('Location: detail-commande.php?id=' . $id);
        exit();
    }
}

$pageNoIndex = true;
$pageTitle = 'Modifier la commande #' . $id;
include 'includes/header.php';
?>

<div class="container py-5 user-space">
    <nav aria-label="Fil d'Ariane" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="espace-utilisateur.php?tab=commandes">Mes commandes</a></li>
            <li class="breadcrumb-item"><a href="detail-commande.php?id=<?= $id ?>">Commande #<?= $id ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Modifier</li>
        </ol>
    </nav>

    <h1 class="h2 mb-1">Modifier la commande #<?= $id ?></h1>
    <p class="text-muted mb-4">
        Menu : <strong><?= htmlspecialchars($commande['menu_titre']) ?></strong> (non modifiable).
        Vous pouvez ajuster la repartition des plats, le nombre de personnes, l'adresse et la livraison tant que la commande est en attente.
    </p>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-4" id="modifier-commande-form">
        <?= csrfField() ?>
        <input type="hidden" name="plat_cart" id="plat_cart" value="">

        <div class="col-md-6">
            <div class="card p-4 h-100">
                <h5 class="mb-3">Prestation</h5>
                <div class="mb-3">
                    <label class="form-label" for="mod-nb-personnes">Nombre de personnes (min. <?= $min ?>)</label>
                    <input type="number" name="nb_personnes" id="mod-nb-personnes" class="form-control" min="<?= $min ?>" value="<?= (int)$commande['nb_personnes'] ?>" required>
                    <div class="form-text">Si vous changez ce nombre, repartissez les invites sur chaque categorie de plats.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mod-date">Date de livraison</label>
                    <input type="date" name="date" id="mod-date" class="form-control" min="<?= htmlspecialchars($dateMin) ?>" value="<?= htmlspecialchars($commande['date_livraison']) ?>" required>
                    <div class="form-text">Delai minimum : <?= $delaiJours ?> jour(s) apres la commande.</div>
                </div>
                <div class="mb-0">
                    <label class="form-label" for="mod-heure">Heure souhaitee</label>
                    <input type="time" name="heure" id="mod-heure" class="form-control" value="<?= htmlspecialchars(substr($commande['heure_livraison'], 0, 5)) ?>" required>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4 h-100">
                <h5 class="mb-3">Coordonnees</h5>
                <div class="mb-3">
                    <label class="form-label" for="mod-gsm">Telephone (GSM)</label>
                    <input type="tel" name="gsm" id="mod-gsm" class="form-control" value="<?= htmlspecialchars($user['gsm'] ?? $user['telephone'] ?? '') ?>" autocomplete="tel">
                </div>
                <div class="row g-2">
                    <div class="col-8">
                        <label class="form-label" for="mod-rue">Rue</label>
                        <input type="text" name="rue" id="mod-rue" class="form-control" value="<?= htmlspecialchars($commande['rue']) ?>" required autocomplete="street-address">
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="mod-numero">N°</label>
                        <input type="text" name="numero" id="mod-numero" class="form-control" value="<?= htmlspecialchars($commande['numero']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="mod-complement">Complement</label>
                        <input type="text" name="complement" id="mod-complement" class="form-control" value="<?= htmlspecialchars($commande['complement'] ?? '') ?>" autocomplete="address-line2">
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="mod-cp">Code postal</label>
                        <input type="text" name="code_postal" id="mod-cp" class="form-control" value="<?= htmlspecialchars($commande['code_postal']) ?>" required autocomplete="postal-code">
                    </div>
                    <div class="col-8">
                        <label class="form-label" for="mod-ville">Ville</label>
                        <input type="text" name="ville" id="mod-ville" class="form-control" value="<?= htmlspecialchars($commande['ville']) ?>" required autocomplete="address-level2">
                    </div>
                </div>
            </div>
        </div>

        <?php if ($hasPlats): ?>
        <div class="col-12">
            <div class="card-custom">
                <h5 class="mb-2">Repartition des plats</h5>
                <p class="text-muted small mb-3">
                    Repartissez <strong class="guest-count-label"><?= (int)$commande['nb_personnes'] ?></strong> invites entre les options pour chaque categorie.
                    Le menu choisi ne peut pas etre change.
                </p>
                <div id="plat-validation-msg" class="alert alert-warning py-2 small d-none mb-3" role="alert"></div>

                <?php foreach ($activeTypes as $type): ?>
                    <?php if (empty($group[$type])) continue; ?>
                    <section id="section-<?= $type ?>" class="menu-category mb-4 pb-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h6 class="h5 mb-0"><?= htmlspecialchars($labels[$type] ?? ucfirst($type)) ?></h6>
                            <div class="d-flex align-items-center gap-2">
                                <div id="status-<?= $type ?>">
                                    <span class="badge bg-secondary">0 / <?= (int)$commande['nb_personnes'] ?></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-autofill-cat" data-type="<?= $type ?>">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Repartir auto
                                </button>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height:8px">
                            <div class="progress-bar" id="progress-<?= $type ?>" role="progressbar" style="width:0%"></div>
                        </div>
                        <div class="row g-3 plat-grid">
                            <?php foreach ($group[$type] as $item): ?>
                                <?php
                                $platId = (int)$item['id'];
                                $qty = (int)($savedPlatCart[$type][$platId] ?? 0);
                                ?>
                                <div class="col-sm-6 col-lg-4">
                                    <div class="plat-select-card" id="card-<?= $type ?>-<?= $platId ?>">
                                        <div class="plat-img-wrap">
                                            <img src="<?= htmlspecialchars(assetImageUrl('assets/images/' . ($item['image'] ?? 'default.jpg'))) ?>" class="plat-img" alt="<?= htmlspecialchars($item['nom']) ?>" loading="lazy">
                                        </div>
                                        <div class="plat-card-body">
                                            <div class="plat-card-info">
                                                <h6 class="plat-card-title"><?= htmlspecialchars($item['nom']) ?></h6>
                                                <div class="plat-card-badges"><?= platBadge($item['regime'] ?? 'classique') ?></div>
                                                <div class="plat-allergenes mt-1"><?= renderAllergenesBadges($item['allergenes'] ?? null) ?></div>
                                            </div>
                                            <div class="plat-card-controls">
                                                <label class="form-label small mb-1" for="<?= $type ?>-<?= $platId ?>">Invites</label>
                                                <input type="range" class="form-range plat-slider" min="0" max="<?= (int)$commande['nb_personnes'] ?>" value="<?= $qty ?>"
                                                    id="<?= $type ?>-<?= $platId ?>"
                                                    data-type="<?= $type ?>" data-id="<?= $platId ?>"
                                                    data-nom="<?= htmlspecialchars($item['nom'], ENT_QUOTES) ?>"
                                                    data-regime="<?= htmlspecialchars($item['regime'] ?? 'classique', ENT_QUOTES) ?>">
                                                <div class="plat-qty"><span id="label-<?= $type ?>-<?= $platId ?>"><?= $qty ?></span> invite(s)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="detail-commande.php?id=<?= $id ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php if ($hasPlats): ?>
<script>
window.MODIFIER_PLATS_CONFIG = {
    minGuests: <?= $min ?>,
    invites: <?= (int)$commande['nb_personnes'] ?>,
    types: <?= json_encode($activeTypes) ?>,
    savedCart: <?= json_encode($savedPlatCart, JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="assets/js/modifier-plats.js"></script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
