<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$pageTitle = $pageTitle ?? 'FLF — FreeLanceFinder';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
  <div class="container navbar-inner">
    <a class="brand" href="index.php">
      <span class="brand-mark">F</span>
      <span class="brand-text">FLF<small>FreeLanceFinder</small></span>
    </a>

    <nav class="nav-links" aria-label="Main">
      <a href="index.php" class="<?= $currentPage === 'index.php' ? 'is-active' : '' ?>">Home</a>
      <a href="browse.php" class="<?= $currentPage === 'browse.php' ? 'is-active' : '' ?>">Find Lancers</a>
    </nav>

    <div class="nav-actions">
      <?php if ($user): ?>
        <div class="dropdown" data-dropdown>
          <button class="avatar-btn" type="button" aria-haspopup="true" aria-expanded="false" data-dropdown-toggle>
            <span class="avatar"><?= e(initials($user['name'])) ?></span>
            <span class="avatar-name"><?= e($user['name']) ?></span>
            <svg class="chev" width="14" height="14" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="dropdown-menu" data-dropdown-menu hidden>
            <a href="profile.php">My Profile</a>
            <a href="browse.php">Find Lancers</a>
            <div class="dropdown-sep"></div>
            <form action="logout.php" method="post">
              <button type="submit" class="is-danger">Log out</button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <a class="btn btn-ghost" href="login.php">Log in</a>
        <a class="btn btn-primary" href="register.php">Sign up</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main></main>