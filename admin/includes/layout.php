<?php
function admin_header(string $title, string $active = ''): void
{
    $admin  = current_admin();
    $unread = unread_count();
    $nav = [
        'dashboard'    => ['index.php',        'Dashboard',        '📊'],
        'personal'     => ['personal.php',     'Personal Info',    '👤'],
        'skills'       => ['skills.php',       'Skills',           '🛠️'],
        'projects'     => ['projects.php',     'Projects',         '🗂️'],
        'education'    => ['education.php',    'Education',        '🎓'],
        'experience'   => ['experience.php',   'Experience',       '💼'],
        'achievements' => ['achievements.php', 'Achievements',      '🏆'],
        'messages'     => ['messages.php',     'Messages',         '✉️'],
        'settings'     => ['settings.php',     'Website Settings', '⚙️'],
        'account'      => ['account.php',      'My Account',       '🔐'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> | Admin</title>
<link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>">
</head>
<body>
<div class="admin-shell">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">Admin Panel</div>
    <nav>
      <?php foreach ($nav as $key => [$file, $label, $icon]): ?>
        <a href="<?= e(url('admin/' . $file)) ?>" class="<?= $key === $active ? 'active' : '' ?>">
          <span class="ico"><?= $icon ?></span><?= e($label) ?>
          <?php if ($key === 'messages' && $unread > 0): ?><span class="badge"><?= (int)$unread ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-foot">
      <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">↗ View website</a>
      <form method="post" action="<?= e(url('admin/logout.php')) ?>">
        <?= csrf_field() ?>
        <button type="submit" class="link-btn">Sign out</button>
      </form>
    </div>
  </aside>
  <div class="main">
    <header class="topbar">
      <button type="button" class="menu-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
      <h1><?= e($title) ?></h1>
      <span class="who">Signed in as <strong><?= e($admin['username'] ?? '') ?></strong></span>
    </header>
    <main class="content">
      <?= flash_render('notice') ?>
<?php
}

function admin_footer(): void
{
    ?>
    </main>
  </div>
</div>
<script>
document.getElementById('navToggle').addEventListener('click', function () {
  document.getElementById('sidebar').classList.toggle('open');
});
document.addEventListener('submit', function (e) {
  var m = e.target.getAttribute('data-confirm');
  if (m && !window.confirm(m)) { e.preventDefault(); }
});
</script>
</body>
</html>
<?php
}
