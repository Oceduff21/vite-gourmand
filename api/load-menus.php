<?php
/**
 * BACK — API AJAX menus
 * Controleur MVC : MenuController + modele Menu (POO).
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../back/autoload.php';

use ViteGourmand\Controllers\MenuController;

$controller = new MenuController($pdo);
$controller->ajaxList([
    'search' => $_GET['search'] ?? '',
    'theme' => $_GET['theme'] ?? '',
    'regime' => $_GET['regime'] ?? '',
    'prix_min' => $_GET['prix_min'] ?? '',
    'prix_max' => $_GET['prix_max'] ?? '',
    'personnes' => $_GET['personnes'] ?? '',
    'sort' => $_GET['sort'] ?? '',
]);
