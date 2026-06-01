<?php
require_once __DIR__ . '/../includes/view.php';
require_once __DIR__ . '/../config/database.php';

require_admin();

$studentCount = 0;
$result = $dbhandle->query('SELECT COUNT(*) AS total FROM enrollment');
if ($result) {
    $row = $result->fetch_assoc();
    $studentCount = (int) ($row['total'] ?? 0);
}

render_app_shell_start('Dashboard', 'dashboard', admin_nav_items(), 'Admin');
?>
<div class="page-intro">
  <p>Overview of enrollment activity and quick access to student records.</p>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <p class="stat-card__label">Total enrollments</p>
    <p class="stat-card__value"><?= e((string) $studentCount) ?></p>
  </div>
  <div class="stat-card">
    <p class="stat-card__label">Portal</p>
    <p class="stat-card__value" style="font-size:1.125rem;color:var(--color-primary-700);">Admin</p>
  </div>
</div>

<div class="card-grid">
  <article class="card">
    <h2 class="card__title">Student records</h2>
    <p class="card__text">Search, review, and update enrollment data including cashier and clearance status.</p>
    <p style="margin-top:1rem;">
      <a href="<?= url('admin/records.php') ?>" class="btn btn--primary">Open records</a>
    </p>
  </article>
  <article class="card">
    <h2 class="card__title">About BCST</h2>
    <p class="card__text">Bohol College of Science & Technology Inc. provides quality programs in computing, business, and engineering.</p>
  </article>
  <article class="card">
    <h2 class="card__title">Contact</h2>
    <p class="card__text">Email: info@bcst.edu.ph<br>Phone: (123) 456-7890</p>
  </article>
</div>
<?php render_app_shell_end(); ?>
