<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle   = 'Contact';
$currentPage = 'contact';

$errors = [];
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Your form expired. Please reload the page and try again.';
    } else {
        foreach ($old as $k => $_) {
            $old[$k] = trim((string)($_POST[$k] ?? ''));
        }

        // Spam trap 1: hidden field that humans never fill in
        if (!empty($_POST['website'])) {
            flash_set('success', 'Thank you! Your message has been sent.');
            redirect('contact.php');
        }
        // Spam trap 2: forms submitted faster than a human could type
        if (time() - (int)($_SESSION['contact_started'] ?? 0) < 3) {
            $errors['form'] = 'That was very fast. Please wait a moment and submit again.';
        }

        if (mb_strlen($old['name']) < 2 || mb_strlen($old['name']) > 100) {
            $errors['name'] = 'Please enter your name (2–100 characters).';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if (mb_strlen($old['subject']) < 3 || mb_strlen($old['subject']) > 150) {
            $errors['subject'] = 'Please enter a subject (3–150 characters).';
        }
        if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 2000) {
            $errors['message'] = 'Your message must be between 10 and 2000 characters.';
        }

        // Spam trap 3: limit submissions per IP address
        if (!$errors) {
            $st = db()->prepare('SELECT COUNT(*) FROM messages WHERE ip_address = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)');
            $st->execute([client_ip()]);
            if ((int)$st->fetchColumn() >= 3) {
                $errors['form'] = 'You have sent several messages recently. Please try again in a few minutes.';
            }
        }

        if (!$errors) {
            db()->prepare('INSERT INTO messages (name, email, subject, message, ip_address, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())')
                ->execute([$old['name'], $old['email'], $old['subject'], $old['message'], client_ip()]);
            unset($_SESSION['contact_started'], $_SESSION['csrf_token']);
            flash_set('success', 'Thank you! Your message has been sent.');
            redirect('contact.php');
        }
    }
}
if (empty($_SESSION['contact_started'])) {
    $_SESSION['contact_started'] = time();
}

$socials = ['Facebook' => 'facebook', 'Telegram' => 'telegram', 'LinkedIn' => 'linkedin', 'GitHub' => 'github'];
include __DIR__ . '/includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>Contact</h1>
      <p style="margin-top:1rem">Send a message or reach me directly.</p>

      <?= flash_render('alert') ?>
      <?php if (isset($errors['form'])): ?><div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>

      <?php if (pinfo('email') || pinfo('phone') || pinfo('location')): ?>
      <ul class="contact-info">
        <?php if (pinfo('email')): ?><li><strong>Email:</strong> <a href="mailto:<?= e(pinfo('email')) ?>"><?= e(pinfo('email')) ?></a></li><?php endif; ?>
        <?php if (pinfo('phone')): ?><li><strong>Phone:</strong> <?= e(pinfo('phone')) ?></li><?php endif; ?>
        <?php if (pinfo('location')): ?><li><strong>Location:</strong> <?= e(pinfo('location')) ?></li><?php endif; ?>
      </ul>
      <?php endif; ?>

      <form id="contactFormServer" method="post" action="<?= e(url('contact.php')) ?>">
        <?= csrf_field() ?>
        <div class="hp-field" aria-hidden="true">
          <label for="website">Leave this field empty</label>
          <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>
        <div><label for="name">Name</label><input id="name" name="name" type="text" autocomplete="name" maxlength="100" value="<?= e($old['name']) ?>" required><?php if (isset($errors['name'])): ?><div class="error" style="display:block"><?= e($errors['name']) ?></div><?php endif; ?></div>
        <div><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" maxlength="150" value="<?= e($old['email']) ?>" required><?php if (isset($errors['email'])): ?><div class="error" style="display:block"><?= e($errors['email']) ?></div><?php endif; ?></div>
        <div><label for="subject">Subject</label><input id="subject" name="subject" type="text" maxlength="150" value="<?= e($old['subject']) ?>" required><?php if (isset($errors['subject'])): ?><div class="error" style="display:block"><?= e($errors['subject']) ?></div><?php endif; ?></div>
        <div><label for="message">Message</label><textarea id="message" name="message" maxlength="2000" required><?= e($old['message']) ?></textarea><?php if (isset($errors['message'])): ?><div class="error" style="display:block"><?= e($errors['message']) ?></div><?php endif; ?></div>
        <div><button class="btn" type="submit">Send message</button></div>
      </form>

      <div class="socials">
        <?php foreach ($socials as $label => $col): ?>
          <?php if (pinfo($col) !== ''): ?>
            <a class="btn ghost" href="<?= e(pinfo($col)) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
