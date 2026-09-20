<?php
$ref  = $_GET['reference']    ?? '-';
$mref = $_GET['merchant_ref'] ?? '-';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payment return — FLF</title>
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

<div class="container">
  <div class="pay-shell pay-shell--narrow">

    <ol class="pay-progress">
      <li class="is-done">Profile</li>
      <li class="is-done">Payment</li>
      <li class="is-active">Done</li>
    </ol>

    <div class="pay-result pay-result--pending">
      <div class="pay-result__icon">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
      </div>
      <h2>Thanks! We're checking your payment.</h2>
      <p>You came back from Tripay. Final status is confirmed via webhook or dashboard.</p>
    </div>

    <dl class="pay-summary">
      <div class="kv"><dt>Reference</dt>    <dd><?= htmlspecialchars($ref) ?></dd></div>
      <div class="kv"><dt>Merchant ref</dt><dd><?= htmlspecialchars($mref) ?></dd></div>
    </dl>

    <div class="pay-actions">
      <a class="btn btn-outline" href="../browse.php">Back to Lancers</a>
      <a class="btn btn-primary" href="../profile.php">Go to profile</a>
    </div>

  </div>
</div>
</body>
</html>