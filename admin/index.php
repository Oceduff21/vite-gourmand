<?php
session_start();
require '../includes/db.php';
require '../includes/helpers.php';
require '../includes/mongo.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: ../index.php');
    exit();
}

$filterMenu = (int)($_GET['menu_id'] ?? 0);
$filterDebut = $_GET['date_debut'] ?? '';
$filterFin = $_GET['date_fin'] ?? '';

$totalCommandes = $pdo->query('SELECT COUNT(*) FROM commandes')->fetchColumn();
$ca = $pdo->query('SELECT SUM(prix_total) FROM commandes')->fetchColumn();
$users = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$menus = $pdo->query('SELECT id, titre FROM menus ORDER BY titre')->fetchAll();

$mongoOk = mongoIsAvailable();

$chartLabels = [];
$chartTotals = [];
$chartCounts = [];
$chartSource = 'MongoDB';

if ($mongoOk) {
    $mongoCA = mongoGetCAparMenu($filterMenu ?: null, $filterDebut ?: null, $filterFin ?: null);
    $chartLabels = array_column($mongoCA, 'titre');
    $chartTotals = array_column($mongoCA, 'total');
    $chartCounts = array_column($mongoCA, 'count');
} else {
    $chartSource = 'MySQL';
    $sql = 'SELECT m.titre, COALESCE(SUM(c.prix_total), 0) AS total, COUNT(c.id) AS count
            FROM commandes c
            JOIN menus m ON c.menu_id = m.id
            WHERE 1=1';
    $params = [];
    if ($filterMenu) {
        $sql .= ' AND c.menu_id = ?';
        $params[] = $filterMenu;
    }
    if ($filterDebut) {
        $sql .= ' AND DATE(c.created_at) >= ?';
        $params[] = $filterDebut;
    }
    if ($filterFin) {
        $sql .= ' AND DATE(c.created_at) <= ?';
        $params[] = $filterFin;
    }
    $sql .= ' GROUP BY m.id, m.titre ORDER BY m.titre';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    foreach ($stmt->fetchAll() as $row) {
        $chartLabels[] = $row['titre'];
        $chartTotals[] = (float)$row['total'];
        $chartCounts[] = (int)$row['count'];
    }
}

$data = $pdo->query('SELECT DATE(created_at) as date, COUNT(*) as total FROM commandes GROUP BY DATE(created_at) ORDER BY date ASC')->fetchAll();
$dates = array_column($data, 'date');
$totaux = array_map('intval', array_column($data, 'total'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{background:#f1f5f9;font-family:'Segoe UI',sans-serif}
.sidebar{position:fixed;width:220px;height:100vh;background:white;border-right:1px solid #e5e7eb;padding:20px}
.sidebar a{display:block;padding:10px;margin-bottom:8px;border-radius:10px;color:#374151;text-decoration:none}
.sidebar a:hover,.sidebar .active{background:#6366f1;color:white}
.content{margin-left:240px;padding:30px}
.card-kpi,.chart-box{background:white;border-radius:15px;padding:20px;box-shadow:0 5px 15px rgba(0,0,0,0.05)}
</style>
</head>
<body>
<div class="sidebar">
<h4 class="mb-4">Admin</h4>
<a href="index.php" class="active">Dashboard</a>
<a href="admin-commandes.php">Commandes</a>
<a href="admin-menus.php">Menus</a>
<a href="admin-plats.php">Plats</a>
<a href="admin-users.php">Utilisateurs</a>
<a href="admin-avis.php">Avis</a>
<hr><a href="../index.php">Retour site</a>
</div>
<div class="content">
<h2 class="mb-4">Dashboard</h2>

<?php if (!$mongoOk): ?>
<div class="alert alert-info">Statistiques avancees via MySQL (MongoDB indisponible sur cet hebergeur — normal sur InfinityFree).</div>
<?php endif; ?>

<div class="row mb-4">
<div class="col-md-4"><div class="card-kpi"><h6>Chiffre d'affaires (MySQL)</h6><strong><?= number_format($ca ?? 0, 2) ?> EUR</strong></div></div>
<div class="col-md-4"><div class="card-kpi"><h6>Commandes</h6><strong><?= (int)$totalCommandes ?></strong></div></div>
<div class="col-md-4"><div class="card-kpi"><h6>Utilisateurs</h6><strong><?= (int)$users ?></strong></div></div>
</div>

<form method="GET" class="row g-3 mb-4 chart-box">
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
<div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Filtrer</button></div>
</form>

<div class="row g-4 mb-4">
<div class="col-md-6 chart-box">
<h5>CA par menu (<?= htmlspecialchars($chartSource) ?>)</h5>
<canvas id="chartMongoCA" aria-label="Graphique chiffre affaires par menu"></canvas>
</div>
<div class="col-md-6 chart-box">
<h5>Commandes par menu (<?= htmlspecialchars($chartSource) ?>)</h5>
<canvas id="chartMongoCount" aria-label="Graphique nombre commandes par menu"></canvas>
</div>
</div>

<div class="chart-box mb-4">
<h5>Commandes par jour (MySQL)</h5>
<canvas id="chartSQL" aria-label="Graphique commandes par jour"></canvas>
</div>
</div>
<script>
new Chart(document.getElementById('chartMongoCA'), {type:'bar', data:{labels:<?= json_encode($chartLabels) ?>, datasets:[{label:'CA (EUR)', data:<?= json_encode($chartTotals) ?>, backgroundColor:'#6366f1'}]}});
new Chart(document.getElementById('chartMongoCount'), {type:'bar', data:{labels:<?= json_encode($chartLabels) ?>, datasets:[{label:'Nb commandes', data:<?= json_encode($chartCounts) ?>, backgroundColor:'#22c55e'}]}});
new Chart(document.getElementById('chartSQL'), {type:'line', data:{labels:<?= json_encode($dates) ?>, datasets:[{label:'Commandes', data:<?= json_encode($totaux) ?>, borderColor:'#f59e0b', fill:true, tension:0.4}]}});
</script>
</body>
</html>
