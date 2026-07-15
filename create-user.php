<?php
require 'includes/auth.php';
require 'includes/db.php';

if($_POST){

$email = $_POST["email"];
$password = password_hash($_POST["password"], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (email,password,role,actif)
VALUES (?,?, 'employe',1)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email,$password]);

echo "<div class='alert alert-success'>Employé créé</div>";
}
?>

<div class="container mt-5">

<h3>Créer un employé</h3>

<form method="POST">

<input type="email" name="email" class="form-control mb-2" required>
<input type="password" name="password" class="form-control mb-2" required>

<button class="btn btn-success">Créer</button>

</form>

</div>