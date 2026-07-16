<?php

if (!function_exists('adminShowFatalError')) {
    function adminShowFatalError(): void
    {
        $error = error_get_last();
        if (!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            return;
        }

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            http_response_code(500);
        }

        echo '<pre style="margin:20px;padding:16px;background:#fee;border:1px solid #f99;font-family:monospace">';
        echo 'Erreur fatale admin' . "\n\n";
        echo htmlspecialchars($error['message']) . "\n";
        echo htmlspecialchars(($error['file'] ?? '') . ':' . ($error['line'] ?? 0));
        echo '</pre>';
    }

    register_shutdown_function('adminShowFatalError');
}
