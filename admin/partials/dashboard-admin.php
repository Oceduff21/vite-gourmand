<?php
/** Dashboard admin — chiffre d'affaires et statistiques commerciales. */
?>
<h2 class="mb-4">Tableau de bord commercial</h2>

<?php if ($dashboardError): ?>
<div class="alert alert-danger"><?= htmlspecialchars($dashboardError) ?></div>
<?php endif; ?>

<?php if (!$mongoOk): ?>
<div class="alert alert-info py-2">Statistiques via MySQL (MongoDB indisponible sur InfinityFree — normal).</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">CA total</h6>
            <strong class="fs-5 text-primary"><?= number_format($ca, 0, ',', ' ') ?> EUR</strong>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">CA ce mois</h6>
            <strong class="fs-5"><?= number_format($caMois, 0, ',', ' ') ?> EUR</strong>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">Commandes</h6>
            <strong class="fs-5"><?= $totalCommandes ?></strong>
            <div class="small text-muted"><?= $commandesMois ?> ce mois</div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">Panier moyen</h6>
            <strong class="fs-5"><?= number_format($panierMoyen, 0) ?> EUR</strong>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">En attente</h6>
            <strong class="fs-5 text-warning"><?= $enAttente ?></strong>
            <?php if ($enAttente): ?><div><a href="admin-commandes.php?statut=en_attente" class="small">Traiter (<?= $enAttente ?>)</a></div><?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">Avis a valider</h6>
            <strong class="fs-5 text-danger"><?= $avisEnAttente ?></strong>
            <?php if ($avisEnAttente): ?><div><a href="admin-avis.php" class="small">Moderer</a></div><?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card-custom">
            <h6 class="text-muted">Clients inscrits</h6>
            <strong class="fs-4"><?= $users ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-custom">
            <h6 class="text-muted">Menus actifs</h6>
            <strong class="fs-4"><?= $menusCount ?></strong>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-custom">
            <h6 class="text-muted">Source graphiques</h6>
            <strong class="fs-6"><?= htmlspecialchars($chartSource) ?></strong>
        </div>
    </div>
</div>

<form method="GET" class="row g-3 mb-4 card-custom">
    <div class="col-md-3">
        <label class="form-label">Menu</label>
        <select name="menu_id" class="form-select">
            <option value="">Tous les menus</option>
            <?php foreach ($menus as $m): ?>
            <option value="<?= (int)$m['id'] ?>" <?= $filterMenu === (int)$m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['titre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3"><label class="form-label">Date debut</label><input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($filterDebut) ?>"></div>
    <div class="col-md-3"><label class="form-label">Date fin</label><input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($filterFin) ?>"></div>
    <div class="col-md-3 d-flex align-items-end gap-2">
        <button class="btn btn-primary flex-grow-1">Filtrer</button>
        <a href="index.php" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card-custom">
            <h5 class="mb-3">CA par menu</h5>
            <div class="chart-wrap"><canvas id="chartCA"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-custom">
            <h5 class="mb-3">Repartition des statuts</h5>
            <div class="chart-wrap"><canvas id="chartStatuts"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card-custom">
            <h5 class="mb-3">Evolution CA (6 derniers mois)</h5>
            <div class="chart-wrap chart-wrap-sm"><canvas id="chartCAMois"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-custom">
            <h5 class="mb-3">Top 5 menus</h5>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Menu</th><th>Cmd</th><th>CA</th></tr></thead>
                    <tbody>
                    <?php foreach ($topMenus as $tm): ?>
                    <tr>
                        <td><?= htmlspecialchars($tm['titre']) ?></td>
                        <td><?= (int)$tm['nb'] ?></td>
                        <td><?= number_format((float)$tm['ca'], 0) ?> EUR</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($topMenus)): ?>
                    <tr><td colspan="3" class="text-muted">Aucune donnee</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card-custom mb-4">
    <h5 class="mb-3">Commandes par jour (30 derniers jours)</h5>
    <div class="chart-wrap chart-wrap-sm"><canvas id="chartJour"></canvas></div>
</div>

<script>
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    plugins: { legend: { display: true, position: 'bottom' } }
};

new Chart(document.getElementById('chartCA'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{ label: 'CA (EUR)', data: <?= json_encode($chartTotals) ?>, backgroundColor: '#6366f1' }]
    },
    options: chartDefaults
});

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

new Chart(document.getElementById('chartCAMois'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($caParMois, 'mois')) ?>,
        datasets: [{ label: 'CA mensuel', data: <?= json_encode(array_map('floatval', array_column($caParMois, 'total'))) ?>, borderColor: '#c9a227', backgroundColor: 'rgba(201,162,39,0.15)', fill: true, tension: 0.3 }]
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
