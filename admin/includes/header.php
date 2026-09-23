<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/lang.php';

$user        = current_user();
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$pageTitle   = $pageTitle ?? 'Admin — FLF';
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="navbar">
  <div class="container navbar-inner">
    <a class="brand" href="index.php">
      <span class="brand-mark" style="background:#111827">A</span>
      <span class="brand-text">FLF<small>Admin panel</small></span>
    </a>

    <nav class="nav-links" aria-label="Admin">
      <a href="index.php"    class="<?= $currentPage === 'index.php'    ? 'is-active' : '' ?>">Actions</a>
      <a href="payments.php" class="<?= $currentPage === 'payments.php' ? 'is-active' : '' ?>">Payment</a>
    </nav>

    <div class="nav-actions">
      <a class="lang-btn"
         href="../set-lang.php?lang=<?= e(other_lang()) ?>&back=<?= urlencode('admin/' . $currentPage) ?>"
         title="<?= e(t('nav.lang_switch')) ?>">
        <svg class="lang-btn__globe" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="9"/>
          <path d="M3 12h18"/>
          <path d="M12 3a14 14 0 0 1 0 18"/>
          <path d="M12 3a14 14 0 0 0 0 18"/>
        </svg>
        <span class="lang-btn__code"><?= strtoupper(current_lang()) ?></span>
      </a>

      <a class="btn btn-ghost" href="../index.php">← Site</a>

      <form action="../logout.php" method="post" style="margin:0">
        <button class="btn btn-outline" type="submit"><?= e(t('nav.logout')) ?></button>
      </form>
    </div>
  </div>
</header>

<main></main>