<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle   = 'Projects';
$currentPage = 'projects';

$projects = db()->query('SELECT * FROM projects ORDER BY sort_order ASC, id DESC')->fetchAll();
$cats = [];
foreach ($projects as $p) {
    $cats[$p['category']] = ucfirst($p['category']);
}
ksort($cats);

include __DIR__ . '/includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>Projects</h1>
      <?php if ($projects): ?>
      <div class="filters" role="group" aria-label="Filter projects">
        <button class="filter-btn active" type="button" data-filter="all" aria-pressed="true">All</button>
        <?php foreach ($cats as $slug => $label): ?>
          <button class="filter-btn" type="button" data-filter="<?= e($slug) ?>" aria-pressed="false"><?= e($label) ?></button>
        <?php endforeach; ?>
      </div>
      <div class="grid">
        <?php foreach ($projects as $p) { render_project_card($p); } ?>
      </div>
      <?php else: ?>
        <p style="margin-top:1rem">Projects will appear here soon.</p>
      <?php endif; ?>
      <div class="btns"><a class="btn ghost" href="<?= e(url('contact.php')) ?>">Work with me</a></div>
    </section>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
