<?php
session_start();
require 'includes/db.php';

if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* USER */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

/* COMMANDES */
$stmt = $pdo->prepare("
SELECT c.*, m.titre 
FROM commandes c
JOIN menus m ON c.menu_id = m.id
WHERE c.user_id = ?
ORDER BY c.id DESC
");
$stmt->execute([$user_id]);
$commandes = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">

<h2 class="mb-4">Mon espace</h2>

<!-- ================= INFOS USER ================= -->

<div class="card p-4 mb-5">
<h4>Mes informations</h4>

<form method="POST" action="update-user.php">

<div class="row g-3">

<!-- NOM -->
<div class="col-md-4">
<label for="nom" class="form-label">Nom</label>
<input type="text" id="nom" name="nom" class="form-control"
value="<?= $user["nom"] ?>" required>
</div>

<!-- PRENOM -->
<div class="col-md-4">
<label for="prenom" class="form-label">Prénom</label>
<input type="text" id="prenom" name="prenom" class="form-control"
value="<?= $user["prenom"] ?>" required>
</div>

<!-- EMAIL -->
<div class="col-md-4">
<label for="email" class="form-label">Email</label>
<input type="email" id="email" name="email" class="form-control"
value="<?= $user["email"] ?>" required>
</div>

<!-- TELEPHONE -->
<div class="col-md-4">
<label for="telephone" class="form-label">Téléphone</label>
<input type="tel" id="telephone" name="telephone" class="form-control"
value="<?= $user["telephone"] ?? '' ?>"
pattern="[0-9]{10}" 
placeholder="0612345678"
required>
</div>

<!-- DATE NAISSANCE -->
<div class="col-md-4">
<label for="date_naissance" class="form-label">Date de naissance</label>
<input type="date" id="date_naissance" name="date_naissance"
class="form-control"
value="<?= $user["date_naissance"] ?? '' ?>"
required>
</div>

</div>

<hr>

<h5 class="mt-3">Adresse postale</h5>

<div class="row g-3">

<!-- RUE -->
<div class="col-md-6">
<label for="rue" class="form-label">Rue</label>
<input type="text" id="rue" name="rue" class="form-control"
value="<?= $user["rue"] ?? '' ?>"
required>
</div>

<!-- NUMERO -->
<div class="col-md-2">
<label for="numero" class="form-label">Numéro</label>
<input type="text" id="numero" name="numero" class="form-control"
value="<?= $user["numero"] ?? '' ?>"
required>
</div>

<!-- COMPLEMENT -->
<div class="col-md-4">
<label for="complement" class="form-label">Complément</label>
<input type="text" id="complement" name="complement" class="form-control"
value="<?= $user["complement"] ?? '' ?>">
</div>

<!-- CODE POSTAL -->
<div class="col-md-4">
<label for="code_postal" class="form-label">Code postal</label>
<input type="text" id="code_postal" name="code_postal"
class="form-control"
pattern="[0-9]{5}"
placeholder="33000"
value="<?= $user["code_postal"] ?? '' ?>"
required>
</div>

<!-- VILLE -->
<div class="col-md-8">
<label for="ville" class="form-label">Ville</label>
<input type="text" id="ville" name="ville" class="form-control"
value="<?= $user["ville"] ?? '' ?>"
required>
</div>

<!-- BOUTON -->
<div class="col-md-12">
<button class="btn btn-primary mt-3">
Mettre à jour
</button>
</div>

</div>

</form>
</div>

<!-- ================= COMMANDES ================= -->

<h4>Mes commandes</h4>

<?php if(empty($commandes)): ?>
<p>Aucune commande</p>
<?php endif; ?>

<?php foreach($commandes as $c): ?>

<div class="card mb-4 shadow-sm">

<div class="card-body">

<h5><?= htmlspecialchars($c["titre"]) ?></h5>

<p>
📅 <?= $c["date_livraison"] ?> à <?= $c["heure_livraison"] ?><br>
👥 <?= $c["nb_personnes"] ?> personnes<br>
💰 <?= $c["prix_total"] ?> €<br>
📍 <?= $c["numero"] ?> <?= $c["rue"] ?>, <?= $c["ville"] ?>
</p>

<p>
Statut :
<strong class="
<?php
if($c["statut"] == "en_attente") echo "text-warning";
if($c["statut"] == "acceptee") echo "text-primary";
if($c["statut"] == "terminee") echo "text-success";
?>
">
<?= $c["statut"] ?>
</strong>
</p>

<!-- ACTIONS -->

<div class="d-flex gap-2 flex-wrap">

<?php if($c["statut"] == "en_attente"): ?>

<a href="modifier-commande.php?id=<?= $c["id"] ?>" class="btn btn-warning btn-sm">
Modifier
</a>

<a href="annuler-commande.php?id=<?= $c["id"] ?>" class="btn btn-danger btn-sm"
onclick="return confirm('Annuler cette commande ?')">
Annuler
</a>

<?php endif; ?>

<?php if(in_array($c['statut'], ['acceptee','en_preparation','en_livraison','livre','en_attente_materiel','terminee'])): ?>

<a href="suivi-commande.php?id=<?= $c["id"] ?>" class="btn btn-info btn-sm">
Suivi
</a>

<?php endif; ?>

<?php if($c["statut"] == "terminee"): ?>

<a href="avis.php?commande_id=<?= $c["id"] ?>" class="btn btn-success btn-sm">
Donner un avis
</a>

<?php endif; ?>

</div>

</div>
</div>

<?php endforeach; ?>

</div>

<?php include 'includes/footer.php'; ?>