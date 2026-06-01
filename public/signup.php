<?php
require_once __DIR__ . '/../includes/view.php';
require_once __DIR__ . '/../config/database.php';

ensure_session();

$name = $email = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        flash_set('error', 'Passwords do not match.');
    } else {
        $stmt = $dbhandle->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            flash_set('warning', 'This email is already registered. Please sign in.');
            $stmt->close();
        } else {
            $stmt->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $dbhandle->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $hashed_password);

            if ($stmt->execute()) {
                flash_set('success', 'Account created successfully. You can sign in now.');
                header('Location: ' . url('public/login.php'));
                exit();
            }
            flash_set('error', 'Registration failed. Please try again later.');
        }
        $stmt->close();
    }
}

render_auth_shell_start('Create account', 'Register as a BCST student');
?>
<form method="post" action="<?= url('public/signup.php') ?>" class="form-stack">
  <div class="form-field">
    <label for="name">Full name</label>
    <input type="text" id="name" name="name" value="<?= e($name) ?>" autocomplete="name" required>
  </div>
  <div class="form-field">
    <label for="email">Email address</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
  </div>
  <div class="form-field">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" autocomplete="new-password" required>
  </div>
  <div class="form-field">
    <label for="confirm_password">Confirm password</label>
    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
  </div>
  <button type="submit" name="submit" class="btn btn--primary btn--block">Create account</button>
  <p class="form-footer">
    Already registered? <a href="<?= url('public/login.php') ?>">Sign in</a>
  </p>
</form>
<?php render_auth_shell_end(); ?>
