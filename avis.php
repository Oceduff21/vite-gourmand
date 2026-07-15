<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$commande_id = (int)($_GET['commande_id'] ?? $_POST['commande_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = (int)($_POST['note'] ?? 0);
    $commentaire = trim($_POST['commentaire'] ?? '');

    if ($commande_id && $note >= 1 && $note <= 5) {
        $stmt = $pdo->prepare('
            INSERT INTO avis (user_id, commande_id, note, commentaire)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$_SESSION['user_id'], $commande_id, $note, $commentaire]);
        $message = 'Avis envoye avec succes.';
    } else {
        $message = 'Veuillez remplir tous les champs correctement.';
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">

<h2>Donner un avis</h2>

<?php if (!empty($message)): ?>
<div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST">
<input type="hidden" name="commande_id" value="<?= $commande_id ?>">

<select name="note" class="form-control mb-3" required>
<option value="5">5 etoiles</option>
<option value="4">4 etoiles</option>
<option value="3">3 etoiles</option>
<option value="2">2 etoiles</option>
<option value="1">1 etoile</option>
</select>

<textarea name="commentaire" class="form-control mb-3" required></textarea>

<button class="btn btn-success">Envoyer</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>
