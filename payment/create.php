<?php
require 'config.php';

$method     = $_POST['method']      ?? '';
$amount     = (int)($_POST['amount'] ?? 0);
$name       = $_POST['name']        ?? 'Test User';
$email      = $_POST['email']       ?? 'test@example.com';
$phone      = $_POST['phone']       ?? '08123456789';
$lancerId   = (int)($_POST['lancer_id'] ?? 0);
$lancerName = trim($_POST['lancer_name'] ?? '');

if ($method === '' || $amount <= 0) {
    die('Invalid input.');
}

$merchantRef = 'INV-' . time() . '-' . rand(100, 999);

$signature = hash_hmac(
    'sha256',
    TRIPAY_MERCHANT_CODE . $merchantRef . $amount,
    TRIPAY_PRIVATE_KEY
);

$itemName = $lancerName !== ''
    ? 'Payment to ' . $lancerName
    : 'Sandbox Test Item';

$payload = [
    'method'         => $method,
    'merchant_ref'   => $merchantRef,
    'amount'         => $amount,
    'customer_name'  => $name,
    'customer_email' => $email,
    'customer_phone' => $phone,
    'order_items'    => [[
        'sku'      => $lancerId > 0 ? 'LANCER-' . $lancerId : 'TEST-01',
        'name'     => $itemName,
        'price'    => $amount,
        'quantity' => 1,
    ]],
    'callback_url'   => TRIPAY_CALLBACK_URL,
    'return_url'     => TRIPAY_RETURN_URL,
    'expired_time'   => time() + (24 * 60 * 60),
    'signature'      => $signature,
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_FRESH_CONNECT  => true,
    CURLOPT_URL            => TRIPAY_BASE_URL . '/transaction/create',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . TRIPAY_API_KEY,
        'Content-Type: application/json',
    ],
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
]);
$response = curl_exec($curl);
$error    = curl_error($curl);
curl_close($curl);

$result = json_decode($response, true);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Instructions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="container navbar-inner">
    <a class="brand" href="../index.php">
      <span class="brand-mark">F</span>
      <span class="brand-text">FLF<small>FreeLanceFinder</small></span>
    </a>
    <nav class="nav-links">
      <a href="../index.php">Home</a>
      <a href="../browse.php">Find Lancers</a>
    </nav>
  </div>
</header>

<div class="container" style="max-width:720px;padding:48px 24px">
  <h1>Payment instructions</h1>

  <?php if ($lancerName !== ''): ?>
    <p class="auth-sub">
      Paying <strong><?= htmlspecialchars($lancerName) ?></strong>
      — reference <code><?= htmlspecialchars($merchantRef) ?></code>
    </p>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
      <div class="alert alert-error">cURL error: <?= htmlspecialchars($error) ?></div>
  <?php elseif (empty($result['success'])): ?>
      <div class="alert alert-error">
          <strong>Failed:</strong>
          <?= htmlspecialchars($result['message'] ?? $response) ?>
      </div>
  <?php else: $d = $result['data']; ?>

      <dl style="margin:24px 0">
          <div class="kv"><dt>Reference</dt>    <dd><?= htmlspecialchars($d['reference']) ?></dd></div>
          <div class="kv"><dt>Merchant Ref</dt><dd><?= htmlspecialchars($d['merchant_ref']) ?></dd></div>
          <div class="kv"><dt>Method</dt>      <dd><?= htmlspecialchars($d['payment_name']) ?></dd></div>
          <div class="kv"><dt>Amount</dt>      <dd>Rp <?= number_format($d['amount'], 0, ',', '.') ?></dd></div>
          <div class="kv"><dt>Fee</dt>         <dd>Rp <?= number_format($d['fee_merchant'], 0, ',', '.') ?></dd></div>
          <div class="kv"><dt>Status</dt>      <dd><?= htmlspecialchars($d['status']) ?></dd></div>
          <div class="kv"><dt>Expires</dt>     <dd><?= date('d M Y H:i', $d['expired_time']) ?></dd></div>
      </dl>

      <?php if (!empty($d['pay_code'])): ?>
          <div class="panel" style="margin-bottom:20px">
              <div class="kv"><dt>Payment code / VA</dt><dd><?= htmlspecialchars($d['pay_code']) ?></dd></div>
          </div>
      <?php endif; ?>

      <?php if (!empty($d['qr_url'])): ?>
          <div class="panel" style="margin-bottom:20px">
              <p><strong>QR Code</strong></p>
              <p><img src="<?= htmlspecialchars($d['qr_url']) ?>" alt="QR" style="max-width:220px"></p>
          </div>
      <?php endif; ?>

      <?php if (!empty($d['instructions'])): ?>
          <div class="panel" style="margin-bottom:20px">
              <p><strong>Instructions</strong></p>
              <?php foreach ($d['instructions'] as $grp): ?>
                  <p><em><?= htmlspecialchars($grp['title']) ?></em></p>
                  <ol>
                      <?php foreach ($grp['steps'] as $step): ?>
                          <li><?= $step ?></li>
                      <?php endforeach; ?>
                  </ol>
              <?php endforeach; ?>
          </div>
      <?php endif; ?>

      <?php if (!empty($d['checkout_url'])): ?>
          <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars($d['checkout_url']) ?>" target="_blank">
              Open Tripay Checkout
          </a>
      <?php endif; ?>

      <p style="margin-top:24px">
          <a href="../profile.php?id=<?= (int)$lancerId ?>">← Back to profile</a>
      </p>
  <?php endif; ?>
</div>
</body>
</html>