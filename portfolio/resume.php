<?php
$pageTitle   = 'Resume';
$currentPage = 'resume';

$education = [
    ['school' => 'Bugay Elementary School',          'detail' => 'Elementary &bull; S.Y. 2014'],
    ['school' => 'Bugay National High School',       'detail' => 'High school &bull; S.Y. 2021&ndash;2022'],
    ['school' => 'Dawis-Lapay Senior High School',   'detail' => 'Senior high school &bull; 2022&ndash;2024'],
];

include 'includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>Resume</h1>
      <h2 style="margin-top:1.5rem">Education</h2>
      <ul class="timeline">
        <?php foreach ($education as $e): ?>
          <li><strong><?= htmlspecialchars($e['school']) ?></strong><span><?= $e['detail'] ?></span></li>
        <?php endforeach; ?>
      </ul>
      <div class="btns"><a class="btn ghost" href="about.php">About me</a></div>
    </section>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
