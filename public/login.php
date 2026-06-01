<?php
require_once __DIR__ . '/../includes/view.php';
require_once __DIR__ . '/../config/database.php';

ensure_session();

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'user';
    redirect_intended_or($role === 'admin' ? 'admin/dashboard.php' : 'public/userdash.php');
}

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $dbhandle->prepare('SELECT id, password, role FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = $role;

            redirect_intended_or($role === 'admin' ? 'admin/dashboard.php' : 'public/userdash.php');
        }
        flash_set('error', 'Incorrect password. Please try again.');
    } else {
        flash_set('error', 'Email not found. Please sign up first.');
    }
    $stmt->close();
}

render_auth_shell_start('Sign in', 'Access your student or admin portal');
?>
<form method="post" action="<?= url('public/login.php') ?>" class="form-stack">
  <div class="form-field">
    <label for="email">Email address</label>
    <input type="email" id="email" name="email" autocomplete="email" required>
  </div>
  <div class="form-field">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" autocomplete="current-password" required>
  </div>
  <button type="submit" name="submit" class="btn btn--primary btn--block">Sign in</button>
  <p class="form-footer">
    Don't have an account? <a href="<?= url('public/signup.php') ?>">Create one</a>
  </p>
</form>
<?php render_auth_shell_end(); ?>
