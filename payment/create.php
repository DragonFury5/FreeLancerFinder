<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';   // ← was missing — caused the blank page
require_once __DIR__ . '/../includes/lang.php';
require 'config.php';

$method     = $_POST['method']      ?? '';
$name       = $_POST['name']        ?? 'Guest';
$email      = $_POST['email']       ?? 'guest@example.com';
$phone      = $_POST['phone']       ?? '08123456789';
$lancerId   = (int)($_POST['lancer_id'] ?? 0);
$lancerName = trim($_POST['lancer_name'] ?? '');
$donation   = 0;

$me   = current_user();
$meId = $me ? (int)$me['id'] : null;

// --- Amount: always read from the DB (never trust the client) ---
$amount = 0;
if ($lancerId > 0) {
    $stmt = $pdo->prepare("SELECT rate FROM lancers WHERE user_id = ?");
    $stmt->execute([$lancerId]);
    $row = $stmt->fetch();
    if ($row) $amount = (int)$row['rate'];
}
if ($amount <= 0) {
    $amount = (int)($_POST['amount'] ?? 0);
}
if (!empty($_POST['donate_toggle'])) {
    $donation = max(0, (int)($_POST['donation'] ?? 0));
}
if ($method === '' || $amount <= 0) {
    die('Invalid input.');
}

$totalAmount = $amount + $donation;
$merchantRef = 'INV-' . time() . '-' . rand(100, 999);

$signature = hash_hmac(
    'sha256',
    TRIPAY_MERCHANT_CODE . $merchantRef . $totalAmount,
    TRIPAY_PRIVATE_KEY
);

$orderItems = [[
    'sku'      => $lancerId > 0 ? 'LANCER-' . $lancerId : 'TEST-01',
    'name'     => $lancerName !== '' ? 'Pembayaran untuk ' . $lancerName : 'Item Uji Sandbox',
    'price'    => $amount,
    'quantity' => 1,
]];
if ($donation > 0) {
    $orderItems[] = [
        'sku'      => 'DONATION-FLF',
        'name'     => 'Donasi untuk FLF',
        'price'    => $donation,
        'quantity' => 1,
    ];
}

$payload = [
    'method'         => $method,
    'merchant_ref'   => $merchantRef,
    'amount'         => $totalAmount,
    'customer_name'  => $name,
    'customer_email' => $email,
    'customer_phone' => $phone,
    'order_items'    => $orderItems,
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

// --- Log the transaction for the admin Payment page ---
if (!empty($result['success']) && isset($result['data'])) {
    $d = $result['data'];
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO transactions
             (user_id, lancer_id, lancer_name, merchant_ref, reference,
              amount, donation, total, method, status, checkout_url)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $meId,
            $lancerId ?: null,
            $lancerName,
            $d['merchant_ref'] ?? $merchantRef,
            $d['reference']    ?? '',
            $amount,
            $donation,
            $totalAmount,
            $d['payment_name'] ?? $method,
            $d['status']       ?? 'PENDING',
            $d['checkout_url'] ?? '',
        ]);
    } catch (Throwable $e) {
        // don't block the page if the log insert fails
    }
}
?>
<!doctype html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(t('pi.title')) ?> — FLF</title>
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
    <nav class="nav-links" aria-label="Main">
      <a href="../index.php"><?= e(t('nav.home')) ?></a>
      <a href="../browse.php"><?= e(t('nav.browse')) ?></a>
    </nav>
  </div>
</header>

