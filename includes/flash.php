<?php

function setFlash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = $message;
    $_SESSION['flash_type'] = in_array($type, ['success', 'danger', 'warning', 'info'], true) ? $type : 'success';
}

function showFlash(): void
{
    if (!isset($_SESSION['flash'])) {
        return;
    }

    $type = $_SESSION['flash_type'] ?? 'success';
    echo '<div class="alert alert-' . htmlspecialchars($type) . '" role="alert">'
        . htmlspecialchars($_SESSION['flash'])
        . '</div>';

    unset($_SESSION['flash'], $_SESSION['flash_type']);
}
