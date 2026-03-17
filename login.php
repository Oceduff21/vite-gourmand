<?php
session_start();
require 'includes/db.php';

$message = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){

$email = $_POST["email"];
$password = $_POST["password"];

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);

$user = $stmt->fetch();

if($user && password_verify($password, $user["password"])){

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_role"] = $user["role"];
$_SESSION["user_nom"] = $user["nom"];

header("Location: index.php");
exit();

}else{

$message = "Email ou mot de passe incorrect";

}

}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">

<h2>Connexion</h2>

<?php if($message): ?>
<div class="alert alert-danger"><?= $message ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label>Mot de passe</label>
<input type="password" name="password" class="form-control" required>
</div>

<button class="btn btn-primary">Se connecter</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>