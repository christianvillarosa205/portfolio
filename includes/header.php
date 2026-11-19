<?php
require_once __DIR__ . '/bootstrap.php';

$pageTitle   = $pageTitle   ?? 'Home';
$currentPage = $currentPage ?? '';

$siteTitle = setting('site_title', pinfo('full_name', 'My Portfolio'));
$metaDesc  = setting('meta_description', 'Portfolio of ' . $siteTitle);
$accent    = setting('accent_color', '');
$logo      = setting('logo', '');

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
<meta name="description" content="<?= e($metaDesc) ?>">
<title><?= e($pageTitle) ?> | <?= e($siteTitle) ?></title>
<link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
<link rel="stylesheet" href="<?= e(url('css/extra.css')) ?>">
<?php if (preg_match('/^#[0-9a-fA-F]{6}$/', $accent)): ?>
<style>:root{--site-accent:<?= e($accent) ?>}</style>
<?php endif; ?>
</head>
<body class="<?= $accent ? 'has-accent' : '' ?>">
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="<?= e(url('index.php')) ?>">
      <?php if ($logo): ?>
        <img class="logo-img" src="<?= e(img_url($logo)) ?>" alt="<?= e($siteTitle) ?>">
      <?php else: ?>
        <?= e($siteTitle) ?>
      <?php endif; ?>
    </a>
    <ul class="nav-links" id="navLinks">
      <?php foreach ($navItems as $slug => $label): ?>
        <li><a href="<?= e(url($slug . '.php')) ?>"<?= $slug === $currentPage ? ' class="active" aria-current="page"' : '' ?>><?= e($label) ?></a></li>
      <?php endforeach; ?>
    </ul>
    <div class="nav-tools">
      <button class="icon-btn" id="themeToggle" type="button">🌙</button>
      <button class="icon-btn menu-btn" id="menuBtn" type="button" aria-label="Toggle menu" aria-expanded="false">☰</button>
    </div>
  </div>
</header>
