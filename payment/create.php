<?php
require_once __DIR__ . '/../includes/lang.php';
require 'config.php';

$method     = $_POST['method']      ?? '';
$amount     = (int)($_POST['amount'] ?? 0);
$donation   = 0;
$name       = $_POST['name']        ?? 'Tamu';
$email      = $_POST['email']       ?? 'tamu@example.com';
$phone      = $_POST['phone']       ?? '08123456789';
$lancerId   = (int)($_POST['lancer_id'] ?? 0);
$lancerName = trim($_POST['lancer_name'] ?? '');

// Donasi hanya dihitung kalau checkbox dicentang
if (!empty($_POST['donate_toggle'])) {
    $donation = max(0, (int)($_POST['donation'] ?? 0));
}

if ($method === '' || $amount <= 0) {
    die('Input tidak valid.');
}

$totalAmount = $amount + $donation;

$merchantRef = 'INV-' . time() . '-' . rand(100, 999);

// Signature memakai TOTAL (jumlah + donasi), karena itulah yang ditagih Tripay
$signature = hash_hmac(
    'sha256',
    TRIPAY_MERCHANT_CODE . $merchantRef . $totalAmount,
    TRIPAY_PRIVATE_KEY
);

// --- Susun order items ---
$orderItems = [[
    'sku'      => $lancerId > 0 ? 'LANCER-' . $lancerId : 'TEST-01',
    'name'     => $lancerName !== ''
                    ? 'Pembayaran untuk ' . $lancerName
                    : 'Item Uji Sandbox',
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
?>
<!doctype html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruksi Pembayaran — FLF</title>
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
    <nav class="nav-links" aria-label="Utama">
     <a href="../index.php"><?= e(t('nav.home')) ?></a>
<a href="../browse.php"><?= e(t('nav.browse')) ?></a>
    </nav>
  </div>
</header>

<main>
  <div class="container">
    <div class="pay-shell">

      <div class="pay-head">
        <h1>Instruksi pembayaran</h1>
        <?php if ($lancerName !== ''): ?>
          <p class="sub">
            Membayar <strong><?= htmlspecialchars($lancerName) ?></strong>
            — referensi <code><?= htmlspecialchars($merchantRef) ?></code>
          </p>
        <?php endif; ?>
      </div>

      <?php if (!empty($error)): ?>

        <div class="pay-result pay-result--error">
          <div class="pay-result__icon">
            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </div>
          <h2>Tidak dapat menghubungi Tripay</h2>
          <p><?= htmlspecialchars($error) ?></p>
        </div>

      <?php elseif (empty($result['success'])): ?>

        <div class="pay-result pay-result--error">
          <div class="pay-result__icon">
            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </div>
          <h2>Pembayaran gagal</h2>
          <p><?= htmlspecialchars($result['message'] ?? 'Kesalahan tidak diketahui dari Tripay.') ?></p>
        </div>

      <?php else: $d = $result['data']; ?>

        <ol class="pay-progress">
          <li class="is-done">Profil</li>
          <li class="is-done">Pembayaran</li>
          <li class="is-active">Bayar sekarang</li>
        </ol>

        <!-- Banner jumlah -->
        <div class="pay-amount">
          <span class="label">Total tagihan</span>
          <span class="value">Rp <?= number_format($d['amount'], 0, ',', '.') ?></span>
          <span class="meta">
            <?= htmlspecialchars($d['payment_name']) ?>
            <?php if ($lancerName !== ''): ?>
              · <?= htmlspecialchars($lancerName) ?>
            <?php endif; ?>
            <?php if ($donation > 0): ?>
              · termasuk donasi Rp <?= number_format($donation, 0, ',', '.') ?>
            <?php endif; ?>
          </span>
        </div>

        <!-- Ringkasan -->
        <dl class="pay-summary">
          <div class="kv">
            <dt>Status</dt>
            <dd><span class="pay-status pay-status--<?= strtolower($d['status']) ?>"><?= htmlspecialchars($d['status']) ?></span></dd>
          </div>
          <div class="kv"><dt>Referensi</dt>           <dd><?= htmlspecialchars($d['reference']) ?></dd></div>
          <div class="kv"><dt>Referensi merchant</dt>  <dd><?= htmlspecialchars($d['merchant_ref']) ?></dd></div>
          <div class="kv"><dt>Jumlah</dt>              <dd>Rp <?= number_format($amount, 0, ',', '.') ?></dd></div>
          <?php if ($donation > 0): ?>
            <div class="kv"><dt>Donasi</dt>            <dd>Rp <?= number_format($donation, 0, ',', '.') ?></dd></div>
          <?php endif; ?>
          <div class="kv"><dt>Biaya</dt>               <dd>Rp <?= number_format($d['fee_merchant'], 0, ',', '.') ?></dd></div>
          <div class="kv"><dt>Kedaluwarsa</dt>         <dd><?= date('d M Y H:i', $d['expired_time']) ?></dd></div>
        </dl>

        <?php if (!empty($d['pay_code'])): ?>
          <div class="pay-code">
            <span class="label">Kode pembayaran / VA</span>
            <span class="value" id="payCode"><?= htmlspecialchars($d['pay_code']) ?></span>
            <button type="button" class="copy" data-copy="#payCode">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2"/>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
              </svg>
              Salin kode
            </button>
          </div>
        <?php endif; ?>

        <?php if (!empty($d['qr_url']) || !empty($d['qr_string'])): ?>
          <div class="pay-qr">
            <div class="label">Pindai untuk membayar</div>
            <?php if (!empty($d['qr_url'])): ?>
              <img src="<?= htmlspecialchars($d['qr_url']) ?>" alt="Kode QR pembayaran">
            <?php endif; ?>
            <?php if (!empty($d['qr_string'])): ?>
              <pre><?= htmlspecialchars($d['qr_string']) ?></pre>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($d['instructions'])): ?>
          <div class="pay-steps">
            <div class="label">Cara membayar</div>
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
          <a class="btn btn-outline" href="../profile.php?id=<?= (int)$lancerId ?>">← Kembali ke profil</a>
          <?php if (!empty($d['checkout_url'])): ?>
            <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars($d['checkout_url']) ?>" target="_blank" rel="noopener">
              Buka Checkout Tripay →
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
      btn.innerHTML = '✓ Tersalin';
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