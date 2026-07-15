<?php
session_start();
require '../includes/db.php';

$error = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user["password"])){

        if($user["role"] !== "admin" && $user["role"] !== "employe"){
            $error = "Acces refuse";
        } elseif(isset($user["is_active"]) && !$user["is_active"]){
            $error = "Compte desactive";
        } else {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_role"] = $user["role"];
            header("Location: index.php");
            exit();
        }

    }else{
        $error = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Login Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="card p-4 shadow" style="width:350px">

<h3 class="mb-3 text-center">Admin Login</h3>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<input type="email" name="email" class="form-control mb-3" placeholder="Email" required>

<input type="password" name="password" class="form-control mb-3" placeholder="Mot de passe" required>

<button class="btn btn-primary w-100">Connexion</button>

</form>

</div>


</body>
</html>