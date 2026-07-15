<?php include 'includes/db.php'; ?>

<?php
if($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = htmlspecialchars($_POST['name']);
    $message = htmlspecialchars($_POST['message']);
    $rating = intval($_POST['rating']);

    $stmt = $pdo->prepare("INSERT INTO reviews (name, message, rating) VALUES (?, ?, ?)");
    $stmt->execute([$name, $message, $rating]);

    $success = "Votre avis a été envoyé et sera validé par notre équipe.";
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <h2>Laisser un avis</h2>

    <?php if(isset($success)) echo "<p class='text-success'>$success</p>"; ?>

    <form method="POST">
        <input type="text" name="name" class="form-control mb-3" placeholder="Votre nom" required>

        <textarea name="message" class="form-control mb-3" placeholder="Votre avis" required></textarea>

        <select name="rating" class="form-control mb-3" required>
            <option value="">Note</option>
            <option value="5">⭐⭐⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="2">⭐⭐</option>
            <option value="1">⭐</option>
        </select>

        <button class="btn btn-primary">Envoyer</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>