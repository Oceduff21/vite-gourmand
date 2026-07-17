<?php
/** Dashboard admin — chiffre d'affaires et statistiques commerciales (MongoDB + graphiques comparatifs). */
$caFiltre = array_sum($chartTotals ?? []);
$commandesFiltre = array_sum($chartCounts ?? []);
?>
<h1 class="h2 mb-4">Tableau de bord administrateur</h1>

<?php if ($dashboardError): ?>
<div class="alert alert-danger"><?= htmlspecialchars($dashboardError) ?></div>
<?php endif; ?>

<?php if ($mongoOk): ?>
<div class="alert alert-success py-2">
    <i class="fa-solid fa-database me-1"></i>
    Statistiques commandes / CA par menu : source <strong>MongoDB</strong> (base non relationnelle).
</div>
<?php else: ?>
<div class="alert alert-info py-2">
    MongoDB indisponible (normal sur InfinityFree) — statistiques calculees depuis MySQL.
    En local : lancez <code>php scripts/sync-mongo.php</code> apres installation MongoDB.
</div>
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
            <h6 class="text-muted small mb-1">CA filtre</h6>
            <strong class="fs-5"><?= number_format($caFiltre, 0, ',', ' ') ?> EUR</strong>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">Commandes (filtre)</h6>
            <strong class="fs-5"><?= (int)$commandesFiltre ?></strong>
            <div class="small text-muted"><?= $totalCommandes ?> au total</div>
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
            <?php if ($enAttente): ?><div><a href="admin-commandes.php?statut=en_attente" class="small">Traiter</a></div><?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card-custom text-center">
            <h6 class="text-muted small mb-1">Avis a valider</h6>
            <strong class="fs-5 text-danger"><?= $avisEnAttente ?></strong>
            <?php if ($avisEnAttente): ?><div><a href="admin-avis.php?filter=pending" class="small">Moderer</a></div><?php endif; ?>
        </div>
    </div>
</div>

