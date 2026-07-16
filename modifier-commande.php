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

$min = (int)$commande['min_personnes'];
$delaiJours = (int)($commande['delai_jours'] ?? 7);
$dateMin = getDateMinLivraison($delaiJours, $commande['created_at'] ?? null);
$error = '';

$stmtUser = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

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

    if ($nb < $min) {
        $error = 'Minimum ' . $min . ' personnes pour ce menu.';
    } elseif ($rue === '' || $numero === '' || $codePostal === '' || $ville === '' || $heure === '') {
        $error = 'Adresse, date et heure sont obligatoires.';
    } else {
        $delaiError = validateDateLivraisonMenu($date, $delaiJours, $commande['created_at'] ?? null);
        if ($delaiError) {
            $error = $delaiError;
        } else {
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

            if ($gsm !== '') {
                $pdo->prepare('UPDATE users SET gsm = ?, telephone = COALESCE(telephone, ?) WHERE id = ?')
                    ->execute([$gsm, $gsm, $userId]);
            }

            enregistrerHistorique($pdo, $id, 'en_attente', 'Modification par le client (hors choix du menu)');
            $pdo->commit();

            header('Location: espace-utilisateur.php?tab=commandes');
            exit();
        }
    }
}

$pageNoIndex = true;
include 'includes/header.php';
?>

<div class="container py-5">
<h2 class="mb-1">Modifier la commande #<?= $id ?></h2>
<p class="text-muted mb-4">
    Menu : <strong><?= htmlspecialchars($commande['menu_titre']) ?></strong> (non modifiable).
    Vous pouvez ajuster les personnes, l'adresse, la date et l'heure tant que la commande est en attente.
</p>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="row g-4">
<?= csrfField() ?>

<div class="col-md-6">
    <div class="card p-4">
        <h5 class="mb-3">Prestation</h5>
        <div class="mb-3">
            <label class="form-label">Nombre de personnes (min. <?= $min ?>)</label>
            <input type="number" name="nb_personnes" class="form-control" min="<?= $min ?>" value="<?= (int)($commande['nb_personnes']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Date de livraison</label>
            <input type="date" name="date" class="form-control" min="<?= htmlspecialchars($dateMin) ?>" value="<?= htmlspecialchars($commande['date_livraison']) ?>" required>
            <div class="form-text">Delai minimum : <?= $delaiJours ?> jour(s) apres la commande.</div>
        </div>
        <div class="mb-0">
            <label class="form-label">Heure souhaitee</label>
            <input type="time" name="heure" class="form-control" value="<?= htmlspecialchars(substr($commande['heure_livraison'], 0, 5)) ?>" required>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card p-4">
        <h5 class="mb-3">Coordonnees</h5>
        <div class="mb-3">
            <label class="form-label">Telephone (GSM)</label>
            <input type="tel" name="gsm" class="form-control" value="<?= htmlspecialchars($user['gsm'] ?? $user['telephone'] ?? '') ?>">
        </div>
        <div class="row g-2">
            <div class="col-8">
                <label class="form-label">Rue</label>
                <input type="text" name="rue" class="form-control" value="<?= htmlspecialchars($commande['rue']) ?>" required>
            </div>
            <div class="col-4">
                <label class="form-label">N°</label>
                <input type="text" name="numero" class="form-control" value="<?= htmlspecialchars($commande['numero']) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label">Complement</label>
                <input type="text" name="complement" class="form-control" value="<?= htmlspecialchars($commande['complement'] ?? '') ?>">
            </div>
            <div class="col-4">
                <label class="form-label">Code postal</label>
                <input type="text" name="code_postal" class="form-control" value="<?= htmlspecialchars($commande['code_postal']) ?>" required>
            </div>
            <div class="col-8">
                <label class="form-label">Ville</label>
                <input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($commande['ville']) ?>" required>
            </div>
        </div>
    </div>
</div>

<div class="col-12">
    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    <a href="espace-utilisateur.php?tab=commandes" class="btn btn-outline-secondary">Annuler</a>
</div>
</form>
</div>

<?php include 'includes/footer.php'; ?>
