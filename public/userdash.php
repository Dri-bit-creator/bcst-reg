<?php
require_once __DIR__ . '/../includes/view.php';

require_login();
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: ' . url('admin/dashboard.php'));
    exit();
}

render_app_shell_start('Home', 'home', user_nav_items(), 'Student');
?>
<div class="page-intro">
  <p>Welcome to the BCST student portal. Submit enrollment or browse school information below.</p>
</div>

<div class="card-grid">
  <article class="card">
    <h2 class="card__title">Enrollment</h2>
    <p class="card__text">Complete the enrollment form with your personal and academic details for the current term.</p>
    <p style="margin-top:1rem;">
      <a href="<?= url('public/enrollment.php') ?>" class="btn btn--primary">Start enrollment</a>
    </p>
  </article>
  <article class="card">
    <h2 class="card__title">About our school</h2>
    <p class="card__text">We foster a nurturing environment where faculty support students in reaching academic and personal goals.</p>
  </article>
  <article class="card">
    <h2 class="card__title">Programs offered</h2>
    <p class="card__text">
      Computer Science, Information Technology, Business Administration, and Engineering — designed for career readiness.
    </p>
  </article>
  <article class="card">
    <h2 class="card__title">Need help?</h2>
    <p class="card__text">Email info@bcst.edu.ph or call (123) 456-7890 for assistance with enrollment.</p>
  </article>
</div>
<?php render_app_shell_end(); ?>
