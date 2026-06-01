<?php

if (!defined('BASE_URL')) {
    define('BASE_URL', detect_base_path());
}

/**
 * Detect app base path from filesystem (e.g. /BCSTR) so links work on any host/port.
 */
function detect_base_path(): string
{
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $appRoot = realpath(dirname(__DIR__));

    if ($documentRoot && $appRoot && str_starts_with($appRoot, $documentRoot)) {
        $relative = str_replace('\\', '/', substr($appRoot, strlen($documentRoot)));
        return $relative === '' ? '' : $relative;
    }

  // Fallback when document root cannot be resolved
    return '/BCSTR';
}

function url(string $path): string
{
    $base = BASE_URL;
    $path = ltrim($path, '/');

    if ($base === '') {
        return '/' . $path;
    }

    return rtrim($base, '/') . '/' . $path;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Full URL for redirects/sharing (same host as current request).
 */
function absolute_url(string $path): string
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . url($path);
}

/**
 * True if $uri is an in-app path under BASE_URL (blocks open redirects).
 */
function is_safe_app_uri(string $uri): bool
{
    $uri = strtok($uri, '?') ?: $uri;
    $base = BASE_URL;

    if ($base === '') {
        return $uri === '' || ($uri[0] === '/' && !str_starts_with($uri, '//'));
    }

    return $uri === $base || str_starts_with($uri, $base . '/');
}
