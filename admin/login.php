<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

// If setup has not been run yet, send the owner there
try {
    $adminCount = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
} catch (PDOException $e) {
    $adminCount = 0;
}
if ($adminCount === 0) {
    redirect('setup.php');
}

if (!empty($_SESSION['admin_id']) && current_admin()) {
    redirect('admin/index.php');
}

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $error = 'Your form expired. Please try again.';
    } elseif ($username === '' || $password === '') {
        $error = 'Please enter both your username and password.';
    } else {
        [$ok, $msg] = attempt_login($username, $password);
        if ($ok) {
            redirect('admin/index.php');
        }
        $error = $msg;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Administrator Login</title>
<link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>
<body class="auth-body">
  <div class="auth-card">
    <h1>Administrator Login</h1>
    <p class="muted">Sign in to manage your portfolio.</p>
    <?= flash_render('notice') ?>
    <?php if ($error): ?><div class="notice notice-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('admin/login.php')) ?>" autocomplete="off">
      <?= csrf_field() ?>
      <div class="form-row">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" maxlength="50" required autofocus>
      </div>
      <div class="form-row">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button class="btn btn-block" type="submit">Sign in</button>
    </form>
    <p class="center"><a href="<?= e(url('index.php')) ?>">← Back to website</a></p>
  </div>
</body>
</html>
