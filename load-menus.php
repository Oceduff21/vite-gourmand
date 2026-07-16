<?php
require 'includes/db.php';
require 'includes/menu-helpers.php';

$search = $_GET['search'] ?? '';
$theme = $_GET['theme'] ?? '';
$regime = $_GET['regime'] ?? '';
$prix_min = $_GET['prix_min'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';
$personnes = $_GET['personnes'] ?? '';
$sort = $_GET['sort'] ?? '';

$sql = 'SELECT * FROM menus WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND (titre LIKE ? OR description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($theme !== '') {
    $sql .= ' AND LOWER(COALESCE(theme, \'\')) = ?';
    $params[] = strtolower($theme);
}

if ($regime !== '') {
    $sql .= ' AND LOWER(COALESCE(regime, \'\')) = ?';
    $params[] = strtolower($regime);
}

if ($prix_min !== '' && is_numeric($prix_min)) {
    $sql .= ' AND prix >= ?';
    $params[] = (float)$prix_min;
}

if ($prix_max !== '' && is_numeric($prix_max)) {
    $sql .= ' AND prix <= ?';
    $params[] = (float)$prix_max;
}

if ($personnes !== '' && is_numeric($personnes)) {
    $sql .= ' AND min_personnes <= ?';
    $params[] = (int)$personnes;
}

switch ($sort) {
    case 'prix_asc':
        $sql .= ' ORDER BY prix ASC, titre ASC';
        break;
    case 'prix_desc':
        $sql .= ' ORDER BY prix DESC, titre ASC';
        break;
    case 'populaire':
        $sql = '
            SELECT m.*, COUNT(c.id) AS nb_commandes
            FROM menus m
            LEFT JOIN commandes c ON c.menu_id = m.id
            WHERE 1=1
        ';
        $paramsPop = [];
        if ($search !== '') {
            $sql .= ' AND (m.titre LIKE ? OR m.description LIKE ?)';
            $paramsPop[] = '%' . $search . '%';
            $paramsPop[] = '%' . $search . '%';
        }
        if ($theme !== '') {
            $sql .= ' AND LOWER(COALESCE(m.theme, \'\')) = ?';
            $paramsPop[] = strtolower($theme);
        }
        if ($regime !== '') {
            $sql .= ' AND LOWER(COALESCE(m.regime, \'\')) = ?';
            $paramsPop[] = strtolower($regime);
        }
        if ($prix_min !== '' && is_numeric($prix_min)) {
            $sql .= ' AND m.prix >= ?';
            $paramsPop[] = (float)$prix_min;
        }
        if ($prix_max !== '' && is_numeric($prix_max)) {
            $sql .= ' AND m.prix <= ?';
            $paramsPop[] = (float)$prix_max;
        }
        if ($personnes !== '' && is_numeric($personnes)) {
            $sql .= ' AND m.min_personnes <= ?';
            $paramsPop[] = (int)$personnes;
        }
        $sql .= ' GROUP BY m.id ORDER BY nb_commandes DESC, m.titre ASC';
        $params = $paramsPop;
        break;
    default:
        $sql .= ' ORDER BY stock DESC, id DESC';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($menus)) {
    echo '<div class="col-12"><p class="text-center text-muted py-5 mb-0">Aucun menu ne correspond a vos criteres.</p></div>';
    exit;
}

foreach ($menus as $menu) {
    echo renderMenuCard($menu);
}
