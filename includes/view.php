<?php

require_once __DIR__ . '/paths.php';
require_once __DIR__ . '/auth.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash_set(string $type, string $message): void
{
    ensure_session();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_render(): void
{
    ensure_session();
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = e($flash['type'] ?? 'info');
    $message = e($flash['message'] ?? '');
    echo "<div class=\"alert alert--{$type}\" role=\"alert\">{$message}</div>";
}

function user_nav_items(): array
{
    return [
        ['key' => 'home', 'label' => 'Home', 'href' => url('public/userdash.php')],
        ['key' => 'enrollment', 'label' => 'Enrollment', 'href' => url('public/enrollment.php')],
    ];
}

function admin_nav_items(): array
{
    return [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => url('admin/dashboard.php')],
        ['key' => 'records', 'label' => 'Student Records', 'href' => url('admin/records.php')],
    ];
}

function render_head(string $title, array $extraStyles = []): void
{
    $styles = array_merge([asset('css/main.css')], $extraStyles);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Bohol College of Science & Technology — student enrollment portal">
  <meta name="application-base" content="<?= e(BASE_URL) ?>">
  <title><?= e($title) ?> · BCST</title>
  <link rel="icon" href="<?= asset('images/LOGO.png') ?>" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<?php foreach ($styles as $href): ?>
  <link rel="stylesheet" href="<?= e($href) ?>">
<?php endforeach; ?>
</head>
<?php
}

function render_auth_shell_start(string $title, string $subtitle = ''): void
{
    render_head($title);
    ?>
<body class="auth-page">
  <div class="auth-page__bg" aria-hidden="true"></div>
  <div class="auth-page__wrapper">
    <header class="auth-brand">
      <img src="<?= asset('images/LOGO.png') ?>" alt="" class="auth-brand__logo" width="72" height="72">
      <div>
        <p class="auth-brand__eyebrow">Bohol College of Science & Technology</p>
        <h1 class="auth-brand__title"><?= e($title) ?></h1>
        <?php if ($subtitle !== ''): ?>
          <p class="auth-brand__subtitle"><?= e($subtitle) ?></p>
        <?php endif; ?>
      </div>
    </header>
    <main class="auth-card">
      <?php flash_render(); ?>
<?php
}

function render_auth_shell_end(): void
{
    ?>
    </main>
    <footer class="auth-footer">
      <small>&copy; <?= date('Y') ?> BCST Inc. All rights reserved.</small>
    </footer>
  </div>
</body>
</html>
<?php
}

function render_app_shell_start(string $title, string $activeNav, array $navItems, string $roleLabel = 'Portal'): void
{
    ensure_session(); // from auth.php
    $userEmail = $_SESSION['user_email'] ?? 'User';
    render_head($title);
    ?>
<body class="app-shell">
  <a class="skip-link" href="#main-content">Skip to content</a>
  <aside class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="sidebar__brand">
      <img src="<?= asset('images/LOGO.png') ?>" alt="" class="sidebar__logo" width="40" height="40">
      <div>
        <span class="sidebar__name">BCST</span>
        <span class="sidebar__role"><?= e($roleLabel) ?></span>
      </div>
    </div>
    <nav class="sidebar__nav">
      <ul>
<?php foreach ($navItems as $item): ?>
        <li>
          <a href="<?= e($item['href']) ?>" class="nav-link<?= ($item['key'] ?? '') === $activeNav ? ' nav-link--active' : '' ?>">
            <?= e($item['label']) ?>
          </a>
        </li>
<?php endforeach; ?>
      </ul>
    </nav>
    <div class="sidebar__footer">
      <p class="sidebar__user" title="<?= e($userEmail) ?>"><?= e($userEmail) ?></p>
      <a href="<?= url('public/logout.php') ?>" class="nav-link nav-link--logout">Sign out</a>
    </div>
  </aside>
  <div class="app-shell__main">
    <header class="topbar">
      <button type="button" class="topbar__menu" id="sidebar-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="sidebar">
        <span></span><span></span><span></span>
      </button>
      <div class="topbar__titles">
        <h1 class="topbar__title"><?= e($title) ?></h1>
      </div>
    </header>
    <main class="page-content" id="main-content">
      <?php flash_render(); ?>
<?php
}

function render_app_shell_end(): void
{
    ?>
    </main>
    <footer class="page-footer">
      <small>&copy; <?= date('Y') ?> BCST Inc.</small>
    </footer>
  </div>
  <script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
<?php
}

function status_badge(string $status, string $kind = 'cashier'): string
{
    $normalized = strtolower(trim($status));
    $class = 'badge badge--neutral';

    if ($kind === 'clearance') {
        $class = $normalized === 'cleared' ? 'badge badge--success' : 'badge badge--warning';
    } else {
        if ($normalized === 'paid') {
            $class = 'badge badge--success';
        } elseif ($normalized === 'incomplete') {
            $class = 'badge badge--warning';
        } else {
            $class = 'badge badge--danger';
        }
    }

    return '<span class="' . $class . '">' . e($status) . '</span>';
}
