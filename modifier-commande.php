<?php
session_start();
require 'includes/db.php';

if(!isset($_SESSION["user_id"])){
header("Location: login.php");
exit();
}

$id = $_GET["id"];

$sql = "SELECT * FROM commandes
WHERE id=? AND user_id=?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id,$_SESSION["user_id"]]);

$commande = $stmt->fetch();

if(!$commande){
die("Commande introuvable");
}

/* modification */

if($_SERVER["REQUEST_METHOD"] === "POST"){

$nb = $_POST["nb_personnes"];

$sql = "UPDATE commandes
SET nb_personnes=?
WHERE id=? AND user_id=?";

$stmt = $pdo->prepare($sql);

$stmt->execute([
$nb,
$id,
$_SESSION["user_id"]
]);

header("Location: espace-utilisateur.php");
exit();

}
?>

<?php include 'includes/header.php'; ?>

<h2>Modifier la commande</h2>

<form method="POST">

<label>Nombre de personnes</label>

<input
type="number"
name="nb_personnes"
value="<?= $commande["nb_personnes"] ?>"
class="form-control">

<br>

<button class="btn btn-primary">
Enregistrer
</button>

</form>

<?php include 'includes/footer.php'; ?>