<?php

require_once __DIR__ . '/paths.php';

function ensure_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $cookiePath = BASE_URL === '' ? '/' : BASE_URL;

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookiePath,
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function store_intended_url(): void
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ($uri !== '' && is_safe_app_uri($uri)) {
        $_SESSION['intended_url'] = $uri;
    }
}

function take_intended_url(): ?string
{
    if (empty($_SESSION['intended_url'])) {
        return null;
    }

    $uri = $_SESSION['intended_url'];
    unset($_SESSION['intended_url']);

    return is_safe_app_uri($uri) ? $uri : null;
}

function redirect_intended_or(string $fallbackPath): void
{
    $intended = take_intended_url();
    if ($intended !== null) {
        header('Location: ' . $intended);
        exit();
    }

    header('Location: ' . url($fallbackPath));
    exit();
}

function require_login(): void
{
    ensure_session();
    if (!isset($_SESSION['user_id'])) {
        store_intended_url();
        header('Location: ' . url('public/login.php'));
        exit();
    }
}

function require_admin(): void
{
    require_login();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        store_intended_url();
        header('Location: ' . url('public/login.php'));
        exit();
    }
}

function logout_and_redirect(): void
{
    ensure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
    header('Location: ' . url('public/login.php'));
    exit();
}
