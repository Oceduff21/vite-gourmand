<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT c.*, m.titre FROM commandes c JOIN menus m ON c.menu_id = m.id WHERE c.id = ? AND c.user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$c) {
    die('Commande introuvable');
}

$hist = $pdo->prepare('SELECT * FROM commande_historique WHERE commande_id = ? ORDER BY created_at ASC');
$hist->execute([$id]);
$historique = $hist->fetchAll(PDO::FETCH_ASSOC);

$statuts = getStatutsCommande();
$statutActuel = $c['statut'];
$ordre = array_keys($statuts);
$indexActuel = array_search($statutActuel, $ordre, true);

include 'includes/header.php';
?>

<div class="container py-5">
<h2>Suivi commande #<?= $id ?></h2>
<p class="text-muted">Menu : <?= htmlspecialchars($c['titre']) ?></p>

<div class="timeline-vertical mt-4">
<?php if (!empty($historique)): ?>
<?php foreach ($historique as $h): ?>
<div class="timeline-item active">
<div class="timeline-dot"></div>
<div class="timeline-content">
<strong><?= htmlspecialchars($statuts[$h['statut']] ?? $h['statut']) ?></strong>
<small class="text-muted d-block"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></small>
<?php if (!empty($h['note'])): ?><p class="small mb-0"><?= htmlspecialchars($h['note']) ?></p><?php endif; ?>
</div>
</div>
<?php endforeach; ?>
<?php else: ?>
<?php foreach ($ordre as $i => $key): if ($key === 'annulee') continue; ?>
<div class="timeline-item <?= ($indexActuel !== false && $i <= $indexActuel) ? 'active' : '' ?>">
<div class="timeline-dot"></div>
<div class="timeline-content"><strong><?= htmlspecialchars($statuts[$key]) ?></strong></div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<a href="espace-utilisateur.php" class="btn btn-outline-primary mt-4">Retour a mon espace</a>
</div>

<style>
.timeline-vertical { border-left: 3px solid #dee2e6; margin-left: 20px; padding-left: 30px; }
.timeline-item { position: relative; margin-bottom: 24px; }
.timeline-dot { position: absolute; left: -38px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: #dee2e6; }
.timeline-item.active .timeline-dot { background: #28a745; }
.timeline-item.active .timeline-content { color: #28a745; }
</style>

<?php include 'includes/footer.php'; ?>
