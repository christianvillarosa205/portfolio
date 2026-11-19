<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle   = 'Skills';
$currentPage = 'skills';

$groups = [];
foreach (db()->query('SELECT name, category FROM skills ORDER BY sort_order ASC, id ASC') as $s) {
    $groups[$s['category']][] = $s['name'];
}
// Show well-known categories first, in a fixed order
$order = ['Technical', 'Tools', 'Languages', 'Personal', 'Other'];
uksort($groups, function ($a, $b) use ($order) {
    $ia = array_search($a, $order); $ib = array_search($b, $order);
    return ($ia === false ? 99 : $ia) <=> ($ib === false ? 99 : $ib);
});

include __DIR__ . '/includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>Skills</h1>
      <?php if (!$groups): ?>
        <p style="margin-top:1rem">Skills will appear here soon.</p>
      <?php endif; ?>
      <?php foreach ($groups as $category => $names): ?>
        <h3 style="margin-top:2rem"><?= e($category) ?></h3>
        <div class="tags">
          <?php foreach ($names as $n): ?><span class="tag"><?= e($n) ?></span><?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      <div class="btns"><a class="btn" href="<?= e(url('projects.php')) ?>">See my projects</a></div>
    </section>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
