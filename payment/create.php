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
    <link rel="stylesheet" href="../assets/css/payment.css">
</head>

<script>
document.querySelectorAll('[data-copy]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var target = document.querySelector(btn.getAttribute('data-copy'));
    if (!target) return;
    var text = target.textContent.trim();
    navigator.clipboard.writeText(text).then(function () {
      var original = btn.innerHTML;
      btn.classList.add('is-copied');
      btn.innerHTML = '✓ Copied';
      setTimeout(function () {
        btn.classList.remove('is-copied');
        btn.innerHTML = original;
      }, 1600);
    });
  });
});
</script>
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

  <div class="pay-result pay-result--error">
    <div class="pay-result__icon">
      <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </div>
    <h2>Couldn't reach Tripay</h2>
    <p><?= htmlspecialchars($error) ?></p>
  </div>

<?php elseif (empty($result['success'])): ?>

  <div class="pay-result pay-result--error">
    <div class="pay-result__icon">
      <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </div>
    <h2>Payment failed</h2>
    <p><?= htmlspecialchars($result['message'] ?? 'Unknown error from Tripay.') ?></p>
  </div>

<?php else: $d = $result['data']; ?>

  <ol class="pay-progress">
    <li class="is-done">Profile</li>
    <li class="is-done">Payment</li>
    <li class="is-active">Pay now</li>
  </ol>

  <!-- Amount banner -->
  <div class="pay-amount">
    <span class="label">Amount due</span>
    <span class="value">Rp <?= number_format($d['amount'], 0, ',', '.') ?></span>
    <span class="meta">
      <?= htmlspecialchars($d['payment_name']) ?>
      <?php if ($lancerName !== ''): ?>
        · <?= htmlspecialchars($lancerName) ?>
      <?php endif; ?>
    </span>
  </div>

  <!-- Summary -->
  <dl class="pay-summary">
    <div class="kv">
      <dt>Status</dt>
      <dd><span class="pay-status pay-status--<?= strtolower($d['status']) ?>"><?= htmlspecialchars($d['status']) ?></span></dd>
    </div>
    <div class="kv"><dt>Reference</dt>     <dd><?= htmlspecialchars($d['reference']) ?></dd></div>
    <div class="kv"><dt>Merchant ref</dt>  <dd><?= htmlspecialchars($d['merchant_ref']) ?></dd></div>
    <div class="kv"><dt>Fee</dt>           <dd>Rp <?= number_format($d['fee_merchant'], 0, ',', '.') ?></dd></div>
    <div class="kv"><dt>Expires</dt>       <dd><?= date('d M Y H:i', $d['expired_time']) ?></dd></div>
  </dl>

  <?php if (!empty($d['pay_code'])): ?>
    <div class="pay-code">
      <span class="label">Payment code / VA</span>
      <span class="value" id="payCode"><?= htmlspecialchars($d['pay_code']) ?></span>
      <button type="button" class="copy" data-copy="#payCode">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="9" y="9" width="13" height="13" rx="2"/>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
        </svg>
        Copy code
      </button>
    </div>
  <?php endif; ?>

  <?php if (!empty($d['qr_url']) || !empty($d['qr_string'])): ?>
    <div class="pay-qr">
      <div class="label">Scan to pay</div>
      <?php if (!empty($d['qr_url'])): ?>
        <img src="<?= htmlspecialchars($d['qr_url']) ?>" alt="Payment QR code">
      <?php endif; ?>
      <?php if (!empty($d['qr_string'])): ?>
        <pre><?= htmlspecialchars($d['qr_string']) ?></pre>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($d['instructions'])): ?>
    <div class="pay-steps">
      <div class="label">How to pay</div>
      <?php foreach ($d['instructions'] as $grp): ?>
        <div class="pay-steps__group">
          <h4><?= htmlspecialchars($grp['title']) ?></h4>
          <ol>
            <?php foreach ($grp['steps'] as $step): ?>
              <li><?= $step ?></li>
            <?php endforeach; ?>
          </ol>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="pay-actions">
    <a class="btn btn-outline" href="../profile.php?id=<?= (int)$lancerId ?>">← Back to profile</a>
    <?php if (!empty($d['checkout_url'])): ?>
      <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars($d['checkout_url']) ?>" target="_blank" rel="noopener">
        Open Tripay Checkout →
      </a>
    <?php endif; ?>
  </div>

<?php endif; ?>
</div>
</body>
</html>