<main>
  <div class="container">
    <div class="pay-shell">

      <div class="pay-head">
        <h1><?= e(t('pi.title')) ?></h1>
        <?php if ($lancerName !== ''): ?>
          <p class="sub"><?= e(t('pi.paying', ['name' => $lancerName, 'ref' => $merchantRef])) ?></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($error)): ?>

        <div class="pay-result pay-result--error">
          <div class="pay-result__icon">
            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </div>
          <h2><?= e(t('pi.err_curl_t')) ?></h2>
          <p><?= htmlspecialchars($error) ?></p>
        </div>

      <?php elseif (empty($result['success'])): ?>

        <div class="pay-result pay-result--error">
          <div class="pay-result__icon">
            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </div>
          <h2><?= e(t('pi.err_fail_t')) ?></h2>
          <p><?= htmlspecialchars($result['message'] ?? t('pi.err_unknown')) ?></p>
        </div>

      <?php else: $d = $result['data']; ?>

        <ol class="pay-progress">
          <li class="is-done"><?= e(t('pay.step_profile')) ?></li>
          <li class="is-done"><?= e(t('pay.step_payment')) ?></li>
          <li class="is-active"><?= e(t('pay.step_now')) ?></li>
        </ol>

        <div class="pay-amount">
          <span class="label"><?= e(t('pay.amount_due')) ?></span>
          <span class="value">Rp <?= number_format($d['amount'], 0, ',', '.') ?></span>
          <span class="meta">
            <?= htmlspecialchars($d['payment_name']) ?>
            <?php if ($lancerName !== ''): ?> · <?= htmlspecialchars($lancerName) ?><?php endif; ?>
            <?php if ($donation > 0): ?> · <?= e(t('pi.incl_donation', ['amt' => number_format($donation, 0, ',', '.')])) ?><?php endif; ?>
          </span>
        </div>

        <dl class="pay-summary">
          <div class="kv"><dt><?= e(t('pi.sum_status')) ?></dt>
            <dd><span class="pay-status pay-status--<?= strtolower($d['status']) ?>"><?= htmlspecialchars($d['status']) ?></span></dd>
          </div>
          <div class="kv"><dt><?= e(t('pi.sum_ref')) ?></dt>     <dd><?= htmlspecialchars($d['reference']) ?></dd></div>
          <div class="kv"><dt><?= e(t('pi.sum_mref')) ?></dt>    <dd><?= htmlspecialchars($d['merchant_ref']) ?></dd></div>
          <div class="kv"><dt><?= e(t('pi.sum_amount')) ?></dt>  <dd>Rp <?= number_format($amount, 0, ',', '.') ?></dd></div>
          <?php if ($donation > 0): ?>
            <div class="kv"><dt><?= e(t('pi.sum_donation')) ?></dt><dd>Rp <?= number_format($donation, 0, ',', '.') ?></dd></div>
          <?php endif; ?>
          <div class="kv"><dt><?= e(t('pi.sum_fee')) ?></dt>     <dd>Rp <?= number_format($d['fee_merchant'], 0, ',', '.') ?></dd></div>
          <div class="kv"><dt><?= e(t('pi.sum_expires')) ?></dt> <dd><?= date('d M Y H:i', $d['expired_time']) ?></dd></div>
        </dl>

        <?php if (!empty($d['pay_code'])): ?>
          <div class="pay-code">
            <span class="label"><?= e(t('pi.code_lbl')) ?></span>
            <span class="value" id="payCode"><?= htmlspecialchars($d['pay_code']) ?></span>
            <button type="button" class="copy" data-copy="#payCode">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2"/>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
              </svg>
              <?= e(t('pi.copy')) ?>
            </button>
          </div>
        <?php endif; ?>

        <?php if (!empty($d['qr_url']) || !empty($d['qr_string'])): ?>
          <div class="pay-qr">
            <div class="label"><?= e(t('pi.qr_lbl')) ?></div>
            <?php if (!empty($d['qr_url'])): ?>
              <img src="<?= htmlspecialchars($d['qr_url']) ?>" alt="<?= e(t('pi.qr_alt')) ?>">
            <?php endif; ?>
            <?php if (!empty($d['qr_string'])): ?>
              <pre><?= htmlspecialchars($d['qr_string']) ?></pre>
            <?php endif; ?>
          </div>
        <?php endif; ?>

               <?php if (!empty($d['instructions'])): ?>
          <div class="accordion" data-accordion="single">
            <div class="accordion__label"><?= e(t('pi.how_lbl')) ?></div>

            <?php foreach ($d['instructions'] as $i => $grp): ?>
              <div class="accordion__item">
                <h3 class="accordion__header">
                  <button type="button"
                          class="accordion__trigger"
                          aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                    <span><?= htmlspecialchars($grp['title']) ?></span>
                    <svg class="accordion__icon" viewBox="0 0 24 24" aria-hidden="true">
                      <polyline points="6 9 12 15 18 9"/>
                    </svg>
                  </button>
                </h3>

                <div class="accordion__panel" data-open="<?= $i === 0 ? 'true' : 'false' ?>">
                  <div class="accordion__panel-inner">
                    <div class="accordion__body">
                      <ol>
                        <?php foreach ($grp['steps'] as $step): ?>
                          <li><?= $step ?></li>
                        <?php endforeach; ?>
                      </ol>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="pay-actions">
          <a class="btn btn-outline" href="../profile.php?id=<?= (int)$lancerId ?>"><?= e(t('pi.back_profile')) ?></a>
          <?php if (!empty($d['checkout_url'])): ?>
            <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars($d['checkout_url']) ?>" target="_blank" rel="noopener">
              <?= e(t('pi.open_checkout')) ?>
            </a>
          <?php endif; ?>
        </div>

      <?php endif; ?>

    </div>
  </div>
</main>

<script>
document.querySelectorAll('[data-copy]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    var target = document.querySelector(btn.getAttribute('data-copy'));
    if (!target) return;
    navigator.clipboard.writeText(target.textContent.trim()).then(function () {
      var original = btn.innerHTML;
      btn.classList.add('is-copied');
      btn.innerHTML = <?= json_encode(t('pi.copied')) ?>;
      setTimeout(function () {
        btn.classList.remove('is-copied');
        btn.innerHTML = original;
      }, 1600);
    });
  });
});
</script>

</body>
</html>