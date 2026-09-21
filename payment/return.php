<?php
require_once __DIR__ . '/../includes/lang.php';
$ref  = $_GET['reference']    ?? '-';
$mref = $_GET['merchant_ref'] ?? '-';
?>
<html lang="<?= e(current_lang()) ?>">
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran selesai — FLF</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/payment.css">
</head>
<body>

<header class="navbar">
  <div class="container navbar-inner">
    <a class="brand" href="../index.php">
      <span class="brand-mark">F</span>
      <span class="brand-text">FLF<small>FreeLanceFinder</small></span>
    </a>
  </div>
</header>

<main>
  <div class="container">
    <div class="pay-shell pay-shell--narrow">

      <ol class="pay-progress">
        <?= e(t('pay.step_profile')) ?>
        <?= e(t('pay.step_payment')) ?>
        <?= e(t('pay.step_payment')) ?>
      </ol>

      <div class="pay-result pay-result--pending">
        <div class="pay-result__icon">
          <svg viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <polyline points="12 7 12 12 15 15"/>
          </svg>
        </div>
        <?= e(t('pr.thanks_t')) ?>
         <?= e(t('pr.thanks_s')) ?>      </div>

      <dl class="pay-summary">
        <div class="kv">	<?= e(t('pi.sum_ref')) ?>        <dd><?= htmlspecialchars($ref) ?></dd></div>
        <div class="kv"><?= e(t('pi.sum_mref')) ?>  <dd><?= htmlspecialchars($mref) ?></dd></div>
      </dl>

      <div class="pay-actions">
        <a class="btn btn-outline" href="../browse.php"><?= e(t('pr.back_browse')) ?></a>
        <a class="btn btn-primary" href="../profile.php"><?= e(t('pr.open_profile')) ?></a>
      </div>

    </div>
  </div>
</main>

</body>
</html>