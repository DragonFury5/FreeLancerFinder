<?php
require_once __DIR__ . '/../includes/lang.php';
require 'config.php';

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_FRESH_CONNECT  => true,
    CURLOPT_URL            => TRIPAY_BASE_URL . '/merchant/payment-channel',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => false,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . TRIPAY_API_KEY],
    CURLOPT_FAILONERROR    => false,
    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
]);
$response = curl_exec($curl);
$error    = curl_error($curl);
curl_close($curl);

$channels = [];
if (empty($error)) {
    $decoded = json_decode($response, true);
    if (!empty($decoded['success'])) {
        $channels = $decoded['data'];
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Uji Sandbox — FLF</title>
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
      <a href="../index.php">Beranda</a>
      <a href="../browse.php">Cari Lancer</a>
    </nav>
    <div class="nav-actions">
      <a class="btn btn-ghost" href="../index.php">← Kembali ke situs</a>
    </div>
  </div>
</header>

<main>
  <div class="container">
    <div class="pay-shell pay-shell--narrow">

      <div class="pay-head">
        <h1>Pembayaran uji sandbox</h1>
        <p class="sub">
          Gunakan halaman ini untuk menguji Tripay tanpa melalui profil Lancer.
        </p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error">Kesalahan cURL: <?= htmlspecialchars($error) ?></div>
      <?php elseif (empty($channels)): ?>
        <div class="alert alert-error">
          Gagal memuat metode pembayaran. Periksa API key / merchant code Anda.
        </div>
      <?php endif; ?>

      <form method="post" action="create.php">

        <div class="field">
          <label for="method">Metode pembayaran</label>
          <select class="input" id="method" name="method" required>
            <?php foreach ($channels as $ch): ?>
              <option value="<?= htmlspecialchars($ch['code']) ?>">
                <?= htmlspecialchars($ch['name']) ?><?= $ch['active'] ? '' : ' (tidak aktif)' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="amount">Jumlah (IDR)</label>
          <input class="input" id="amount" name="amount" type="number"
                 value="15000" min="1" required>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="name">Nama pelanggan</label>
            <input class="input" id="name" name="name" type="text"
                   value="Pelanggan Uji" required>
          </div>
          <div class="field">
            <label for="phone">Nomor telepon</label>
            <input class="input" id="phone" name="phone" type="text"
                   value="08123456789">
          </div>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input class="input" id="email" name="email" type="email"
                 value="test@example.com" required>
        </div>

        <div class="pay-actions">
          <button class="btn btn-primary btn-lg" type="submit">
            Buat transaksi sandbox →
          </button>
        </div>

      </form>

    </div>
  </div>
</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <p>© <?= date('Y') ?> FLF — FreeLanceFinder.</p>
    <p class="footer-links">
      <a href="../index.php">Beranda</a>
      <a href="../browse.php">Cari Lancer</a>
    </p>
  </div>
</footer>

</body>
</html>