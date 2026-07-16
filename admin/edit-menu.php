<?php
session_start();
require '../includes/db.php';

if(!isset($_GET["id"])) die("ID manquant");

$id = (int) $_GET["id"];

$stmt = $pdo->prepare("SELECT * FROM menus WHERE id=?");
$stmt->execute([$id]);
$menu = $stmt->fetch();

if(!$menu) die("Menu introuvable");

/* DATA */
$entrees = $pdo->query("SELECT * FROM plats WHERE type='entree'")->fetchAll();
$plats = $pdo->query("SELECT * FROM plats WHERE type='plat'")->fetchAll();
$desserts = $pdo->query("SELECT * FROM plats WHERE type='dessert'")->fetchAll();
$boissons = $pdo->query("SELECT * FROM boissons")->fetchAll();

/* OPTIONS ACTUELLES */
$options = $pdo->prepare("SELECT * FROM menu_options WHERE menu_id=?");
$options->execute([$id]);
$options = $options->fetchAll();

$selected = [];
foreach($options as $o){
    $selected[$o["type"]][] = $o["plat_id"];
}

/* BOISSONS */
$mb = $pdo->prepare("SELECT boisson_id FROM menu_boissons WHERE menu_id=?");
$mb->execute([$id]);
$selectedBoissons = array_column($mb->fetchAll(), 'boisson_id');

/* UPDATE */
if($_SERVER["REQUEST_METHOD"] === "POST"){

    if(count($_POST["entrees"] ?? []) != 3) die("❌ 3 entrées obligatoires");
    if(count($_POST["plats"] ?? []) != 3) die("❌ 3 plats obligatoires");
    if(count($_POST["desserts"] ?? []) != 3) die("❌ 3 desserts obligatoires");

    $pdo->beginTransaction();

    $pdo->prepare("
        UPDATE menus SET titre=?, prix=?, min_personnes=? WHERE id=?
    ")->execute([
        $_POST["titre"],
        $_POST["prix"],
        $_POST["min"],
        $id
    ]);

    /* RESET */
    $pdo->prepare("DELETE FROM menu_options WHERE menu_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM menu_boissons WHERE menu_id=?")->execute([$id]);

    /* INSERT */
    foreach($_POST["entrees"] as $e){
        $pdo->prepare("INSERT INTO menu_options (menu_id,plat_id,type) VALUES (?,?, 'entree')")
        ->execute([$id,$e]);
    }

    foreach($_POST["plats"] as $p){
        $pdo->prepare("INSERT INTO menu_options (menu_id,plat_id,type) VALUES (?,?, 'plat')")
        ->execute([$id,$p]);
    }

    foreach($_POST["desserts"] as $d){
        $pdo->prepare("INSERT INTO menu_options (menu_id,plat_id,type) VALUES (?,?, 'dessert')")
        ->execute([$id,$d]);
    }

    foreach($_POST["boissons"] ?? [] as $b){
        $pdo->prepare("INSERT INTO menu_boissons (menu_id,boisson_id) VALUES (?,?)")
        ->execute([$id,$b]);
    }

    $pdo->commit();

    header("Location: admin-menus.php");
    exit();
}
?>

<?php include 'partials/layout.php'; ?>

<h2>Modifier le menu</h2>

<form method="POST">

<input name="titre" value="<?= $menu["titre"] ?>" class="form-control mb-3">
<input name="prix" value="<?= $menu["prix"] ?>" class="form-control mb-3">
<input name="min" value="<?= $menu["min_personnes"] ?>" class="form-control mb-3">

<h5>Entrées</h5>
<?php foreach($entrees as $e): ?>
<label>
<input type="checkbox" name="entrees[]" value="<?= $e["id"] ?>"
<?= in_array($e["id"], $selected["entree"] ?? []) ? 'checked' : '' ?>>
<?= $e["nom"] ?>
</label><br>
<?php endforeach; ?>

<h5>Plats</h5>
<?php foreach($plats as $p): ?>
<label>
<input type="checkbox" name="plats[]" value="<?= $p["id"] ?>"
<?= in_array($p["id"], $selected["plat"] ?? []) ? 'checked' : '' ?>>
<?= $p["nom"] ?>
</label><br>
<?php endforeach; ?>

<h5>Desserts</h5>
<?php foreach($desserts as $d): ?>
<label>
<input type="checkbox" name="desserts[]" value="<?= $d["id"] ?>"
<?= in_array($d["id"], $selected["dessert"] ?? []) ? 'checked' : '' ?>>
<?= $d["nom"] ?>
</label><br>
<?php endforeach; ?>

<h5>Boissons</h5>
<?php foreach($boissons as $b): ?>
<label>
<input type="checkbox" name="boissons[]" value="<?= $b["id"] ?>"
<?= in_array($b["id"], $selectedBoissons) ? 'checked' : '' ?>>
<?= $b["nom"] ?>
</label><br>
<?php endforeach; ?>

<button class="btn btn-primary mt-4">Mettre à jour</button>

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
