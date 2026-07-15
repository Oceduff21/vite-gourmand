<?php
session_start();
require 'includes/db.php';

$id = (int)($_GET["id"] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM menus WHERE id=?");
$stmt->execute([$id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$menu) die("Menu introuvable");

$prix = (float)($menu["prix"] ?? 0);
$min = (int)($menu["min_personnes"] ?? 1);
$description = $menu["description"] ?? "";

/* OPTIONS */
$options = $pdo->prepare("
SELECT mo.type, p.*
FROM menu_options mo
JOIN plats p ON p.id = mo.plat_id
WHERE mo.menu_id=?
");
$options->execute([$id]);
$options = $options->fetchAll(PDO::FETCH_ASSOC);

/* GROUP */
$group = [
"entree"=>[],
"plat"=>[],
"dessert"=>[]
];

foreach($options as $o){
$group[$o["type"]][] = $o;
}

function badge($regime){
if($regime=="vegan") return '<span class="badge bg-success">Vegan</span>';
if($regime=="vegetarien") return '<span class="badge bg-warning">Végétarien</span>';
return '<span class="badge bg-secondary">Classique</span>';
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">

<h1 class="text-center"><?= $menu["titre"] ?></h1>
<p class="text-center text-muted"><?= $description ?></p>
<h3 class="text-center text-danger"><?= number_format($prix,2) ?> €</h3>

<!-- INVITES -->
<div class="mb-4">
<label>Invités</label>
<input type="number" id="invites" value="<?= $min ?>" min="<?= $min ?>" class="form-control" onchange="updateInvites(this.value)">
</div>

<button class="btn btn-outline-primary mb-4" onclick="autoFill()">
⚡ Répartition automatique
</button>

<?php foreach($group as $type => $items): ?>

<h3 class="mt-4"><?= ucfirst($type) ?></h3>

<div class="row">
<?php foreach($items as $item): ?>
<div class="col-md-4">

<div class="card p-3 mb-3">

<img src="assets/images/<?= $item["image"] ?? 'default.jpg' ?>" class="w-100 mb-2" style="height:200px;object-fit:cover">

<h5><?= $item["nom"] ?></h5>

<?= badge($item["regime"] ?? "classique") ?>

<input type="range" min="0" max="0" value="0"
id="<?= $type ?>-<?= $item["id"] ?>"
oninput="updateChoice('<?= $type ?>',<?= $item["id"] ?>,this.value)"
class="form-range">

<div class="text-center">
<span id="label-<?= $type ?>-<?= $item["id"] ?>">0</span> invités
</div>

</div>

</div>
<?php endforeach; ?>
</div>

<div class="alert alert-info">
Restant : <span id="remain-<?= $type ?>">0</span>
</div>

<?php endforeach; ?>

<!-- RESUME -->
<div class="bg-light p-4 mt-4 rounded">
<h4>Résumé</h4>
<div id="cart"></div>
<h5>Total : <span id="total">0</span> €</h5>
<a href="commande.php?menu_id=<?= $id ?>" class="btn btn-success w-100" id="btn-commander" onclick="return goToCommande(event)">Commander</a>
</div>

</div>

<script>

let base = <?= $prix ?>;

let cart = {
invites: <?= $min ?>,
entree:{},
plat:{},
dessert:{}
};

function updateInvites(v){
cart.invites = parseInt(v);
reset();
update();
}

function reset(){
document.querySelectorAll("input[type=range]").forEach(s=>{
s.max = cart.invites;
});
}

function updateChoice(type,id,val){
cart[type][id]=parseInt(val);
document.getElementById(`label-${type}-${id}`).innerText = val;
update();
}

function sum(obj){
return Object.values(obj).reduce((a,b)=>a+b,0);
}

function autoFill(){

["entree","plat","dessert"].forEach(type=>{
let sliders = document.querySelectorAll(`[id^=${type}-]`);
let each = Math.floor(cart.invites / sliders.length);

sliders.forEach((s,i)=>{
let val = (i===0)
? cart.invites - (each*(sliders.length-1))
: each;

s.value = val;
updateChoice(type, s.id.split('-')[1], val);
});
});

}

function update(){

let html="";
let total = base * cart.invites;
let valid=true;

["entree","plat","dessert"].forEach(type=>{

let s = sum(cart[type]);
document.getElementById(`remain-${type}`).innerText = cart.invites - s;

if(s !== cart.invites) valid=false;

html += `<div>${type}: ${s}/${cart.invites}</div>`;
});

/* BONUS prix vegan */
Object.values(cart).forEach(cat=>{
if(typeof cat === "object"){
Object.keys(cat).forEach(id=>{
if(cat[id]>0){
let el = document.getElementById(`entree-${id}`) || document.getElementById(`plat-${id}`) || document.getElementById(`dessert-${id}`);
if(el){
let parent = el.closest('.card');
if(parent.innerHTML.includes("Vegan")){
total += 2 * cat[id];
}
}
}
});
}
});

document.getElementById("cart").innerHTML = html;
document.getElementById("total").innerText = total.toFixed(2);

}

function submitOrder(){

if(
sum(cart.entree)!==cart.invites ||
sum(cart.plat)!==cart.invites ||
sum(cart.dessert)!==cart.invites
){
alert("Répartition incorrecte");
return false;
}

let f=document.createElement("form");
f.method="POST";
f.action="commande.php";

let menuInput=document.createElement("input");
menuInput.type="hidden";
menuInput.name="menu_id";
menuInput.value="<?= $id ?>";
f.appendChild(menuInput);

let i=document.createElement("input");
i.type="hidden";
i.name="data";
i.value=JSON.stringify(cart);

f.appendChild(i);
document.body.appendChild(f);
f.submit();
return false;
}

function goToCommande(e){
if(
sum(cart.entree)!==cart.invites ||
sum(cart.plat)!==cart.invites ||
sum(cart.dessert)!==cart.invites
){
e.preventDefault();
alert("Veuillez repartir les plats entre les invites avant de commander.");
return false;
}

let f=document.createElement("form");
f.method="POST";
f.action="commande.php";

let menuInput=document.createElement("input");
menuInput.type="hidden";
menuInput.name="menu_id";
menuInput.value="<?= $id ?>";
f.appendChild(menuInput);

let i=document.createElement("input");
i.type="hidden";
i.name="data";
i.value=JSON.stringify(cart);
f.appendChild(i);

document.body.appendChild(f);
f.submit();
return false;
}

reset();
update();

</script>

<?php include 'includes/footer.php'; ?>