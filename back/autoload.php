<?php
/**
 * Autoload POO back-end (namespace ViteGourmand\).
 * Usage : require_once __DIR__ . '/../back/autoload.php';
 */

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'ViteGourmand\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
