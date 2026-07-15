<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
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

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$min = (int)$menu['min_personnes'];
$prix = (float)$menu['prix'];
$stock = (int)($menu['stock'] ?? 0);

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
                            <label class="form-label">Telephone</label>
                            <input type="tel" name="gsm" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? $user['gsm'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="card p-4 mt-3">
                    <h4>Adresse de livraison</h4>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Rue</label>
                            <input type="text" name="rue" class="form-control" value="<?= htmlspecialchars($user['rue'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Numero</label>
                            <input type="text" name="numero" class="form-control" value="<?= htmlspecialchars($user['numero'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Complement</label>
                            <input type="text" name="complement" class="form-control" value="<?= htmlspecialchars($user['complement'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code postal</label>
                            <input type="text" name="code_postal" id="code_postal" class="form-control" value="<?= htmlspecialchars($user['code_postal'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Ville</label>
                            <input type="text" name="ville" id="ville" class="form-control" value="<?= htmlspecialchars($user['ville'] ?? '') ?>" required>
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
                        <label class="form-label">Nombre de personnes (min. <?= $min ?>)</label>
                        <input type="number" name="quantite" id="quantite" class="form-control" min="<?= $min ?>" value="<?= $min ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de livraison</label>
                        <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d', strtotime('+' . (int)($menu['delai_jours'] ?? 7) . ' days')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Heure souhaitee</label>
                        <input type="time" name="heure" class="form-control" required>
                    </div>
                </div>

                <div class="card p-4 mt-3 bg-light">
                    <h4>Recapitulatif prix</h4>
                    <div class="d-flex justify-content-between"><span>Prix menu</span><span><span id="prixMenu">0.00</span> EUR</span></div>
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
const minPers = <?= $min ?>;

function calculPrix() {
    const quantite = parseInt(document.getElementById('quantite').value) || minPers;
    const ville = (document.getElementById('ville').value || '').toLowerCase();
    const cp = document.getElementById('code_postal').value || '';
    let totalMenu = quantite * prixMenuUnitaire;
    let livraison = 0;
    if (ville !== 'bordeaux') {
        livraison = 5;
        if (cp.startsWith('33')) livraison += 8;
        else if (cp.startsWith('24') || cp.startsWith('47')) livraison += 15;
        else if (cp) livraison += 25;
    }
    let reduction = quantite >= (minPers + 5) ? totalMenu * 0.10 : 0;
    document.getElementById('prixMenu').textContent = totalMenu.toFixed(2);
    document.getElementById('prixLivraison').textContent = livraison.toFixed(2);
    document.getElementById('reduction').textContent = '-' + reduction.toFixed(2);
    document.getElementById('total').textContent = (totalMenu + livraison - reduction).toFixed(2);
}

['quantite', 'ville', 'code_postal'].forEach(id => {
    document.getElementById(id).addEventListener('input', calculPrix);
});
calculPrix();
</script>

<?php include 'includes/footer.php'; ?>