<form method="GET" class="row g-3 mb-4 card-custom" aria-label="Filtrer les statistiques par menu et periode">
    <div class="col-12"><h2 class="h5 mb-0">Filtres CA et commandes par menu</h2></div>
    <div class="col-md-3">
        <label class="form-label" for="filtre-dash-menu">Menu</label>
        <select name="menu_id" id="filtre-dash-menu" class="form-select">
            <option value="">Tous les menus</option>
            <?php foreach ($menus as $m): ?>
            <option value="<?= (int)$m['id'] ?>" <?= $filterMenu === (int)$m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['titre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3"><label class="form-label" for="filtre-dash-debut">Date debut</label><input type="date" name="date_debut" id="filtre-dash-debut" class="form-control" value="<?= htmlspecialchars($filterDebut) ?>"></div>
    <div class="col-md-3"><label class="form-label" for="filtre-dash-fin">Date fin</label><input type="date" name="date_fin" id="filtre-dash-fin" class="form-control" value="<?= htmlspecialchars($filterFin) ?>"></div>
    <div class="col-md-3 d-flex align-items-end gap-2">
        <button class="btn btn-primary flex-grow-1">Appliquer</button>
        <a href="index.php" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card-custom">
            <h5 class="mb-1">Nombre de commandes par menu</h5>
            <p class="small text-muted mb-3">Comparaison entre menus — source <?= htmlspecialchars($chartSource) ?></p>
            <?php
            $cmdTableRows = [];
            foreach ($chartLabels as $i => $label) {
                $cmdTableRows[] = [$label, (int)($chartCounts[$i] ?? 0)];
            }
            ?>
            <div class="chart-wrap" <?= chartFigureAttrs('Nombre de commandes par menu', 'table-cmd-menu') ?>>
                <canvas id="chartCommandesMenu" aria-hidden="true" role="presentation"></canvas>
            </div>
            <?= renderChartDataTable('Commandes par menu', ['Menu', 'Commandes'], $cmdTableRows, 'table-cmd-menu') ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-custom">
            <h5 class="mb-1">Chiffre d'affaires par menu</h5>
            <p class="small text-muted mb-3">Memes filtres — source <?= htmlspecialchars($chartSource) ?></p>
            <?php
            $caTableRows = [];
            foreach ($chartLabels as $i => $label) {
                $caTableRows[] = [$label, number_format((float)($chartTotals[$i] ?? 0), 2, ',', ' ') . ' EUR'];
            }
            ?>
            <div class="chart-wrap" <?= chartFigureAttrs('Chiffre d affaires par menu', 'table-ca-menu') ?>>
                <canvas id="chartCA" aria-hidden="true" role="presentation"></canvas>
            </div>
            <?= renderChartDataTable('CA par menu', ['Menu', 'CA'], $caTableRows, 'table-ca-menu') ?>
        </div>
    </div>
</div>

<?php if (!empty($chartLabels)): ?>
<div class="card-custom mb-4">
    <h5 class="mb-3">Detail par menu (periode filtree)</h5>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <caption class="visually-hidden">Detail chiffre d affaires et commandes par menu</caption>
            <thead class="table-light">
                <tr>
                    <th scope="col">Menu</th>
                    <th scope="col" class="text-end">Commandes</th>
                    <th scope="col" class="text-end">CA (EUR)</th>
                    <th scope="col" class="text-end">Panier moyen</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($chartLabels as $i => $label): ?>
            <?php
                $cnt = (int)($chartCounts[$i] ?? 0);
                $tot = (float)($chartTotals[$i] ?? 0);
                $pm = $cnt > 0 ? $tot / $cnt : 0;
            ?>
            <tr>
                <td><?= htmlspecialchars($label) ?></td>
                <td class="text-end"><?= $cnt ?></td>
                <td class="text-end"><?= number_format($tot, 2, ',', ' ') ?></td>
                <td class="text-end"><?= number_format($pm, 2, ',', ' ') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr class="fw-semibold">
                    <td>Total</td>
                    <td class="text-end"><?= (int)$commandesFiltre ?></td>
                    <td class="text-end"><?= number_format($caFiltre, 2, ',', ' ') ?></td>
                    <td class="text-end"><?= $commandesFiltre > 0 ? number_format($caFiltre / $commandesFiltre, 2, ',', ' ') : '—' ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card-custom">
            <h5 class="mb-3">Repartition des statuts</h5>
            <?php
            $statutRows = [];
            foreach ($statutsBreakdown as $row) {
                $statutRows[] = [$row['label'], (int)$row['count']];
            }
            ?>
            <div class="chart-wrap" <?= chartFigureAttrs('Repartition des statuts de commande', 'table-statuts') ?>>
                <canvas id="chartStatuts" aria-hidden="true" role="presentation"></canvas>
            </div>
            <?= renderChartDataTable('Statuts commandes', ['Statut', 'Nombre'], $statutRows, 'table-statuts') ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-custom">
            <h5 class="mb-3">Top 5 menus (MySQL)</h5>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <caption class="visually-hidden">Top 5 menus par volume et chiffre d affaires</caption>
                    <thead><tr><th scope="col">Menu</th><th scope="col">Cmd</th><th scope="col">CA</th></tr></thead>
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

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card-custom">
            <h5 class="mb-3">Evolution CA (6 derniers mois)</h5>
            <?php
            $caMoisRows = [];
            foreach ($caParMois as $row) {
                $caMoisRows[] = [$row['mois'], number_format((float)$row['total'], 2, ',', ' ') . ' EUR'];
            }
            ?>
            <div class="chart-wrap chart-wrap-sm" <?= chartFigureAttrs('Evolution du CA sur 6 mois', 'table-ca-mois') ?>>
                <canvas id="chartCAMois" aria-hidden="true" role="presentation"></canvas>
            </div>
            <?= renderChartDataTable('CA mensuel', ['Mois', 'CA'], $caMoisRows, 'table-ca-mois') ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-custom">
            <h5 class="mb-3">Acces rapides admin</h5>
            <div class="d-grid gap-2">
                <a href="admin-users.php?tab=employes" class="btn btn-outline-primary btn-sm">Gerer les employes</a>
                <a href="admin-commandes.php" class="btn btn-outline-primary btn-sm">Commandes</a>
                <a href="admin-menus.php" class="btn btn-outline-secondary btn-sm">Menus</a>
            </div>
            <p class="small text-muted mt-3 mb-0">L'administrateur dispose de toutes les fonctions employe + gestion des comptes et statistiques financieres.</p>
        </div>
    </div>
</div>

<div class="card-custom mb-4">
    <h5 class="mb-3">Commandes par jour (30 derniers jours)</h5>
    <?php
    $jourRows = [];
    foreach ($dates as $i => $d) {
        $jourRows[] = [$d, (int)($totaux[$i] ?? 0)];
    }
    ?>
    <div class="chart-wrap chart-wrap-sm" <?= chartFigureAttrs('Commandes par jour sur 30 jours', 'table-cmd-jour') ?>>
        <canvas id="chartJour" aria-hidden="true" role="presentation"></canvas>
    </div>
    <?= renderChartDataTable('Commandes par jour', ['Date', 'Commandes'], $jourRows, 'table-cmd-jour') ?>
</div>

<script>
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    plugins: { legend: { display: true, position: 'bottom' } }
};

new Chart(document.getElementById('chartCommandesMenu'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Nombre de commandes',
            data: <?= json_encode($chartCounts) ?>,
            backgroundColor: '#22c55e'
        }]
    },
    options: chartDefaults
});

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
