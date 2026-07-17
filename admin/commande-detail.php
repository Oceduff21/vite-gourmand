<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

$pageTitle = 'Detail commande';
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT c.*, u.nom, u.prenom, u.email, u.gsm, u.telephone,
           m.titre AS menu_titre, m.prix AS menu_prix, m.min_personnes, m.delai_jours, m.conditions AS menu_conditions
    FROM commandes c
    JOIN users u ON c.user_id = u.id
    JOIN menus m ON c.menu_id = m.id
    WHERE c.id = ?
');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$c) {
    header('Location: admin-commandes.php');
    exit();
}

$nbEnfants = (int)($c['nb_enfants'] ?? 0);
$adultes = max(0, (int)$c['nb_personnes'] - $nbEnfants);
$delai = analyseDelaiLivraison(
    $c['date_livraison'],
    (int)($c['delai_jours'] ?? 7),
    $c['created_at'] ?? null
);
$statuts = getStatutsCommande();
$tel = trim($c['telephone'] ?? $c['gsm'] ?? '');

$details = [];
$stmtD = $pdo->prepare('
    SELECT cd.quantite, cd.type, p.nom, p.regime, p.allergenes
    FROM commande_details cd
    JOIN plats p ON p.id = cd.plat_id
    WHERE cd.commande_id = ?
    ORDER BY FIELD(cd.type, "entree", "plat", "dessert"), p.nom
');
$stmtD->execute([$id]);
$details = $stmtD->fetchAll(PDO::FETCH_ASSOC);

$boissons = [];
try {
    $stmtB = $pdo->prepare('
        SELECT cb.quantite, cb.prix_unitaire, b.nom
        FROM commande_boissons cb
        JOIN boissons b ON b.id = cb.boisson_id
        WHERE cb.commande_id = ?
        ORDER BY b.nom
    ');
    $stmtB->execute([$id]);
    $boissons = $stmtB->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $boissons = [];
}

$hist = $pdo->prepare('SELECT * FROM commande_historique WHERE commande_id = ? ORDER BY created_at ASC');
$hist->execute([$id]);
$historique = $hist->fetchAll(PDO::FETCH_ASSOC);

$canDecide = $c['statut'] === 'en_attente';
$allowedStatuts = getAllowedNextStatuts($c['statut']);
$flash = $_SESSION['admin_flash'] ?? null;
$flashError = $_SESSION['admin_flash_error'] ?? null;
unset($_SESSION['admin_flash'], $_SESSION['admin_flash_error']);

require __DIR__ . '/partials/layout.php';
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <a href="admin-commandes.php" class="text-muted small text-decoration-none">&larr; Retour aux commandes</a>
        <h1 class="h2 mb-1 mt-1">Commande #<?= $id ?></h1>
        <p class="text-muted mb-0">Passee le <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></p>
    </div>
    <div class="text-end">
        <span class="badge bg-<?= getStatutBadgeClass($c['statut']) ?> fs-6"><?= htmlspecialchars($statuts[$c['statut']] ?? $c['statut']) ?></span>
        <div class="mt-2">
            <span class="badge bg-<?= getDelaiBadgeClass($delai) ?>"><?= htmlspecialchars(getDelaiBadgeLabel($delai)) ?></span>
        </div>
    </div>
</div>

<?php if ($flash): ?>
<div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
<div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<?php if ($canDecide): ?>
<div class="card-custom mb-4 border-start border-4 <?= $delai['delai_respecte'] ? 'border-success' : 'border-danger' ?>">
    <h5 class="mb-3">Decision requise</h5>
    <?php if (!$delai['delai_respecte']): ?>
    <div class="alert alert-danger py-2">
        <strong>Delai de reservation non respecte.</strong>
        Ce menu exige <?= (int)$delai['delai_jours'] ?> jour(s) de preparation minimum.
        Date la plus tot possible : <strong><?= $delai['date_min_livraison'] ?></strong>,
        demande client : <strong><?= $delai['date_livraison'] ?></strong>
        (<?= abs($delai['jours_marge']) ?> jour(s) de retard).
    </div>
    <?php elseif ($delai['urgence']): ?>
    <div class="alert alert-warning py-2">
        Livraison dans <?= (int)$delai['jours_avant_livraison'] ?> jour(s) — confirmez rapidement pour lancer la preparation.
    </div>
    <?php else: ?>
    <div class="alert alert-success py-2 mb-3">
        Delai respecte avec <?= (int)$delai['jours_marge'] ?> jour(s) de marge avant la livraison.
    </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-2">
        <form method="POST" action="update-statut.php" class="d-inline">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="statut" value="acceptee">
            <input type="hidden" name="redirect" value="detail">
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fa-solid fa-check me-1"></i> Confirmer la commande
            </button>
        </form>
        <a href="annuler-commande.php?id=<?= $id ?>&from=detail" class="btn btn-outline-danger btn-lg">
            <i class="fa-solid fa-xmark me-1"></i> Refuser / Annuler
        </a>
        <?php if ($tel): ?>
        <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $tel)) ?>" class="btn btn-outline-secondary btn-lg">
            <i class="fa-solid fa-phone me-1"></i> Appeler le client
        </a>
        <?php endif; ?>
        <?php if (!empty($c['email'])): ?>
        <a href="mailto:<?= htmlspecialchars($c['email']) ?>?subject=Commande%20%23<?= $id ?>" class="btn btn-outline-secondary btn-lg">
            <i class="fa-solid fa-envelope me-1"></i> Email
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-custom mb-4">
            <h5 class="mb-3">Client</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Nom</div>
                    <div class="fw-semibold"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Email</div>
                    <div><a href="mailto:<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email']) ?></a></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Telephone</div>
                    <div><?= $tel ? htmlspecialchars($tel) : '—' ?></div>
                </div>
            </div>
        </div>

        <div class="card-custom mb-4">
            <h5 class="mb-3">Prestation</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Menu</div>
                    <div class="fw-semibold"><?= htmlspecialchars($c['menu_titre']) ?></div>
                    <?php if ($canViewFinancials): ?>
                    <div class="small text-muted"><?= number_format((float)$c['menu_prix'], 2) ?> EUR/pers. adulte — min. <?= (int)$c['min_personnes'] ?> pers.</div>
                    <?php else: ?>
                    <div class="small text-muted">Min. <?= (int)$c['min_personnes'] ?> personnes — delai <?= (int)$delai['delai_jours'] ?> j.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Effectif</div>
                    <div class="fw-semibold"><?= (int)$c['nb_personnes'] ?> invite(s) total</div>
                    <?php if ($nbEnfants > 0): ?>
                    <div class="small text-muted"><?= $adultes ?> adulte(s) + <?= $nbEnfants ?> menu(x) enfant</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Date & heure livraison</div>
                    <div class="fw-semibold"><?= date('d/m/Y', strtotime($c['date_livraison'])) ?> a <?= substr($c['heure_livraison'], 0, 5) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Delai preparation menu</div>
                    <div><?= (int)$delai['delai_jours'] ?> jour(s) — livraison possible des le <?= $delai['date_min_livraison'] ?></div>
                </div>
            </div>
            <?php if (!empty($c['menu_conditions'])): ?>
            <hr>
            <div class="small text-muted mb-1">Conditions du menu</div>
            <p class="small mb-0"><?= nl2br(htmlspecialchars($c['menu_conditions'])) ?></p>
            <?php endif; ?>
        </div>

        <div class="card-custom mb-4">
            <h5 class="mb-3">Adresse de livraison</h5>
            <p class="mb-0">
                <?= htmlspecialchars($c['numero'] . ' ' . $c['rue']) ?><br>
                <?php if (!empty($c['complement'])): ?><?= htmlspecialchars($c['complement']) ?><br><?php endif; ?>
                <?= htmlspecialchars($c['code_postal'] . ' ' . $c['ville']) ?>
            </p>
        </div>

        <?php if ($details): ?>
        <div class="card-custom mb-4">
            <h5 class="mb-3">Selection des plats</h5>
            <table class="table table-sm align-middle mb-0">
                <caption class="visually-hidden">Selection des plats pour la commande</caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col">Categorie</th>
                        <th scope="col">Plat</th>
                        <th scope="col">Regime</th>
                        <th scope="col">Allergenes</th>
                        <th scope="col" class="text-end">Invites</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($details as $d): ?>
                <tr>
                    <td><?= htmlspecialchars(ucfirst($d['type'])) ?></td>
                    <td><?= htmlspecialchars($d['nom']) ?></td>
                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($d['regime'] ?? 'classique') ?></span></td>
                    <td><div class="plat-allergenes"><?= renderAllergenesBadges($d['allergenes'] ?? null, false) ?: '—' ?></div></td>
                    <td class="text-end"><?= (int)$d['quantite'] ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if ($boissons): ?>
        <div class="card-custom mb-4">
            <h5 class="mb-3">Boissons</h5>
            <table class="table table-sm align-middle mb-0">
                <caption class="visually-hidden">Boissons commandees</caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col">Boisson</th>
                        <th scope="col" class="text-end">Qte</th>
                        <?php if ($canViewFinancials): ?>
                        <th scope="col" class="text-end">Prix unit.</th>
                        <th scope="col" class="text-end">Sous-total</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($boissons as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['nom']) ?></td>
                    <td class="text-end"><?= (int)$b['quantite'] ?></td>
                    <?php if ($canViewFinancials): ?>
                    <td class="text-end"><?= number_format((float)$b['prix_unitaire'], 2) ?> EUR</td>
                    <td class="text-end"><?= number_format((float)$b['prix_unitaire'] * (int)$b['quantite'], 2) ?> EUR</td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <?php if ($canViewFinancials): ?>
        <div class="card-custom mb-4">
            <h5 class="mb-3">Recapitulatif financier</h5>
            <div class="d-flex justify-content-between mb-2"><span>Menu</span><span><?= number_format((float)$c['prix_menu'], 2) ?> EUR</span></div>
            <div class="d-flex justify-content-between mb-2"><span>Livraison</span><span><?= number_format((float)$c['prix_livraison'], 2) ?> EUR</span></div>
            <?php if ((float)$c['reduction'] > 0): ?>
            <div class="d-flex justify-content-between mb-2 text-success"><span>Reduction</span><span>-<?= number_format((float)$c['reduction'], 2) ?> EUR</span></div>
            <?php endif; ?>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span><?= number_format((float)$c['prix_total'], 2) ?> EUR</span></div>
        </div>
        <?php endif; ?>

        <div class="card-custom mb-4">
            <h5 class="mb-3">Changer le statut</h5>
            <?php if ($isEmploye): ?>
            <p class="small text-muted mb-2">Workflow : acceptee → preparation → livraison → livre → (materiel) → terminee.</p>
            <?php endif; ?>
            <?php if (count($allowedStatuts) <= 1 && $c['statut'] !== 'annulee'): ?>
            <p class="text-muted small mb-0">Aucune evolution de statut disponible pour cette commande.</p>
            <?php else: ?>
            <form method="POST" action="update-statut.php">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="redirect" value="detail">
                <select name="statut" class="form-select mb-2">
                <?php foreach ($statuts as $k => $label): if ($k === 'annulee' || !in_array($k, $allowedStatuts, true)) continue; ?>
                    <option value="<?= $k ?>" <?= $c['statut'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
                </select>
                <button class="btn btn-dark w-100">Mettre a jour</button>
            </form>
            <?php
            $nextStatuts = array_values(array_filter($allowedStatuts, fn($s) => $s !== $c['statut']));
            foreach ($nextStatuts as $nextStatut):
            ?>
            <form method="POST" action="update-statut.php" class="mt-2">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="redirect" value="detail">
                <input type="hidden" name="statut" value="<?= htmlspecialchars($nextStatut) ?>">
                <button class="btn btn-outline-primary w-100">
                    Passer a « <?= htmlspecialchars($statuts[$nextStatut]) ?> »
                </button>
            </form>
            <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($c['statut'] !== 'annulee' && $c['statut'] !== 'terminee'): ?>
            <a href="annuler-commande.php?id=<?= $id ?>&from=detail" class="btn btn-outline-danger w-100 mt-2">
                Refuser / Annuler (contact client requis)
            </a>
            <?php endif; ?>
        </div>

        <div class="card-custom">
            <h5 class="mb-3">Historique</h5>
            <?php if (empty($historique)): ?>
            <p class="text-muted small mb-0">Aucun historique enregistre.</p>
            <?php else: ?>
            <ul class="list-unstyled mb-0 admin-timeline">
            <?php foreach ($historique as $h): ?>
            <li class="mb-3 pb-3 border-bottom">
                <div class="fw-semibold"><?= htmlspecialchars($statuts[$h['statut']] ?? $h['statut']) ?></div>
                <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></div>
                <?php if (!empty($h['note'])): ?><div class="small mt-1"><?= htmlspecialchars($h['note']) ?></div><?php endif; ?>
            </li>
            <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
