<?php
/* One-time installer. Creates the tables, loads your current portfolio content,
   and creates the first administrator. It locks itself once an admin exists.
   DELETE THIS FILE after setup. */
require_once __DIR__ . '/includes/bootstrap.php';

$pdo = db();

// 1. Create tables from database/schema.sql
$sql = file_get_contents(ROOT_PATH . '/database/schema.sql');
foreach (preg_split('/;\s*[\r\n]+/', $sql) as $stmt) {
    $stmt = trim($stmt);
    if ($stmt !== '') {
        $pdo->exec($stmt);
    }
}

$locked = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
$done   = false;
$errors = [];
$old    = ['username' => '', 'full_name' => '', 'email' => ''];

if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form expired. Please try again.';
    } else {
        $old['username']  = trim((string)($_POST['username'] ?? ''));
        $old['full_name'] = trim((string)($_POST['full_name'] ?? ''));
        $old['email']     = trim((string)($_POST['email'] ?? ''));
        $pw   = (string)($_POST['password'] ?? '');
        $pw2  = (string)($_POST['confirm'] ?? '');

        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $old['username'])) {
            $errors[] = 'Username must be 3–50 characters: letters, numbers, dot, dash or underscore.';
        }
        if ($old['full_name'] === '' || mb_strlen($old['full_name']) > 100) {
            $errors[] = 'Please enter your full name.';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($err = password_error($pw)) {
            $errors[] = $err;
        }
        if ($pw !== $pw2) {
            $errors[] = 'The passwords do not match.';
        }

        if (!$errors) {
            $pdo->prepare('INSERT INTO admins (username, full_name, email, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())')
                ->execute([$old['username'], $old['full_name'], $old['email'], password_hash($pw, PASSWORD_DEFAULT)]);

            // 2. Load the content that was previously hard-coded in the HTML pages
            if ((int)$pdo->query('SELECT COUNT(*) FROM personal_info')->fetchColumn() === 0) {
                $pdo->prepare('INSERT INTO personal_info (id, full_name, headline, intro, biography, what_i_do, what_i_value, facebook, telegram, linkedin, profile_image)
                               VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([
                    'Christian Villarosa',
                    'Student • IT Enthusiast',
                    'I enjoy learning new ideas and turning them into real, working websites. This portfolio shows my background, skills, education, and projects.',
                    'I am Christian Villarosa, an IT student who is willing to learn and accept new ideas that have real-world applications. I build my skills through education, projects, and practice.',
                    'I design and build simple, responsive websites with HTML, CSS, and JavaScript.',
                    'Clear design, clean code, and steady learning.',
                    'https://www.facebook.com/christ.villarosa.2024',
                    'https://t.me/Bealevel',
                    'https://www.linkedin.com/in/christian-villarosa-17a5b443b?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app',
                    'images/profile.jpg',
                ]);
            }
            if ((int)$pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn() === 0) {
                foreach (['site_title' => 'Christian Villarosa', 'site_tagline' => 'Student • IT Enthusiast',
                          'meta_description' => 'Portfolio of Christian Villarosa', 'footer_text' => '',
                          'accent_color' => '', 'logo' => ''] as $k => $v) {
                    save_setting($k, $v);
                }
            }
            if ((int)$pdo->query('SELECT COUNT(*) FROM skills')->fetchColumn() === 0) {
                $ins = $pdo->prepare('INSERT INTO skills (name, category, sort_order) VALUES (?, ?, ?)');
                $i = 0;
                foreach ([['HTML5','Technical'],['CSS3','Technical'],['Flexbox & Grid','Technical'],['JavaScript','Technical'],['Responsive design','Technical'],
                          ['Communication','Personal'],['Adaptability','Personal'],['Teamwork','Personal']] as [$n, $c]) {
                    $ins->execute([$n, $c, $i++]);
                }
            }
            if ((int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn() === 0) {
                $ins = $pdo->prepare('INSERT INTO projects (title, category, description, image, sort_order) VALUES (?, ?, ?, ?, ?)');
                $i = 0;
                foreach ([
                    ['Personal Profile Page', 'web', 'Web project. My first personal page built with HTML.', 'images/profile-page.jpg'],
                    ['Boring Gum? Animation', 'design', 'Design project. A colorful fruit and paint-splash animation.', 'images/boring-gum.jpg'],
                    ['Classic Tween Activity', 'academic', 'Academic project. A boat animated with a classic tween in Adobe Animate.', 'images/tween-activity.jpg'],
                    ['Squash and Stretch', 'academic', 'Academic project. A squash-and-stretch animation exercise in Adobe Animate.', 'images/squash-stretch.jpg'],
                ] as [$t, $c, $d, $im]) {
                    $ins->execute([$t, $c, $d, $im, $i++]);
                }
            }
            if ((int)$pdo->query('SELECT COUNT(*) FROM education')->fetchColumn() === 0) {
                $ins = $pdo->prepare('INSERT INTO education (school, level, period, sort_order) VALUES (?, ?, ?, ?)');
                $i = 0;
                foreach ([
                    ['Bugay Elementary School', 'Elementary', 'S.Y. 2014'],
                    ['Bugay National High School', 'High school', 'S.Y. 2021–2022'],
                    ['Dawis-Lapay Senior High School', 'Senior high school', '2022–2024'],
                ] as [$s, $l, $p]) {
                    $ins->execute([$s, $l, $p, $i++]);
                }
            }
            $done = true;
            $locked = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Portfolio Setup</title>
<link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>
<body class="auth-body">
  <div class="auth-card">
    <h1>Portfolio Setup</h1>
    <?php if ($done): ?>
      <div class="notice notice-success">Setup complete. Database tables were created and your existing content was imported.</div>
      <p><strong>Important:</strong> delete <code>setup.php</code> from your server now.</p>
      <a class="btn btn-block" href="<?= e(url('admin/login.php')) ?>">Go to admin login</a>
    <?php elseif ($locked): ?>
      <div class="notice notice-error">Setup has already been completed. For security, delete <code>setup.php</code>.</div>
      <a class="btn btn-block" href="<?= e(url('admin/login.php')) ?>">Admin login</a>
    <?php else: ?>
      <p class="muted">Tables are ready. Create the administrator account.</p>
      <?php foreach ($errors as $er): ?><div class="notice notice-error"><?= e($er) ?></div><?php endforeach; ?>
      <form method="post" action="<?= e(url('setup.php')) ?>" autocomplete="off">
        <?= csrf_field() ?>
        <div class="form-row"><label for="username">Username</label><input type="text" id="username" name="username" value="<?= e($old['username']) ?>" maxlength="50" required></div>
        <div class="form-row"><label for="full_name">Full name</label><input type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" maxlength="100" required></div>
        <div class="form-row"><label for="email">Email</label><input type="email" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="150" required></div>
        <div class="form-row"><label for="password">Password</label><input type="password" id="password" name="password" autocomplete="new-password" required><small>At least 10 characters, with a letter and a number.</small></div>
        <div class="form-row"><label for="confirm">Confirm password</label><input type="password" id="confirm" name="confirm" autocomplete="new-password" required></div>
        <button class="btn btn-block" type="submit">Create administrator</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
