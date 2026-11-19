<?php
require __DIR__ . '/includes/init.php';
$pdo   = db();
$admin = current_admin();
$profileErrors = [];
$passErrors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash_set('error', 'Your security token was invalid or expired. Please try again.');
        redirect('admin/account.php');
    }
    $do = $_POST['do'] ?? '';

    if ($do === 'profile') {
        $name  = trim((string)($_POST['full_name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        if ($name === '' || mb_strlen($name) > 100) {
            $profileErrors[] = 'Name is required (100 characters max).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $profileErrors[] = 'Please enter a valid email address.';
        }
        if (!$profileErrors) {
            $pdo->prepare('UPDATE admins SET full_name = ?, email = ? WHERE id = ?')->execute([$name, $email, $admin['id']]);
            flash_set('success', 'Profile updated.');
            redirect('admin/account.php');
        }
        $admin['full_name'] = $name;
        $admin['email']     = $email;
    }

    if ($do === 'password') {
        $cur  = (string)($_POST['current_password'] ?? '');
        $new  = (string)($_POST['new_password'] ?? '');
        $conf = (string)($_POST['confirm_password'] ?? '');
        $st = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
        $st->execute([$admin['id']]);
        $hash = (string)$st->fetchColumn();

        if (!password_verify($cur, $hash)) {
            $passErrors[] = 'Your current password is incorrect.';
        }
        if ($err = password_error($new)) {
            $passErrors[] = $err;
        }
        if ($new !== $conf) {
            $passErrors[] = 'The new passwords do not match.';
        }
        if (!$passErrors && $cur === $new) {
            $passErrors[] = 'Your new password must be different from the current one.';
        }
        if (!$passErrors) {
            $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
            session_regenerate_id(true);
            flash_set('success', 'Password changed.');
            redirect('admin/account.php');
        }
    }
}

admin_header('My Account', 'account');
?>
<div class="two-col">
  <div class="panel">
    <h2>Profile</h2>
    <?php foreach ($profileErrors as $er): ?><div class="notice notice-error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" action="<?= e(url('admin/account.php')) ?>" novalidate>
      <?= csrf_field() ?><input type="hidden" name="do" value="profile">
      <div class="form-row"><label>Username</label><input type="text" value="<?= e($admin['username']) ?>" disabled></div>
      <div class="form-row"><label for="full_name">Full name</label><input type="text" id="full_name" name="full_name" value="<?= e($admin['full_name']) ?>" maxlength="100" required></div>
      <div class="form-row"><label for="email">Email</label><input type="email" id="email" name="email" value="<?= e($admin['email']) ?>" maxlength="150" required></div>
      <div class="form-actions"><button class="btn" type="submit">Save profile</button></div>
    </form>
  </div>

  <div class="panel">
    <h2>Change password</h2>
    <?php foreach ($passErrors as $er): ?><div class="notice notice-error"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" action="<?= e(url('admin/account.php')) ?>" autocomplete="off" novalidate>
      <?= csrf_field() ?><input type="hidden" name="do" value="password">
      <div class="form-row"><label for="current_password">Current password</label><input type="password" id="current_password" name="current_password" autocomplete="current-password" required></div>
      <div class="form-row"><label for="new_password">New password</label><input type="password" id="new_password" name="new_password" autocomplete="new-password" required><small>At least 10 characters, with a letter and a number.</small></div>
      <div class="form-row"><label for="confirm_password">Confirm new password</label><input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required></div>
      <div class="form-actions"><button class="btn" type="submit">Change password</button></div>
    </form>
  </div>
</div>
<?php admin_footer(); ?>
