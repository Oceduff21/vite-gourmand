<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/mongo.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$menu_id = (int)($_POST['menu_id'] ?? 0);
$quantite = (int)($_POST['quantite'] ?? 0);
$rue = trim($_POST['rue'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$complement = trim($_POST['complement'] ?? '');
$code_postal = trim($_POST['code_postal'] ?? '');
$ville = trim($_POST['ville'] ?? '');
$date = $_POST['date'] ?? '';
$heure = $_POST['heure'] ?? '';

if (!verifyCsrf($_POST['csrf_token'] ?? '')) { die('Token CSRF invalide.'); }

if (!$menu_id || !$quantite || !$rue || !$numero || !$code_postal || !$ville || !$date || !$heure) {
    die('Tous les champs obligatoires doivent etre remplis.');
}

$stmt = $pdo->prepare('SELECT * FROM menus WHERE id = ?');
$stmt->execute([$menu_id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$menu) {
    die('Menu invalide');
}

$min = (int)$menu['min_personnes'];
$prix = (float)$menu['prix'];
$stock = (int)($menu['stock'] ?? 0);

if ($quantite < $min) {
    die('Minimum ' . $min . ' personnes requis.');
}
if ($stock <= 0) {
    die('Stock insuffisant pour ce menu.');
}

$totalMenu = $quantite * $prix;
$ville_lower = strtolower($ville);
$livraison = 0.0;
if ($ville_lower !== 'bordeaux') {
    $livraison = 5.0;
    if (str_starts_with($code_postal, '33')) {
        $livraison += 8 * 0.59;
    } elseif (str_starts_with($code_postal, '24') || str_starts_with($code_postal, '47')) {
        $livraison += 15 * 0.59;
    } else {
        $livraison += 25 * 0.59;
    }
    $livraison = round($livraison, 2);
}

$reduction = 0.0;
if ($quantite >= ($min + 5)) {
    $reduction = round($totalMenu * 0.10, 2);
}
$total = round($totalMenu + $livraison - $reduction, 2);

$pdo->beginTransaction();

$stmt = $pdo->prepare('INSERT INTO commandes (user_id, menu_id, nb_personnes, prix_menu, prix_livraison, reduction, prix_total, rue, numero, complement, code_postal, ville, date_livraison, heure_livraison, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $user_id, $menu_id, $quantite, $totalMenu, $livraison, $reduction, $total,
    $rue, $numero, $complement, $code_postal, $ville, $date, $heure, 'en_attente'
]);
$commande_id = (int)$pdo->lastInsertId();

enregistrerHistorique($pdo, $commande_id, 'en_attente');
$pdo->prepare('UPDATE menus SET stock = stock - 1 WHERE id = ? AND stock > 0')->execute([$menu_id]);

$pdo->commit();

mongoInsertCommandeStat([
    'commande_id' => $commande_id,
    'menu_id' => $menu_id,
    'menu_titre' => $menu['titre'],
    'prix_total' => $total,
    'nb_personnes' => $quantite
]);

$stmt = $pdo->prepare('SELECT email, nom, prenom FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && !empty($user['email'])) {
    $subject = 'Confirmation de votre commande - Vite & Gourmand';
    $message = 'Bonjour ' . $user['prenom'] . ' ' . $user['nom'] . ",\n\nVotre commande #" . $commande_id . " est confirmee.\n\nMenu : " . $menu['titre'] . "\nPersonnes : " . $quantite . "\nDate : " . $date . ' a ' . $heure . "\nAdresse : " . $numero . ' ' . $rue . ', ' . $code_postal . ' ' . $ville . "\nTotal : " . $total . " EUR\n\nMerci de votre confiance !";
    @mail($user['email'], $subject, $message, 'From: contact@vite-gourmand.fr');
}

header('Location: confirmation.php?id=' . $commande_id);
exit();
