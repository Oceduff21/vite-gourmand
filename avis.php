<?php
session_start();
require 'includes/db.php';

if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit();
}

$message = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $note = $_POST["note"];
    $commentaire = $_POST["commentaire"];

    $sql = "INSERT INTO avis (user_id, note, commentaire)
            VALUES (?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    if($stmt->execute([
        $_SESSION["user_id"],
        $note,
        $commentaire
    ])){
        $message = "Avis envoyé avec succès";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">

<h2>Laisser un avis</h2>

<?php if($message): ?>
<div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Note</label>

<select name="note" class="form-control">

<option value="5">5 ⭐</option>
<option value="4">4 ⭐</option>
<option value="3">3 ⭐</option>
<option value="2">2 ⭐</option>
<option value="1">1 ⭐</option>

</select>

</div>

<div class="mb-3">

<label>Commentaire</label>

<textarea name="commentaire"
class="form-control"
required></textarea>

</div>

<button class="btn btn-primary">
Envoyer l'avis
</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>