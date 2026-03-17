<?php
session_start();
require 'includes/db.php';

if(!isset($_SESSION["user_id"])){
header("Location: login.php");
exit();
}

$sql = "
SELECT commandes.*, menus.titre
FROM commandes
JOIN menus ON commandes.menu_id = menus.id
WHERE user_id = ?
ORDER BY commandes.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["user_id"]]);

$commandes = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<h1 class="mb-4">Mon espace</h1>

<div class="row mb-4">

<div class="col-md-4">

<div class="card shadow-sm">

<div class="card-body">

<h6>Mes commandes</h6>

<h2><?= count($commandes) ?></h2>

</div>

</div>

</div>

</div>

<h3>Historique des commandes</h3>

<div class="card shadow-sm">

<div class="card-body">

<table class="table table-hover">

<thead class="table-light">

<tr>

<th>Menu</th>
<th>Personnes</th>
<th>Date</th>
<th>Statut</th>
<th>Prix</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php foreach($commandes as $commande): ?>

<tr>

<td><?= htmlspecialchars($commande["titre"]) ?></td>

<td><?= $commande["nb_personnes"] ?></td>

<td><?= $commande["date_livraison"] ?></td>

<td>

<span class="badge 
<?php
if($commande["statut"] === "en attente") echo "bg-warning";
elseif($commande["statut"] === "préparation") echo "bg-info";
elseif($commande["statut"] === "livraison") echo "bg-primary";
elseif($commande["statut"] === "terminée") echo "bg-success";
?>">
<?= $commande["statut"] ?>
</span>

<div class="commande-timeline mt-2">

<span class="<?= in_array($commande["statut"],["en attente","préparation","livraison","terminée"]) ? "active" : "" ?>">
Commande
</span>

<span class="<?= in_array($commande["statut"],["préparation","livraison","terminée"]) ? "active" : "" ?>">
Préparation
</span>

<span class="<?= in_array($commande["statut"],["livraison","terminée"]) ? "active" : "" ?>">
Livraison
</span>

<span class="<?= $commande["statut"] === "terminée" ? "active" : "" ?>">
Terminée
</span>

</div>

</td>

<td><?= $commande["prix_total"] ?> €</td>

<td>

<?php if($commande["statut"] === "en attente"): ?>

<a
href="modifier-commande.php?id=<?= $commande["id"] ?>"
class="btn btn-sm btn-warning">

Modifier

</a>

<a
href="annuler-commande.php?id=<?= $commande["id"] ?>"
class="btn btn-sm btn-danger"
onclick="return confirm('Annuler cette commande ?')">

Annuler

</a>

<?php else: ?>

<span class="text-muted">
Commande verrouillée
</span>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<?php include 'includes/footer.php'; ?>