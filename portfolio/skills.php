<?php
$pageTitle   = 'Skills';
$currentPage = 'skills';

$technical = ['HTML5', 'CSS3', 'Flexbox & Grid', 'JavaScript', 'Responsive design'];
$personal  = ['Communication', 'Adaptability', 'Teamwork'];

include 'includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>Skills</h1>
      <p style="margin-top:1rem">Replace these with your own skills any time.</p>

      <h3 style="margin-top:2rem">Technical</h3>
      <div class="tags">
        <?php foreach ($technical as $skill): ?>
          <span class="tag"><?= htmlspecialchars($skill) ?></span>
        <?php endforeach; ?>
      </div>

      <h3 style="margin-top:2rem">Personal</h3>
      <div class="tags">
        <?php foreach ($personal as $skill): ?>
          <span class="tag"><?= htmlspecialchars($skill) ?></span>
        <?php endforeach; ?>
      </div>

      <div class="btns"><a class="btn" href="projects.php">See my projects</a></div>
    </section>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
