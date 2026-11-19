<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle   = 'Resume';
$currentPage = 'resume';

$pdo = db();
$education    = $pdo->query('SELECT * FROM education ORDER BY sort_order ASC, id ASC')->fetchAll();
$experience   = $pdo->query('SELECT * FROM experience ORDER BY sort_order ASC, id DESC')->fetchAll();
$achievements = $pdo->query('SELECT * FROM achievements ORDER BY sort_order ASC, achieved_on DESC, id DESC')->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>Resume</h1>

      <h2 style="margin-top:1.5rem">Education</h2>
      <?php if ($education): ?>
      <ul class="timeline">
        <?php foreach ($education as $r): ?>
          <li>
            <strong><?= e($r['school']) ?></strong>
            <span><?= e(trim($r['level'] . ($r['level'] && $r['period'] ? ' • ' : '') . $r['period'])) ?></span>
            <?php if ($r['details']): ?><p class="tl-desc"><?= nl2br(e($r['details'])) ?></p><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?><p>No education records yet.</p><?php endif; ?>

      <?php if ($experience): ?>
      <h2 style="margin-top:2rem">Work experience</h2>
      <ul class="timeline">
        <?php foreach ($experience as $r): ?>
          <li>
            <strong><?= e($r['position']) ?><?= $r['organization'] ? ' — ' . e($r['organization']) : '' ?></strong>
            <?php if ($r['period']): ?><span><?= e($r['period']) ?></span><?php endif; ?>
            <?php if ($r['description']): ?><p class="tl-desc"><?= nl2br(e($r['description'])) ?></p><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <?php if ($achievements): ?>
      <h2 style="margin-top:2rem">Achievements &amp; certificates</h2>
      <ul class="timeline">
        <?php foreach ($achievements as $r): $img = img_url($r['image']); ?>
          <li>
            <strong><?= e($r['title']) ?></strong>
            <span><?= e(trim($r['issuer'] . ($r['issuer'] && $r['achieved_on'] ? ' • ' : '') . ($r['achieved_on'] ? date('F Y', strtotime($r['achieved_on'])) : ''))) ?></span>
            <?php if ($r['description']): ?><p class="tl-desc"><?= nl2br(e($r['description'])) ?></p><?php endif; ?>
            <?php if ($img): ?><a href="<?= e($img) ?>" target="_blank" rel="noopener"><img class="cert-thumb" src="<?= e($img) ?>" alt="<?= e($r['title']) ?>" loading="lazy"></a><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>

      <div class="btns"><a class="btn ghost" href="<?= e(url('about.php')) ?>">About me</a></div>
    </section>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
