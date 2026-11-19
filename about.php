<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle   = 'About';
$currentPage = 'about';
include __DIR__ . '/includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>About me</h1>
      <p style="margin-top:1rem;max-width:65ch"><?= nl2br(e(pinfo('biography'))) ?></p>
      <div class="grid">
        <?php if (pinfo('what_i_do') !== ''): ?>
          <div class="card"><h3>What I do</h3><p><?= nl2br(e(pinfo('what_i_do'))) ?></p></div>
        <?php endif; ?>
        <?php if (pinfo('what_i_value') !== ''): ?>
          <div class="card"><h3>What I value</h3><p><?= nl2br(e(pinfo('what_i_value'))) ?></p></div>
        <?php endif; ?>
      </div>
      <div class="btns">
        <a class="btn" href="<?= e(url('resume.php')) ?>">View my resume</a>
        <a class="btn ghost" href="<?= e(url('index.php')) ?>">Back to home</a>
      </div>
    </section>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
