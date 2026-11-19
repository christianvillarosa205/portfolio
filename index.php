<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle   = 'Home';
$currentPage = 'index';

$featured = db()->query('SELECT * FROM projects ORDER BY sort_order ASC, id DESC LIMIT 3')->fetchAll();
$avatar   = img_url(pinfo('profile_image'), 'images/profile.jpg');

include __DIR__ . '/includes/header.php';
?>
<main>
  <div class="container">
    <section class="hero">
      <div>
        <p>Hello, I'm</p>
        <h1><?= e(pinfo('full_name', 'Your Name')) ?></h1>
        <?php if (pinfo('headline') !== ''): ?><p class="role"><?= e(pinfo('headline')) ?></p><?php endif; ?>
        <p><?= nl2br(e(pinfo('intro'))) ?></p>
        <div class="btns">
          <a class="btn" href="<?= e(url('projects.php')) ?>">View my projects</a>
          <a class="btn ghost" href="<?= e(url('about.php')) ?>">About me</a>
          <a class="btn ghost" href="<?= e(url('contact.php')) ?>">Contact me</a>
        </div>
      </div>
      <img class="avatar" src="<?= e($avatar) ?>" alt="Photo of <?= e(pinfo('full_name', 'me')) ?>">
    </section>

    <section class="block">
      <h2>Welcome to my portfolio</h2>
      <p>Explore each section to learn more about me.</p>
      <div class="grid">
        <div class="card"><h3>About me</h3><p>Who I am and what I enjoy working on.</p><a class="more" href="<?= e(url('about.php')) ?>">Read more</a></div>
        <div class="card"><h3>Skills</h3><p>The tools and abilities I use and keep improving.</p><a class="more" href="<?= e(url('skills.php')) ?>">See skills</a></div>
        <div class="card"><h3>Resume</h3><p>Education, experience and achievements.</p><a class="more" href="<?= e(url('resume.php')) ?>">View resume</a></div>
      </div>
    </section>

    <?php if ($featured): ?>
    <section class="block">
      <h2>Featured projects</h2>
      <div class="grid">
        <?php foreach ($featured as $p) { render_project_card($p); } ?>
      </div>
      <div class="btns"><a class="btn ghost" href="<?= e(url('projects.php')) ?>">See all projects</a></div>
    </section>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
