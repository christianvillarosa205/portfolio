<?php
$socials = ['Facebook' => 'facebook', 'Telegram' => 'telegram', 'LinkedIn' => 'linkedin', 'GitHub' => 'github'];
$footerText = setting('footer_text', '');
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-links">
      <?php foreach ($socials as $label => $col): ?>
        <?php if (pinfo($col) !== ''): ?>
          <a href="<?= e(pinfo($col)) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <p><?= $footerText !== '' ? e($footerText) : '&copy; ' . date('Y') . ' ' . e(pinfo('full_name', setting('site_title', 'Portfolio'))) . '. All rights reserved.' ?></p>
  </div>
</footer>
<button id="scrollTop" type="button" aria-label="Scroll to top">↑</button>
<script src="<?= e(url('js/script.js')) ?>"></script>
</body>
</html>
