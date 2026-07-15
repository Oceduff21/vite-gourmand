<?php
require '../includes/db.php';

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

$avis = $pdo->query("SELECT * FROM avis WHERE is_validated = 0 ORDER BY created_at DESC");
?>

<h2>Avis en attente</h2>

<table border="1" cellpadding="10">
<tr>
    <th>ID</th>
    <th>User</th>
    <th>Note</th>
    <th>Commentaire</th>
    <th>Action</th>
</tr>

<?php foreach($avis as $a): ?>
<tr>
    <td><?= $a['id'] ?></td>
    <td><?= $a['user_id'] ?></td>
    <td><?= str_repeat("⭐", $a['note']) ?></td>
    <td><?= $a['commentaire'] ?></td>
    <td>
        <a href="?validate=<?= $a['id'] ?>">Valider</a> |
        <a href="?delete=<?= $a['id'] ?>">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>

</table>