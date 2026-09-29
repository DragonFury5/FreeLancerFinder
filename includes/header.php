<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lang.php';

// --- Base URL so links work from any subfolder (/, /payment/, etc.) ---
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (basename($scriptDir) === 'payment') {
    $scriptDir = dirname($scriptDir);
}
$baseUrl = rtrim($scriptDir, '/') . '/';

$user        = current_user();
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$pageTitle   = $pageTitle ?? 'FLF — FreeLanceFinder';
$isPayment   = strpos($_SERVER['PHP_SELF'] ?? '', '/payment/') !== false;

$currentUrl = $_SERVER['REQUEST_URI'] ?? ($baseUrl . 'index.php');
$nextLang   = other_lang();
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<script>
(function () {
  try {
    var t = localStorage.getItem('flf-theme');
    if (t !== 'dark' && t !== 'light') {
      t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.setAttribute('data-theme', t);
  } catch (e) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>
<link rel="stylesheet" href="<?= e($baseUrl) ?>assets/css/style.css">
<?php if ($isPayment): ?>
<link rel="stylesheet" href="<?= e($baseUrl) ?>assets/css/payment.css">
<?php endif; ?>
</head>
<body>

<header class="navbar">
  <div class="container navbar-inner">
    <a class="brand" href="<?= e($baseUrl) ?>index.php">
      <span class="brand-mark">F</span>
      <span class="brand-text">FLF<small>FreeLanceFinder</small></span>
    </a>

      <nav class="nav-links" aria-label="Main">
      <a href="<?= e($baseUrl) ?>index.php"
         class="<?= $currentPage === 'index.php' ? 'is-active' : '' ?>"><?= e(t('nav.home')) ?></a>
      <a href="<?= e($baseUrl) ?>browse.php"
         class="<?= $currentPage === 'browse.php' ? 'is-active' : '' ?>"><?= e(t('nav.browse')) ?></a>
      <?php if ($user && ($user['role'] ?? '') === 'lancer'): ?>
        <a href="<?= e($baseUrl) ?>earnings.php"
           class="<?= $currentPage === 'earnings.php' ? 'is-active' : '' ?>"><?= e(t('nav.earnings')) ?></a>
      <?php endif; ?>
    </nav>
    
    <div class="nav-actions">

          <button class="theme-btn" type="button"
              data-theme-toggle
              title="Toggle theme"
              aria-label="Toggle theme">
        <svg class="theme-btn__icon theme-btn__icon--sun" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="4"/>
          <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
        </svg>
        <svg class="theme-btn__icon theme-btn__icon--moon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
        </svg>
      </button>

      <!-- Language toggle -->
      <a class="lang-btn"
         href="<?= e($baseUrl) ?>set-lang.php?lang=<?= e($nextLang) ?>&back=<?= urlencode($currentUrl) ?>"
         title="<?= e(t('nav.lang_switch')) ?>"
         aria-label="<?= e(t('nav.lang_switch')) ?>">
        <svg class="lang-btn__globe" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="12" cy="12" r="9"/>
          <path d="M3 12h18"/>
          <path d="M12 3a14 14 0 0 1 0 18"/>
          <path d="M12 3a14 14 0 0 0 0 18"/>
        </svg>
        <span class="lang-btn__code"><?= strtoupper(current_lang()) ?></span>
      </a>

      <?php if ($user): ?>
        <div class="dropdown" data-dropdown>
          <button class="avatar-btn" type="button" aria-haspopup="true" aria-expanded="false" data-dropdown-toggle>
            <span class="avatar"><?= e(initials($user['name'])) ?></span>
            <span class="avatar-name"><?= e($user['name']) ?></span>
            <svg class="chev" width="14" height="14" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="dropdown-menu" data-dropdown-menu hidden>
            <a href="<?= e($baseUrl) ?>profile.php"><?= e(t('nav.profile')) ?></a>
            <a href="<?= e($baseUrl) ?>browse.php"><?= e(t('nav.browse')) ?></a>
            <div class="dropdown-sep"></div>
            <form action="<?= e($baseUrl) ?>logout.php" method="post">
              <button type="submit" class="is-danger"><?= e(t('nav.logout')) ?></button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <a class="btn btn-ghost" href="<?= e($baseUrl) ?>login.php"><?= e(t('nav.login')) ?></a>
        <a class="btn btn-primary" href="<?= e($baseUrl) ?>register.php"><?= e(t('nav.signup')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main>