<?php
require 'includes/db.php';

$message = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $email = $_POST["email"];
    $gsm = $_POST["gsm"];
    $adresse = $_POST["adresse"];
    $password = $_POST["password"];

    // Hash mot de passe
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (nom, prenom, email, gsm, adresse, password)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    if($stmt->execute([$nom,$prenom,$email,$gsm,$adresse,$passwordHash])){
        $message = "Compte créé avec succès";
    } else {
        $message = "Erreur lors de la création";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">

<h2>Créer un compte</h2>

<?php if($message): ?>
<div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Nom</label>
<input type="text" name="nom" class="form-control" required>
</div>

<div class="mb-3">
<label>Prénom</label>
<input type="text" name="prenom" class="form-control" required>
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label>GSM</label>
<input type="text" name="gsm" class="form-control" required>
</div>

<div class="mb-3">
<label>Adresse</label>
<textarea name="adresse" class="form-control"></textarea>
</div>

<div class="mb-3">
<label>Mot de passe</label>
<input type="password" name="password" class="form-control" required>
</div>

<button class="btn btn-primary">Créer le compte</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>