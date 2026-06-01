<?php
require_once __DIR__ . '/../includes/view.php';
require_once __DIR__ . '/../config/database.php';

require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    flash_set('error', 'Invalid student record.');
    header('Location: ' . url('admin/records.php'));
    exit();
}

$errors = [];
$student = [
  'student_type' => '', 'year_level' => '', 'section' => '', 'semester' => '',
  'last_name' => '', 'given_name' => '', 'middle_name' => '', 'gender' => '',
  'civil_status' => '', 'dob' => '', 'pob' => '', 'contact_number' => '',
  'fathers_name' => '', 'mothers_name' => '', 'parents_contact' => '',
  'address' => '', 'religion' => '',
  'cashier_status' => 'Not Paid', 'clearance_status' => 'Not Cleared',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student['student_type'] = trim($_POST['student_type'] ?? '');
    $student['year_level'] = trim($_POST['year_level'] ?? '');
    $student['section'] = trim($_POST['section'] ?? '');
    $student['semester'] = trim($_POST['semester'] ?? '');
    $student['last_name'] = trim($_POST['last_name'] ?? '');
    $student['given_name'] = trim($_POST['given_name'] ?? '');
    $student['middle_name'] = trim($_POST['middle_name'] ?? '');
    $student['gender'] = trim($_POST['gender'] ?? '');
    $student['civil_status'] = trim($_POST['civil_status'] ?? '');
    $student['dob'] = trim($_POST['dob'] ?? '');
    $student['pob'] = trim($_POST['pob'] ?? '');
    $student['contact_number'] = trim($_POST['contact_number'] ?? '');
    $student['fathers_name'] = trim($_POST['fathers_name'] ?? '');
    $student['mothers_name'] = trim($_POST['mothers_name'] ?? '');
    $student['parents_contact'] = trim($_POST['parents_contact'] ?? '');
    $student['address'] = trim($_POST['address'] ?? '');
    $student['religion'] = trim($_POST['religion'] ?? '');
    $student['cashier_status'] = trim($_POST['cashier_status'] ?? 'Not Paid');
    $student['clearance_status'] = trim($_POST['clearance_status'] ?? 'Not Cleared');

    if ($student['student_type'] === '') $errors[] = 'Student type is required.';
    if ($student['last_name'] === '') $errors[] = 'Last name is required.';
    if ($student['given_name'] === '') $errors[] = 'Given name is required.';
    if (!in_array($student['cashier_status'], ['Paid', 'Not Paid', 'Incomplete'], true)) $errors[] = 'Invalid cashier status.';
    if (!in_array($student['clearance_status'], ['Cleared', 'Not Cleared'], true)) $errors[] = 'Invalid clearance status.';

    if (empty($errors)) {
        $stmt = $dbhandle->prepare('UPDATE enrollment SET
      student_type = ?, year_level = ?, section = ?, semester = ?,
      last_name = ?, given_name = ?, middle_name = ?, gender = ?, civil_status = ?,
      dob = ?, pob = ?, contact_number = ?, fathers_name = ?, mothers_name = ?,
      parents_contact = ?, address = ?, religion = ?, cashier_status = ?, clearance_status = ?
      WHERE id = ?');
        $stmt->bind_param(
            'sssssssssssssssssssi',
            $student['student_type'], $student['year_level'], $student['section'], $student['semester'],
            $student['last_name'], $student['given_name'], $student['middle_name'], $student['gender'], $student['civil_status'],
            $student['dob'], $student['pob'], $student['contact_number'], $student['fathers_name'], $student['mothers_name'],
            $student['parents_contact'], $student['address'], $student['religion'], $student['cashier_status'], $student['clearance_status'],
            $id
        );
        if ($stmt->execute()) {
            $stmt->close();
            flash_set('success', 'Record updated successfully.');
            header('Location: ' . url('admin/records.php'));
            exit();
        }
        $errors[] = 'Failed to update record: ' . $stmt->error;
        $stmt->close();
    }
} else {
    $stmt = $dbhandle->prepare('SELECT * FROM enrollment WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        flash_set('error', 'Record not found.');
        header('Location: ' . url('admin/records.php'));
        exit();
    }
    $student = $result->fetch_assoc();
    $stmt->close();
}

render_app_shell_start('Edit enrollment', 'records', admin_nav_items(), 'Admin');
?>
<div class="page-intro">
  <p>Updating record #<?= e((string) $id) ?> — <?= e($student['last_name'] . ', ' . $student['given_name']) ?></p>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert--error" role="alert">
    <ul class="error-list">
      <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card">
  <form method="post" action="<?= url('admin/editrecords.php?id=' . urlencode((string) $id)) ?>">
    <p class="section-title">Academic information</p>
    <div class="form-grid form-grid--3">
      <div class="form-field"><label for="student_type">Student type</label><input type="text" id="student_type" name="student_type" value="<?= e($student['student_type']) ?>" required></div>
      <div class="form-field"><label for="year_level">Year level</label><input type="text" id="year_level" name="year_level" value="<?= e($student['year_level']) ?>" required></div>
      <div class="form-field"><label for="section">Section</label><input type="text" id="section" name="section" value="<?= e($student['section']) ?>" required></div>
      <div class="form-field"><label for="semester">Semester</label><input type="text" id="semester" name="semester" value="<?= e($student['semester']) ?>" required></div>
    </div>

    <p class="section-title">Personal information</p>
    <div class="form-grid form-grid--3">
      <div class="form-field"><label for="last_name">Last name</label><input type="text" id="last_name" name="last_name" value="<?= e($student['last_name']) ?>" required></div>
      <div class="form-field"><label for="given_name">Given name</label><input type="text" id="given_name" name="given_name" value="<?= e($student['given_name']) ?>" required></div>
      <div class="form-field"><label for="middle_name">Middle name</label><input type="text" id="middle_name" name="middle_name" value="<?= e($student['middle_name']) ?>"></div>
      <div class="form-field"><label for="gender">Gender</label><input type="text" id="gender" name="gender" value="<?= e($student['gender']) ?>" required></div>
      <div class="form-field"><label for="civil_status">Civil status</label><input type="text" id="civil_status" name="civil_status" value="<?= e($student['civil_status']) ?>" required></div>
      <div class="form-field"><label for="dob">Date of birth</label><input type="date" id="dob" name="dob" value="<?= e($student['dob']) ?>" required></div>
      <div class="form-field"><label for="pob">Place of birth</label><input type="text" id="pob" name="pob" value="<?= e($student['pob']) ?>" required></div>
      <div class="form-field"><label for="contact_number">Contact number</label><input type="text" id="contact_number" name="contact_number" value="<?= e($student['contact_number']) ?>" required></div>
      <div class="form-field"><label for="religion">Religion</label><input type="text" id="religion" name="religion" value="<?= e($student['religion']) ?>" required></div>
      <div class="form-field form-field--full"><label for="address">Address</label><textarea id="address" name="address" required><?= e($student['address']) ?></textarea></div>
    </div>

    <p class="section-title">Family &amp; status</p>
    <div class="form-grid form-grid--3">
      <div class="form-field"><label for="fathers_name">Father's name</label><input type="text" id="fathers_name" name="fathers_name" value="<?= e($student['fathers_name']) ?>" required></div>
      <div class="form-field"><label for="mothers_name">Mother's name</label><input type="text" id="mothers_name" name="mothers_name" value="<?= e($student['mothers_name']) ?>" required></div>
      <div class="form-field"><label for="parents_contact">Parents contact</label><input type="text" id="parents_contact" name="parents_contact" value="<?= e($student['parents_contact']) ?>" required></div>
      <div class="form-field">
        <label for="cashier_status">Cashier status</label>
        <select id="cashier_status" name="cashier_status" required>
          <option value="Paid" <?= $student['cashier_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
          <option value="Not Paid" <?= $student['cashier_status'] === 'Not Paid' ? 'selected' : '' ?>>Not Paid</option>
          <option value="Incomplete" <?= $student['cashier_status'] === 'Incomplete' ? 'selected' : '' ?>>Incomplete</option>
        </select>
      </div>
      <div class="form-field">
        <label for="clearance_status">Clearance status</label>
        <select id="clearance_status" name="clearance_status" required>
          <option value="Cleared" <?= $student['clearance_status'] === 'Cleared' ? 'selected' : '' ?>>Cleared</option>
          <option value="Not Cleared" <?= $student['clearance_status'] === 'Not Cleared' ? 'selected' : '' ?>>Not Cleared</option>
        </select>
      </div>
    </div>

    <div class="form-actions" style="margin-top:1.5rem;">
      <button type="submit" class="btn btn--primary">Save changes</button>
      <a href="<?= url('admin/records.php') ?>" class="btn btn--secondary">Cancel</a>
    </div>
  </form>
</div>
<?php render_app_shell_end(); ?>
