<?php
$pageTitle   = 'About';
$currentPage = 'about';
include 'includes/header.php';
?>
<main>
  <div class="container">
    <section class="block page-title">
      <h1>About me</h1>
      <p style="margin-top:1rem;max-width:65ch">I am Christian Villarosa, an IT student who is willing to learn and accept new ideas that have real-world applications. I build my skills through education, projects, and practice.</p>
      <div class="grid">
        <div class="card"><h3>What I do</h3><p>I design and build simple, responsive websites with HTML, CSS, and JavaScript.</p></div>
        <div class="card"><h3>What I value</h3><p>Clear design, clean code, and steady learning.</p></div>
      </div>
      <div class="btns">
        <a class="btn" href="resume.php">View my resume</a>
        <a class="btn ghost" href="index.php">Back to home</a>
      </div>
    </section>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
