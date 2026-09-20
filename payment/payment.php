<?php
require 'config.php';

// --- Fetch available payment channels from sandbox ---
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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sandbox test payment — FLF</title>
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
      <a href="../index.php">Home</a>
      <a href="../browse.php">Find Lancers</a>
    </nav>
    <div class="nav-actions">
      <a class="btn btn-ghost" href="../index.php">← Back to site</a>
    </div>
  </div>
</header>

<main>
  <div class="container">
    <div class="pay-shell pay-shell--narrow">

      <div class="pay-head">
        <h1>Sandbox test payment</h1>
        <p class="sub">
          Use this page to test Tripay without going through a Lancer profile.
        </p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error">cURL error: <?= htmlspecialchars($error) ?></div>
      <?php elseif (empty($channels)): ?>
        <div class="alert alert-error">
          Could not load payment channels. Check your API key / merchant code.
        </div>
      <?php endif; ?>

      <form method="post" action="create.php">

        <div class="field">
          <label for="method">Payment method</label>
          <select class="input" id="method" name="method" required>
            <?php foreach ($channels as $ch): ?>
              <option value="<?= htmlspecialchars($ch['code']) ?>">
                <?= htmlspecialchars($ch['name']) ?><?= $ch['active'] ? '' : ' (inactive)' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="amount">Amount (IDR)</label>
          <input class="input" id="amount" name="amount" type="number"
                 value="15000" min="1" required>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="name">Customer name</label>
            <input class="input" id="name" name="name" type="text"
                   value="Test User" required>
          </div>
          <div class="field">
            <label for="phone">Phone</label>
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
            Create sandbox transaction →
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
      <a href="../index.php">Home</a>
      <a href="../browse.php">Find Lancers</a>
    </p>
  </div>
</footer>

</body>
</html>