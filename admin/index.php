<?php
require __DIR__ . '/includes/init.php';
$pdo = db();

$counts = [];
foreach (['projects', 'skills', 'education', 'experience', 'achievements', 'messages'] as $t) {
    $counts[$t] = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
}
$unread = unread_count();
$recent = $pdo->query('SELECT id, name, subject, is_read, created_at FROM messages ORDER BY created_at DESC, id DESC LIMIT 5')->fetchAll();
$admin  = current_admin();

admin_header('Dashboard', 'dashboard');
$cards = [
    ['Projects',      $counts['projects'],     'projects.php',     ''],
    ['Skills',        $counts['skills'],       'skills.php',       ''],
    ['Messages',      $counts['messages'],     'messages.php',     $unread . ' unread'],
    ['Experience',    $counts['experience'],   'experience.php',   ''],
    ['Achievements',  $counts['achievements'], 'achievements.php', ''],
    ['Education',     $counts['education'],    'education.php',    ''],
];
?>
<div class="stats">
  <?php foreach ($cards as [$label, $n, $file, $sub]): ?>
    <a class="stat" href="<?= e(url('admin/' . $file)) ?>">
      <span class="stat-n"><?= (int)$n ?></span>
      <span class="stat-l">Total <?= e(strtolower($label)) ?></span>
      <?php if ($sub): ?><span class="stat-s"><?= e($sub) ?></span><?php endif; ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="two-col">
  <div class="panel">
    <div class="panel-head"><h2>Recent messages</h2><a href="<?= e(url('admin/messages.php')) ?>">View all</a></div>
    <?php if (!$recent): ?>
      <p class="muted">No messages yet.</p>
    <?php else: ?>
      <ul class="list">
        <?php foreach ($recent as $m): ?>
          <li>
            <a href="<?= e(url('admin/messages.php?view=' . (int)$m['id'])) ?>">
              <?php if (!$m['is_read']): ?><span class="dot" title="Unread"></span><?php endif; ?>
              <strong><?= e($m['name']) ?></strong> — <?= e($m['subject']) ?>
            </a>
            <span class="muted"><?= e(date('M j, Y g:i A', strtotime($m['created_at']))) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Administrator</h2><a href="<?= e(url('admin/account.php')) ?>">Settings</a></div>
    <dl class="profile">
      <dt>Name</dt><dd><?= e($admin['full_name']) ?></dd>
      <dt>Username</dt><dd><?= e($admin['username']) ?></dd>
      <dt>Email</dt><dd><?= e($admin['email']) ?></dd>
      <dt>Last login</dt><dd><?= $admin['last_login'] ? e(date('M j, Y g:i A', strtotime($admin['last_login']))) : '—' ?></dd>
    </dl>
    <div class="quick">
      <a class="btn btn-sm" href="<?= e(url('admin/projects.php?action=add')) ?>">+ Project</a>
      <a class="btn btn-sm" href="<?= e(url('admin/skills.php?action=add')) ?>">+ Skill</a>
      <a class="btn btn-sm" href="<?= e(url('admin/achievements.php?action=add')) ?>">+ Achievement</a>
    </div>
  </div>
</div>
<?php admin_footer(); ?>
