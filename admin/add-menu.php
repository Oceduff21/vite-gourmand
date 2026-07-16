<?php
session_start();
require '../includes/db.php';

/* DATA */
$entrees = $pdo->query("SELECT * FROM plats WHERE type='entree'")->fetchAll();
$plats = $pdo->query("SELECT * FROM plats WHERE type='plat'")->fetchAll();
$desserts = $pdo->query("SELECT * FROM plats WHERE type='dessert'")->fetchAll();
$boissons = $pdo->query("SELECT * FROM boissons")->fetchAll();

/* SAVE */
if($_SERVER["REQUEST_METHOD"] === "POST"){

    if(count($_POST["entrees"] ?? []) != 3) die("3 entrées obligatoires");
    if(count($_POST["plats"] ?? []) != 3) die("3 plats obligatoires");
    if(count($_POST["desserts"] ?? []) != 3) die("3 desserts obligatoires");

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO menus (titre, prix, min_personnes)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $_POST["titre"],
        $_POST["prix"],
        $_POST["min"]
    ]);

    $menu_id = $pdo->lastInsertId();

    /* OPTIONS */
    foreach($_POST["entrees"] as $e){
        $pdo->prepare("
            INSERT INTO menu_options (menu_id, plat_id, type)
            VALUES (?, ?, 'entree')
        ")->execute([$menu_id,$e]);
    }

    foreach($_POST["plats"] as $p){
        $pdo->prepare("
            INSERT INTO menu_options (menu_id, plat_id, type)
            VALUES (?, ?, 'plat')
        ")->execute([$menu_id,$p]);
    }

    foreach($_POST["desserts"] as $d){
        $pdo->prepare("
            INSERT INTO menu_options (menu_id, plat_id, type)
            VALUES (?, ?, 'dessert')
        ")->execute([$menu_id,$d]);
    }

    /* BOISSONS */
    foreach($_POST["boissons"] ?? [] as $b){
        $pdo->prepare("
            INSERT INTO menu_boissons (menu_id, boisson_id)
            VALUES (?, ?)
        ")->execute([$menu_id,$b]);
    }

    $pdo->commit();

    header("Location: admin-menus.php");
    exit();
}
?>

<?php include 'partials/layout.php'; ?>

<h2>Créer un menu</h2>

<form method="POST">

<input name="titre" placeholder="Titre" class="form-control mb-3" required>
<input name="prix" type="number" step="0.01" class="form-control mb-3" required>
<input name="min" type="number" class="form-control mb-3" required>

<h5>Entrées (3 obligatoires)</h5>
<?php foreach($entrees as $e): ?>
<label>
<input type="checkbox" name="entrees[]" value="<?= $e["id"] ?>">
<?= $e["nom"] ?>
</label><br>
<?php endforeach; ?>

<h5>Plats (3 obligatoires)</h5>
<?php foreach($plats as $p): ?>
<label>
<input type="checkbox" name="plats[]" value="<?= $p["id"] ?>">
<?= $p["nom"] ?>
</label><br>
<?php endforeach; ?>

<h5>Desserts (3 obligatoires)</h5>
<?php foreach($desserts as $d): ?>
<label>
<input type="checkbox" name="desserts[]" value="<?= $d["id"] ?>">
<?= $d["nom"] ?>
</label><br>
<?php endforeach; ?>

<h5>Boissons</h5>
<?php foreach($boissons as $b): ?>
<label>
<input type="checkbox" name="boissons[]" value="<?= $b["id"] ?>">
<?= $b["nom"] ?>
</label><br>
<?php endforeach; ?>

<button class="btn btn-success mt-3">Créer</button>

</form>

<script>
document.querySelectorAll('input[type=checkbox]').forEach(cb => {
cb.addEventListener('change', function(){

let name = this.name;
let checked = document.querySelectorAll(`input[name="${name}"]:checked`);

if(checked.length > 3){
this.checked = false;
alert("Maximum 3 choix");
}

});
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
