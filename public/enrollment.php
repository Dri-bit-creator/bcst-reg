<?php
require_once __DIR__ . '/../includes/view.php';
require_once __DIR__ . '/../config/database.php';

require_login();
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: ' . url('admin/dashboard.php'));
    exit();
}

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enrollmentData = [
        'student_type'    => $_POST['student-type'] ?? '',
        'year_level'      => $_POST['year-level'] ?? '',
        'section'         => $_POST['section'] ?? '',
        'semester'        => $_POST['semester'] ?? '',
        'last_name'       => $_POST['last-name'] ?? '',
        'given_name'      => $_POST['given-name'] ?? '',
        'middle_name'     => $_POST['middle-name'] ?? '',
        'gender'          => $_POST['gender'] ?? '',
        'civil_status'    => $_POST['civil-status'] ?? '',
        'dob'             => $_POST['dob'] ?? '',
        'pob'             => $_POST['pob'] ?? '',
        'contact_number'  => $_POST['contact-number'] ?? '',
        'fathers_name'    => $_POST['fathers-name'] ?? '',
        'mothers_name'    => $_POST['mothers-name'] ?? '',
        'parents_contact' => $_POST['parents-contact'] ?? '',
        'address'         => $_POST['address'] ?? '',
        'religion'        => $_POST['religion'] ?? '',
    ];

    $required_fields = ['student_type', 'year_level', 'section', 'semester', 'last_name', 'given_name', 'gender', 'civil_status', 'dob', 'pob', 'contact_number', 'fathers_name', 'mothers_name', 'parents_contact', 'address', 'religion'];
    $valid = true;
    foreach ($required_fields as $field) {
        if (empty($enrollmentData[$field])) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        $stmt = $dbhandle->prepare('
            INSERT INTO enrollment (
                student_type, year_level, section, semester, last_name, given_name, middle_name,
                gender, civil_status, dob, pob, contact_number, fathers_name, mothers_name,
                parents_contact, address, religion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        if ($stmt) {
            $stmt->bind_param(
                'sssssssssssssssss',
                $enrollmentData['student_type'],
                $enrollmentData['year_level'],
                $enrollmentData['section'],
                $enrollmentData['semester'],
                $enrollmentData['last_name'],
                $enrollmentData['given_name'],
                $enrollmentData['middle_name'],
                $enrollmentData['gender'],
                $enrollmentData['civil_status'],
                $enrollmentData['dob'],
                $enrollmentData['pob'],
                $enrollmentData['contact_number'],
                $enrollmentData['fathers_name'],
                $enrollmentData['mothers_name'],
                $enrollmentData['parents_contact'],
                $enrollmentData['address'],
                $enrollmentData['religion']
            );

            if ($stmt->execute()) {
                $message = 'Enrollment submitted successfully.';
                $messageType = 'success';
                $_POST = [];
            } else {
                $message = 'Database error: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Database prepare error: ' . $dbhandle->error;
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill in all required fields.';
        $messageType = 'warning';
    }
}

render_app_shell_start('Enrollment', 'enrollment', user_nav_items(), 'Student');
?>
<div class="page-intro">
  <p>Submit your enrollment information for the current academic term. All required fields must be completed.</p>
</div>

<?php if ($message !== ''): ?>
  <div class="alert alert--<?= e($messageType) ?>" role="alert"><?= e($message) ?></div>
<?php endif; ?>

<div class="card">
  <form method="post" action="<?= url('public/enrollment.php') ?>" class="form-grid form-grid--3">
    <p class="form-field form-field--full section-title" style="margin:0;">Academic details</p>

    <div class="form-field">
      <label for="student-type">Student type</label>
      <select id="student-type" name="student-type" required>
        <option value="" disabled <?= empty($_POST['student-type']) ? 'selected' : '' ?>>Select type</option>
        <option value="new" <?= (($_POST['student-type'] ?? '') === 'new') ? 'selected' : '' ?>>New student</option>
        <option value="old" <?= (($_POST['student-type'] ?? '') === 'old') ? 'selected' : '' ?>>Old student</option>
      </select>
    </div>
    <div class="form-field">
      <label for="year-level">Year level</label>
      <input type="text" id="year-level" name="year-level" required value="<?= e($_POST['year-level'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="section">Section</label>
      <input type="text" id="section" name="section" required value="<?= e($_POST['section'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="semester">Semester</label>
      <input type="text" id="semester" name="semester" required value="<?= e($_POST['semester'] ?? '') ?>">
    </div>

    <p class="form-field form-field--full section-title" style="margin:0;">Personal details</p>

    <div class="form-field">
      <label for="last-name">Last name</label>
      <input type="text" id="last-name" name="last-name" required value="<?= e($_POST['last-name'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="given-name">Given name</label>
      <input type="text" id="given-name" name="given-name" required value="<?= e($_POST['given-name'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="middle-name">Middle name</label>
      <input type="text" id="middle-name" name="middle-name" value="<?= e($_POST['middle-name'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="gender">Gender</label>
      <select id="gender" name="gender" required>
        <option value="" disabled <?= empty($_POST['gender']) ? 'selected' : '' ?>>Select gender</option>
        <option value="male" <?= (($_POST['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
        <option value="female" <?= (($_POST['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
        <option value="other" <?= (($_POST['gender'] ?? '') === 'other') ? 'selected' : '' ?>>Other</option>
      </select>
    </div>
    <div class="form-field">
      <label for="civil-status">Civil status</label>
      <select id="civil-status" name="civil-status" required>
        <option value="" disabled <?= empty($_POST['civil-status']) ? 'selected' : '' ?>>Select status</option>
        <option value="single" <?= (($_POST['civil-status'] ?? '') === 'single') ? 'selected' : '' ?>>Single</option>
        <option value="married" <?= (($_POST['civil-status'] ?? '') === 'married') ? 'selected' : '' ?>>Married</option>
        <option value="widowed" <?= (($_POST['civil-status'] ?? '') === 'widowed') ? 'selected' : '' ?>>Widowed</option>
      </select>
    </div>
    <div class="form-field">
      <label for="dob">Date of birth</label>
      <input type="date" id="dob" name="dob" required value="<?= e($_POST['dob'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="pob">Place of birth</label>
      <input type="text" id="pob" name="pob" required value="<?= e($_POST['pob'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="contact-number">Contact number</label>
      <input type="tel" id="contact-number" name="contact-number" required pattern="[0-9+()-\s]*" value="<?= e($_POST['contact-number'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="religion">Religion</label>
      <input type="text" id="religion" name="religion" required value="<?= e($_POST['religion'] ?? '') ?>">
    </div>

    <p class="form-field form-field--full section-title" style="margin:0;">Family &amp; address</p>

    <div class="form-field">
      <label for="fathers-name">Father's name</label>
      <input type="text" id="fathers-name" name="fathers-name" required value="<?= e($_POST['fathers-name'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="mothers-name">Mother's name</label>
      <input type="text" id="mothers-name" name="mothers-name" required value="<?= e($_POST['mothers-name'] ?? '') ?>">
    </div>
    <div class="form-field">
      <label for="parents-contact">Parents' contact</label>
      <input type="tel" id="parents-contact" name="parents-contact" required pattern="[0-9+()-\s]*" value="<?= e($_POST['parents-contact'] ?? '') ?>">
    </div>
    <div class="form-field form-field--full">
      <label for="address">Address</label>
      <textarea id="address" name="address" required><?= e($_POST['address'] ?? '') ?></textarea>
    </div>

    <div class="form-field form-field--full form-actions">
      <button type="submit" class="btn btn--primary">Submit enrollment</button>
    </div>
  </form>
</div>
<?php render_app_shell_end(); ?>
