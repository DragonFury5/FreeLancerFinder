<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/lang.php';
require __DIR__ . '/config.php';

// --- Fetch payment channels ---
$channels = [];
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

if (empty($error)) {
    $decoded = json_decode($response, true);
    if (!empty($decoded['success'])) $channels = $decoded['data'];
}

$pageTitle = t('pt.title') . ' — FLF';
require __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="pay-shell pay-shell--narrow">

    <div class="pay-head">
      <h1><?= e(t('pt.title')) ?></h1>
      <p class="sub"><?= e(t('pt.sub')) ?></p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= e(t('pay.err_curl', ['msg' => $error])) ?></div>
    <?php elseif (empty($channels)): ?>
      <div class="alert alert-error"><?= e(t('pay.err_channels')) ?></div>
    <?php endif; ?>

    <form method="post" action="create.php">

      <div class="field">
        <label for="method"><?= e(t('pt.method')) ?></label>
        <select class="input" id="method" name="method" required>
          <?php foreach ($channels as $ch): ?>
            <option value="<?= e($ch['code']) ?>">
              <?= e($ch['name']) ?><?= $ch['active'] ? '' : ' ' . e(t('pay.inactive')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Amount is only shown on the sandbox test page; the real flow uses the Lancer's rate -->
      <div class="field">
        <label for="amount"><?= e(t('pt.amount')) ?></label>
        <input class="input" id="amount" name="amount" type="number" value="15000" min="1" required>
      </div>

      <div class="field-row">
        <div class="field">
          <label for="name"><?= e(t('pt.cust_name')) ?></label>
          <input class="input" id="name" name="name" type="text"
                 value="<?= e(t('pt.cust_default')) ?>" required>
        </div>
        <div class="field">
          <label for="phone"><?= e(t('pt.phone')) ?></label>
          <input class="input" id="phone" name="phone" type="text" value="08123456789">
        </div>
      </div>

      <div class="field">
        <label for="email"><?= e(t('pt.email')) ?></label>
        <input class="input" id="email" name="email" type="email"
               value="test@example.com" required>
      </div>

      <div class="pay-actions">
        <button class="btn btn-primary btn-lg" type="submit"><?= e(t('pt.submit')) ?></button>
      </div>

    </form>

  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>