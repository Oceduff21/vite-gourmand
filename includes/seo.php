<?php

function getSiteBaseUrl(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    if ($scriptDir === '/' || $scriptDir === '.') {
        $scriptDir = '';
    }
    return ($https ? 'https' : 'http') . '://' . $host . $scriptDir;
}

function getDefaultSeo(): array
{
    return [
        'title' => 'Vite & Gourmand — Traiteur premium a Bordeaux',
        'description' => 'Traiteur evenementiel haut de gamme a Bordeaux : menus mariage, seminaires, fetes privees. Livraison, selection de plats par invite, boissons sur mesure.',
        'keywords' => 'traiteur, bordeaux, mariage, evenement, gastronomie, menu, livraison',
        'og_image' => 'assets/images/presentation.jpg',
        'type' => 'website',
    ];
}

function buildCanonicalUrl(?string $path = null): string
{
    $base = rtrim(getSiteBaseUrl(), '/');
    if ($path === null) {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $path = strtok($path, '?') ?: '/';
    }
    if (!str_starts_with($path, '/')) {
        $path = '/' . ltrim($path, '/');
    }
    return $base . $path;
}
