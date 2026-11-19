<?php
$pageTitle   = $pageTitle   ?? 'Home';
$currentPage = $currentPage ?? '';

$navItems = [
    'index'    => 'Home',
    'about'    => 'About',
    'skills'   => 'Skills',
    'projects' => 'Projects',
    'resume'   => 'Resume',
    'contact'  => 'Contact',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Portfolio of Christian Villarosa">
<title><?= htmlspecialchars($pageTitle) ?> | Christian Villarosa</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="index.php">Christian Villarosa</a>
    <ul class="nav-links" id="navLinks">
      <?php foreach ($navItems as $slug => $label): ?>
        <li><a href="<?= $slug ?>.php"<?= $slug === $currentPage ? ' class="active" aria-current="page"' : '' ?>><?= $label ?></a></li>
      <?php endforeach; ?>
    </ul>
    <div class="nav-tools">
      <button class="icon-btn" id="themeToggle" type="button">🌙</button>
      <button class="icon-btn menu-btn" id="menuBtn" type="button" aria-label="Toggle menu" aria-expanded="false">☰</button>
    </div>
  </div>
</header>
