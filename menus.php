<?php
/**
 * BACK — page Menus (controleur MVC).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/back/autoload.php';

use ViteGourmand\Controllers\MenuController;

$controller = new MenuController($pdo);
$controller->indexPage();
