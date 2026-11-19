<?php
$pageTitle   = 'Contact';
$currentPage = 'contact';
include 'includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>Contact</h1>
      <p style="margin-top:1rem">Send a message or reach me on social media.</p>
      <form id="contactForm" novalidate>
        <div><label for="name">Name</label><input id="name" name="name" type="text" autocomplete="name"><div class="error" id="nameError"></div></div>
        <div><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email"><div class="error" id="emailError"></div></div>
        <div><label for="message">Message</label><textarea id="message" name="message"></textarea><div class="error" id="messageError"></div></div>
        <div><button class="btn" type="submit">Send message</button></div>
      </form>
      <p class="success" id="formSuccess" role="status">Your message passed validation. Connect the form to a service like Formspree to receive it.</p>
      <div class="socials">
        <a class="btn ghost" href="https://www.facebook.com/christ.villarosa.2024" target="_blank" rel="noopener">Facebook</a>
        <a class="btn ghost" href="https://t.me/Bealevel" target="_blank" rel="noopener">Telegram</a>
        <a class="btn ghost" href="https://www.linkedin.com/in/christian-villarosa-17a5b443b?utm_source=share&amp;utm_campaign=share_via&amp;utm_content=profile&amp;utm_medium=android_app" target="_blank" rel="noopener">LinkedIn</a>
      </div>
    </section>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
