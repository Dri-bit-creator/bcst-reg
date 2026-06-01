<?php
require_once __DIR__ . '/../includes/view.php';
require_once __DIR__ . '/../config/database.php';

require_admin();

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$enrollments = [];

$enrollQuery = 'SELECT * FROM enrollment';
if ($search !== '') {
    $like = '%' . $dbhandle->real_escape_string($search) . '%';
    $enrollQuery .= " WHERE last_name LIKE '$like'
                   OR given_name LIKE '$like'
                   OR student_type LIKE '$like'
                   OR section LIKE '$like'
                   OR semester LIKE '$like'";
}
$enrollQuery .= ' ORDER BY last_name, given_name';

$enrollResult = $dbhandle->query($enrollQuery);
if ($enrollResult) {
    while ($row = $enrollResult->fetch_assoc()) {
        $enrollments[] = $row;
    }
}

render_app_shell_start('Student records', 'records', admin_nav_items(), 'Admin');
?>
<div class="table-card">
  <div class="table-toolbar">
    <div>
      <h2>Enrollment records</h2>
      <p style="font-size:0.875rem;color:var(--color-slate-500);margin-top:0.25rem;"><?= count($enrollments) ?> student(s)</p>
    </div>
    <form method="post" class="search-form" action="<?= url('admin/records.php') ?>">
      <input type="search" name="search" placeholder="Search name, section, semester…" value="<?= e($search) ?>" aria-label="Search records">
      <button type="submit" class="btn btn--primary btn--sm">Search</button>
    </form>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Year</th>
          <th>Section</th>
          <th>Semester</th>
          <th>Contact</th>
          <th>Cashier</th>
          <th>Clearance</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($enrollments) === 0): ?>
          <tr>
            <td colspan="9" style="text-align:center;padding:2rem;color:var(--color-slate-500);">No records found.</td>
          </tr>
        <?php endif; ?>
        <?php foreach ($enrollments as $student): ?>
          <tr>
            <td>
              <strong><?= e($student['last_name'] . ', ' . $student['given_name']) ?></strong>
              <?php if (!empty($student['middle_name'])): ?>
                <br><span style="color:var(--color-slate-500);font-size:0.8125rem;"><?= e($student['middle_name']) ?></span>
              <?php endif; ?>
            </td>
            <td><?= e($student['student_type']) ?></td>
            <td><?= e($student['year_level']) ?></td>
            <td><?= e($student['section']) ?></td>
            <td><?= e($student['semester']) ?></td>
            <td><?= e($student['contact_number']) ?></td>
            <td><?= status_badge($student['cashier_status'] ?? 'Not Paid', 'cashier') ?></td>
            <td><?= status_badge($student['clearance_status'] ?? 'Not Cleared', 'clearance') ?></td>
            <td>
              <a href="<?= url('admin/editrecords.php?id=' . urlencode($student['id'])) ?>" class="btn btn--secondary btn--sm">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php render_app_shell_end(); ?>
