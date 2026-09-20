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

<div class="container" style="max-width:640px;padding:48px 24px">
  <h1>Thanks! We're checking your payment.</h1>
  <p class="auth-sub">You came back from Tripay. The final status is confirmed via webhook / dashboard.</p>

  <dl>
    <div class="kv"><dt>Reference</dt>    <dd><?= htmlspecialchars($ref) ?></dd></div>
    <div class="kv"><dt>Merchant Ref</dt><dd><?= htmlspecialchars($mref) ?></dd></div>
  </dl>

  <p style="margin-top:24px">
    <a class="btn btn-outline" href="../browse.php">Back to Lancers</a>
  </p>
</div>
</body>
</html>