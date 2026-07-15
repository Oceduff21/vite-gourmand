<?php
session_start();
require '../includes/db.php';

if(!isset($_SESSION["admin"])){
    header("Location: login.php");
    exit();
}

// Valider
if(isset($_GET['validate'])){
    $id = intval($_GET['validate']);
    $pdo->prepare("UPDATE avis SET is_validated = 1 WHERE id = ?")
        ->execute([$id]);
}

// Supprimer
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM avis WHERE id = ?")
        ->execute([$id]);
}

$avis = $pdo->query("SELECT * FROM avis ORDER BY created_at DESC");
?>

<h2>Gestion des avis</h2>

<a href="dashboard.php">⬅ Retour</a>

<table border="1" cellpadding="10">
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Note</th>
    <th>Commentaire</th>
    <th>Statut</th>
    <th>Action</th>
</tr>

<?php foreach($avis as $a): ?>
<tr>
    <td><?= $a['id'] ?></td>
    <td><?= $a['user_id'] ?></td>
    <td><?= str_repeat("⭐", $a['note']) ?></td>
    <td><?= $a['commentaire'] ?></td>
    <td>
        <?= $a['is_validated'] ? "✅ Validé" : "⏳ En attente" ?>
    </td>
    <td>
        <?php if(!$a['is_validated']): ?>
            <a href="?validate=<?= $a['id'] ?>">Valider</a> |
        <?php endif; ?>
        <a href="?delete=<?= $a['id'] ?>">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>

</table>