<?php
session_start();
require 'includes/db.php';

if(!isset(\$_SESSION['user_id'])){ header('Location: login.php'); exit(); }

\$commande_id = (int)(\$_GET['commande_id'] ?? 0);

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $note = $_POST["note"];
    $commentaire = $_POST["commentaire"];

    $stmt = $pdo->prepare("
    INSERT INTO avis (user_id, commande_id, note, commentaire)
    VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $_SESSION["user_id"],
        $commande_id,
        $note,
        $commentaire
    ]);

    echo "<div class='alert alert-success'>Avis envoyé</div>";
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">

<h2>Donner un avis</h2>

<form method="POST">

<select name="note" class="form-control mb-3">
<option value="5">⭐⭐⭐⭐⭐</option>
<option value="4">⭐⭐⭐⭐</option>
<option value="3">⭐⭐⭐</option>
<option value="2">⭐⭐</option>
<option value="1">⭐</option>
</select>

<textarea name="commentaire" class="form-control mb-3"></textarea>

<button class="btn btn-success">Envoyer</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>