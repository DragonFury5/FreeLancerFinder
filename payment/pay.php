<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/lang.php';
require __DIR__ . '/config.php';

// --- Ambil data Lancer ---
$lancerId = (int)($_GET['lancer_id'] ?? 0);
if ($lancerId <= 0) {
    exit('Missing lancer_id.');
}

$stmt = $pdo->prepare(
    "SELECT l.*, u.name, u.email
     FROM lancers l
     JOIN users u ON u.id = l.user_id
     WHERE l.user_id = ?"
);
$stmt->execute([$lancerId]);
$lancer = $stmt->fetch();

if (!$lancer) {
    exit('Lancer not found.');
}

// --- Ambil channel pembayaran ---
$channels = [];
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_FRESH_CONNECT  => true,
    CURLOPT_URL            => TRIPAY_BASE_URL . '/merchant/payment-channel',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . TRIPAY_API_KEY],
    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
]);
$response = curl_exec($curl);
$error    = curl_error($curl);
curl_close($curl);

if (empty($error)) {
    $decoded = json_decode($response, true);
    if (!empty($decoded['success'])) {
        $channels = $decoded['data'];
    }
}

$me = current_user();

$pageTitle = t('pay.title') . ' ' . $lancer['name'] . ' — FLF';
require __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="pay-shell pay-shell--narrow">

    <ol class="pay-progress">
      <li class="is-done"><?= e(t('pay.step_profile')) ?></li>
      <li class="is-active"><?= e(t('pay.step_payment')) ?></li>
      <li><?= e(t('pay.step_done')) ?></li>
    </ol>

    <div class="pay-head">
      <h1><?= e(t('pay.title')) ?></h1>
      <p class="sub"><?= e(t('pay.sub', ['name' => $lancer['name']])) ?></p>
    </div>

    <div class="pay-amount">
      <span class="label"><?= e(t('pay.amount_due')) ?></span>
      <span class="value">Rp <?= number_format((float)$lancer['rate'], 0, ',', '.') ?></span>
      <span class="meta">
        <?= e($lancer['name']) ?>
        · <?= e($lancer['title'] ?: 'Lancer') ?>
        <?php if ($lancer['location']): ?>
          · <?= e($lancer['location']) ?>
        <?php endif; ?>
      </span>
    </div>

    <dl class="pay-summary">
      <div class="kv"><dt><?= e(t('pay.sum_lancer')) ?></dt><dd><?= e($lancer['name']) ?></dd></div>
      <div class="kv"><dt><?= e(t('pay.sum_skill')) ?></dt><dd><?= e($lancer['skill'] ?: '—') ?></dd></div>
      <div class="kv"><dt><?= e(t('pay.sum_location')) ?></dt><dd><?= e($lancer['location'] ?: '—') ?></dd></div>
      <div class="kv"><dt><?= e(t('pay.sum_rate')) ?></dt><dd>Rp <?= number_format((float)$lancer['rate'], 0, ',', '.') ?></dd></div>
    </dl>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e(t('pay.err_curl', ['msg' => $error])) ?></div>
    <?php elseif (!$channels): ?>
      <div class="alert alert-error"><?= e(t('pay.err_channels')) ?></div>
    <?php endif; ?>

    <form method="post" action="create.php" id="payForm">
      <input type="hidden" name="lancer_id"   value="<?= (int)$lancer['user_id'] ?>">
      <input type="hidden" name="lancer_name" value="<?= e($lancer['name']) ?>">

      <div class="field">
        <label for="method"><?= e(t('pay.method')) ?></label>
        <select class="input" id="method" name="method" required>
          <?php foreach ($channels as $ch): ?>
            <option value="<?= e($ch['code']) ?>">
              <?= e($ch['name']) ?><?= $ch['active'] ? '' : ' ' . e(t('pay.inactive')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="amount"><?= e(t('pay.amount_lbl')) ?></label>
        <input class="input" id="amount" name="amount" type="number"
               value="<?= (int)$lancer['rate'] ?: 15000 ?>" min="1" required>
        <p class="hint"><?= e(t('pay.amount_hint')) ?></p>
      </div>

      <div class="pay-donation">
        <label class="pay-donation__toggle" for="donate_toggle">
          <input type="checkbox" id="donate_toggle" name="donate_toggle" value="1">
          <span>
            <?= e(t('pay.donate_t')) ?>
            <small><?= e(t('pay.donate_s')) ?></small>
          </span>
        </label>

        <div class="pay-donation__amount" id="donationBox" hidden>
          <div class="field" style="margin:0">
            <label for="donation"><?= e(t('pay.donate_lbl')) ?></label>
            <input class="input" id="donation" name="donation" type="number"
                   value="2000" min="0" step="500">
            <p class="hint"><?= e(t('pay.donate_hint')) ?></p>
          </div>
        </div>
      </div>

      <dl class="pay-total">
        <dt><?= e(t('pay.total')) ?></dt>
        <dd id="totalDisplay">Rp <?= number_format((float)$lancer['rate'] ?: 15000, 0, ',', '.') ?></dd>
      </dl>

      <div class="field-row">
        <div class="field">
          <label for="name"><?= e(t('pay.your_name')) ?></label>
          <input class="input" id="name" name="name" type="text"
                 value="<?= e($me['name'] ?? t('pay.guest')) ?>" required>
        </div>
        <div class="field">
          <label for="phone"><?= e(t('pay.phone')) ?></label>
          <input class="input" id="phone" name="phone" type="text" value="08123456789">
        </div>
      </div>

      <div class="field">
        <label for="email"><?= e(t('pay.your_email')) ?></label>
        <input class="input" id="email" name="email" type="email"
               value="<?= e($me['email'] ?? 'guest@example.com') ?>" required>
      </div>

      <div class="pay-actions">
        <a class="btn btn-outline" href="../profile.php?id=<?= (int)$lancer['user_id'] ?>">
          <?= e(t('common.cancel')) ?>
        </a>
        <button class="btn btn-primary btn-lg" type="submit"><?= e(t('pay.continue')) ?></button>
      </div>
    </form>

  </div>
</div>

<script>
(function () {
  var toggle    = document.getElementById('donate_toggle');
  var box       = document.getElementById('donationBox');
  var donation  = document.getElementById('donation');
  var amount    = document.getElementById('amount');
  var totalEl   = document.getElementById('totalDisplay');
  var locale    = <?= json_encode(current_lang() === 'id' ? 'id-ID' : 'en-US') ?>;

  function rupiah(n) { return 'Rp ' + (n || 0).toLocaleString(locale); }

  function recalc() {
    var base = parseInt(amount.value || '0', 10) || 0;
    var don  = (toggle.checked) ? (parseInt(donation.value || '0', 10) || 0) : 0;
    totalEl.textContent = rupiah(base + don);
  }

  toggle.addEventListener('change', function () {
    box.hidden = !toggle.checked;
    if (toggle.checked) donation.focus();
    recalc();
  });

  amount.addEventListener('input', recalc);
  donation.addEventListener('input', recalc);
  recalc();
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>