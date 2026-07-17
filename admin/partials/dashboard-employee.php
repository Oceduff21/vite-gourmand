<?php
/** Dashboard employe — sans donnees financieres (CA, panier moyen). */
?>
<h1 class="h2 mb-1">Mon espace employe</h1>
<p class="text-muted mb-4">Suivi operationnel de vos prestations — chiffre d'affaires reserve a l'administration.</p>

<?php if ($dashboardError): ?>
<div class="alert alert-danger"><?= htmlspecialchars($dashboardError) ?></div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">A traiter</h6>
            <strong class="fs-5 text-warning"><?= $enAttente ?></strong>
            <?php if ($enAttente): ?><div><a href="admin-commandes.php?statut=en_attente" class="small">Voir</a></div><?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">En preparation</h6>
            <strong class="fs-5 text-info"><?= $enPreparation ?></strong>
            <?php if ($enPreparation): ?><div><a href="admin-commandes.php?statut=en_preparation" class="small">Voir</a></div><?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">En livraison</h6>
            <strong class="fs-5"><?= $enLivraison ?></strong>
            <?php if ($enLivraison): ?><div><a href="admin-commandes.php?statut=en_livraison" class="small">Voir</a></div><?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">Livraisons aujourd'hui</h6>
            <strong class="fs-5 text-primary"><?= $livraisonsAujourdhui ?></strong>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">Avis a moderer</h6>
            <strong class="fs-5 text-danger"><?= $avisEnAttente ?></strong>
            <?php if ($avisEnAttente): ?><div><a href="admin-avis.php?filter=pending" class="small">Moderer</a></div><?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">Mes mises a jour</h6>
            <strong class="fs-5 text-success"><?= $mesActions ?></strong>
        </div>
    </div>
</div>

<div class="card-custom mb-4">
    <h5 class="mb-3">Rechercher une commande</h5>
    <form method="GET" action="admin-commandes.php" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold" for="filtre-statut">Statut</label>
            <select name="statut" id="filtre-statut" class="form-select">
                <option value="">Tous les statuts</option>
                <?php foreach (getStatutsCommande() as $k => $label): ?>
                <option value="<?= $k ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label small fw-semibold" for="filtre-client">Client</label>
            <input type="text" name="client" id="filtre-client" class="form-control" placeholder="Nom, prenom ou email">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Filtrer les commandes</button>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card-custom">
            <h5 class="mb-3">Repartition des commandes par statut</h5>
            <?php
            $statutRowsEmp = [];
            foreach ($statutsBreakdown as $row) {
                $statutRowsEmp[] = [$row['label'], (int)$row['count']];
            }
            ?>
            <div class="chart-wrap" <?= chartFigureAttrs('Repartition des commandes par statut', 'table-emp-statuts') ?>>
                <canvas id="chartStatuts" aria-hidden="true" role="presentation"></canvas>
            </div>
            <?= renderChartDataTable('Statuts commandes', ['Statut', 'Nombre'], $statutRowsEmp, 'table-emp-statuts') ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-custom">
            <h5 class="mb-3">Volume de commandes (30 jours)</h5>
            <?php
            $jourRowsEmp = [];
            foreach ($dates as $i => $d) {
                $jourRowsEmp[] = [$d, (int)($totaux[$i] ?? 0)];
            }
            ?>
            <div class="chart-wrap" <?= chartFigureAttrs('Volume de commandes sur 30 jours', 'table-emp-jour') ?>>
                <canvas id="chartJour" aria-hidden="true" role="presentation"></canvas>
            </div>
            <?= renderChartDataTable('Commandes par jour', ['Date', 'Commandes'], $jourRowsEmp, 'table-emp-jour') ?>
        </div>
    </div>
</div>

<div class="card-custom mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="mb-0">Prochaines livraisons</h5>
        <a href="admin-commandes.php" class="btn btn-sm btn-outline-primary">Toutes les commandes</a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <caption class="visually-hidden">Prochaines livraisons planifiees</caption>
            <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Client</th>
                    <th scope="col">Menu</th>
                    <th scope="col">Date</th>
                    <th scope="col">Statut</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($prochainesLivraisons)): ?>
                <tr><td colspan="6" class="text-muted text-center py-3">Aucune livraison a venir.</td></tr>
            <?php else: ?>
                <?php foreach ($prochainesLivraisons as $pl): ?>
                <tr>
                    <td>#<?= (int)$pl['id'] ?></td>
                    <td><?= htmlspecialchars(trim(($pl['prenom'] ?? '') . ' ' . ($pl['nom'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars($pl['menu_titre']) ?></td>
                    <td><?= date('d/m/Y', strtotime($pl['date_livraison'])) ?> <?= substr($pl['heure_livraison'], 0, 5) ?></td>
                    <td><span class="badge bg-<?= getStatutBadgeClass($pl['statut']) ?>"><?= htmlspecialchars(getStatutLabel($pl['statut'])) ?></span></td>
                    <td class="text-end"><a href="commande-detail.php?id=<?= (int)$pl['id'] ?>" class="btn btn-sm btn-primary">Ouvrir</a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="alert alert-light border mb-0">
    <strong>Rappel employe :</strong>
    <ul class="mb-0 ps-3">
        <li>Menus, plats et <a href="admin-horaires.php">horaires</a> : modification libre dans le menu lateral.</li>
        <li>Annulation commande : contact client (GSM ou email) obligatoire + motif enregistre.</li>
        <li>Statuts commande : acceptee → preparation → livraison → livre → materiel (si pret) → terminee.</li>
        <li>Avis clients : valider ou refuser depuis <a href="admin-avis.php">Avis</a>.</li>
    </ul>
</div>

<script>
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    plugins: { legend: { display: true, position: 'bottom' } }
};

new Chart(document.getElementById('chartStatuts'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($statutsBreakdown, 'label')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($statutsBreakdown, 'count')) ?>,
            backgroundColor: ['#6366f1','#22c55e','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#64748b','#ec4899']
        }]
    },
    options: chartDefaults
});

new Chart(document.getElementById('chartJour'), {
    type: 'line',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{ label: 'Commandes', data: <?= json_encode($totaux) ?>, borderColor: '#22c55e', fill: false, tension: 0.3 }]
    },
    options: chartDefaults
});
</script>
