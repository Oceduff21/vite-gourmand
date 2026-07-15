<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'includes/header.php';

/* SECURITE */
if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
    die("Commande invalide");
}

$id = (int) $_GET["id"];

/* RECUP COMMANDE */
$stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id'] ?? 0]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

/* SI PAS TROUVÉ */
if(!$commande){
    echo "<div class='container py-5 text-center'>";
    echo "<h2>❌ Commande introuvable</h2>";
    echo "<a href='index.php' class='btn btn-primary mt-3'>Retour accueil</a>";
    echo "</div>";
    include 'includes/footer.php';
    exit();
}
?>

<div class="container py-5 text-center">

<h1 class="mb-3">Commande confirmée ✅</h1>

<p>Merci pour votre commande !</p>

<hr>

<p><strong>Nombre de personnes :</strong> <?= $commande["nb_personnes"] ?></p>

<p><strong>Total payé :</strong> <?= number_format($commande["prix_total"],2) ?> €</p>

<p><strong>Date livraison :</strong> <?= $commande["date_livraison"] ?></p>

<p><strong>Heure :</strong> <?= $commande["heure_livraison"] ?></p>

<p><strong>Adresse :</strong><br>
<?= htmlspecialchars($commande["numero"]) ?> 
<?= htmlspecialchars($commande["rue"]) ?><br>

<?= htmlspecialchars($commande["complement"] ?? "") ?><br>

<?= htmlspecialchars($commande["code_postal"]) ?> 
<?= htmlspecialchars($commande["ville"]) ?>
</p>

<a href="index.php" class="btn btn-danger mt-4">Retour accueil</a>

</div>

<?php include 'includes/footer.php'; ?>