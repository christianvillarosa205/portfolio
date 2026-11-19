<?php
$pageTitle   = 'Projects';
$currentPage = 'projects';

$projects = [
    ['category' => 'web',      'image' => 'profile-page.jpg',   'alt' => 'Personal Profile Page screenshot',      'title' => 'Personal Profile Page',   'text' => 'Web project. My first personal page built with HTML.'],
    ['category' => 'design',   'image' => 'boring-gum.jpg',     'alt' => 'Boring Gum? Animation screenshot',      'title' => 'Boring Gum? Animation',   'text' => 'Design project. A colorful fruit and paint-splash animation.'],
    ['category' => 'academic', 'image' => 'tween-activity.jpg', 'alt' => 'Classic Tween Activity screenshot',     'title' => 'Classic Tween Activity',  'text' => 'Academic project. A boat animated with a classic tween in Adobe Animate.'],
    ['category' => 'academic', 'image' => 'squash-stretch.jpg', 'alt' => 'Squash and Stretch screenshot',         'title' => 'Squash and Stretch',      'text' => 'Academic project. A squash-and-stretch animation exercise in Adobe Animate.'],
];

include 'includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>Projects</h1>
      <div class="filters" role="group" aria-label="Filter projects">
        <button class="filter-btn active" type="button" data-filter="all" aria-pressed="true">All</button>
        <button class="filter-btn" type="button" data-filter="web" aria-pressed="false">Web</button>
        <button class="filter-btn" type="button" data-filter="design" aria-pressed="false">Design</button>
        <button class="filter-btn" type="button" data-filter="academic" aria-pressed="false">Academic</button>
      </div>
      <div class="grid">
        <?php foreach ($projects as $p): ?>
          <article class="card project" data-category="<?= htmlspecialchars($p['category']) ?>">
            <div class="thumb"><img src="images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['alt']) ?>" loading="lazy"></div>
            <div class="body">
              <h3><?= htmlspecialchars($p['title']) ?></h3>
              <p><?= htmlspecialchars($p['text']) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="btns"><a class="btn ghost" href="contact.php">Work with me</a></div>
    </section>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
