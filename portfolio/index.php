<?php
$pageTitle   = 'Home';
$currentPage = 'index';
include 'includes/header.php';
?>
<main>
  <div class="container">
    <section class="hero">
      <div>
        <p>Hello, I'm</p>
        <h1>Christian Villarosa</h1>
        <p class="role">Student &bull; IT Enthusiast</p>
        <p>I enjoy learning new ideas and turning them into real, working websites. This portfolio shows my background, skills, education, and projects.</p>
        <div class="btns">
          <a class="btn" href="projects.php">View my projects</a>
          <a class="btn ghost" href="about.php">About me</a>
          <a class="btn ghost" href="contact.php">Contact me</a>
        </div>
      </div>
      <img class="avatar" src="images/profile.jpg" alt="Photo of Christian Villarosa">
    </section>

    <section class="block">
      <h2>Welcome to my portfolio</h2>
      <p>Explore each section to learn more about me.</p>
      <div class="grid">
        <div class="card"><h3>About me</h3><p>Who I am and what I enjoy working on.</p><a class="more" href="about.php">Read more</a></div>
        <div class="card"><h3>Skills</h3><p>The tools and abilities I use and keep improving.</p><a class="more" href="skills.php">See skills</a></div>
        <div class="card"><h3>Projects</h3><p>Academic and personal work, filtered by type.</p><a class="more" href="projects.php">Browse projects</a></div>
      </div>
    </section>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
