<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require __DIR__ . '/config.php';

// --- Get the Lancer ---
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

// --- Load payment channels ---
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

$pageTitle = 'Pay ' . $lancer['name'] . ' — FLF';
require __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="pay-shell pay-shell--narrow">

    <!-- Progress strip -->
    <ol class="pay-progress">
      <li class="is-done">Profile</li>
      <li class="is-active">Payment</li>
      <li>Done</li>
    </ol>

    <div class="pay-head">
      <h1>Pay this Lancer</h1>
      <p class="sub">
        Secure sandbox payment for <strong><?= e($lancer['name']) ?></strong>.
      </p>
    </div>

    <!-- Amount banner -->
    <div class="pay-amount">
      <span class="label">Amount due</span>
      <span class="value">Rp <?= number_format((float)$lancer['rate'], 0, ',', '.') ?></span>
      <span class="meta">
        <?= e($lancer['name']) ?>
        · <?= e($lancer['title'] ?: 'Lancer') ?>
        <?php if ($lancer['location']): ?>
          · <?= e($lancer['location']) ?>
        <?php endif; ?>
      </span>
    </div>

    <!-- Summary -->
    <dl class="pay-summary">
      <div class="kv"><dt>Lancer</dt><dd><?= e($lancer['name']) ?></dd></div>
      <div class="kv"><dt>Skill</dt><dd><?= e($lancer['skill'] ?: '—') ?></dd></div>
      <div class="kv"><dt>Location</dt><dd><?= e($lancer['location'] ?: '—') ?></dd></div>
      <div class="kv"><dt>Base rate</dt><dd>Rp <?= number_format((float)$lancer['rate'], 0, ',', '.') ?></dd></div>
    </dl>

    <?php if ($error): ?>
      <div class="alert alert-error">cURL error: <?= e($error) ?></div>
    <?php elseif (!$channels): ?>
      <div class="alert alert-error">Could not load payment channels. Check your Tripay API key.</div>
    <?php endif; ?>

    <form method="post" action="create.php">
      <input type="hidden" name="lancer_id"   value="<?= (int)$lancer['user_id'] ?>">
      <input type="hidden" name="lancer_name" value="<?= e($lancer['name']) ?>">

      <div class="field">
        <label for="method">Payment method</label>
        <select class="input" id="method" name="method" required>
          <?php foreach ($channels as $ch): ?>
            <option value="<?= e($ch['code']) ?>">
              <?= e($ch['name']) ?><?= $ch['active'] ? '' : ' (inactive)' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="amount">Amount (IDR)</label>
        <input class="input" id="amount" name="amount" type="number"
               value="<?= (int)$lancer['rate'] ?: 15000 ?>" min="1" required>
        <p class="hint">Pre-filled from the Lancer's rate. Change it if you agreed on a different price.</p>
      </div>

      <div class="field-row">
        <div class="field">
          <label for="name">Your name</label>
          <input class="input" id="name" name="name" type="text"
                 value="<?= e($me['name'] ?? 'Guest') ?>" required>
        </div>
        <div class="field">
          <label for="phone">Phone</label>
          <input class="input" id="phone" name="phone" type="text" value="08123456789">
        </div>
      </div>

      <div class="field">
        <label for="email">Your email</label>
        <input class="input" id="email" name="email" type="email"
               value="<?= e($me['email'] ?? 'guest@example.com') ?>" required>
      </div>

      <div class="pay-actions">
        <a class="btn btn-outline" href="../profile.php?id=<?= (int)$lancer['user_id'] ?>">Cancel</a>
        <button class="btn btn-primary btn-lg" type="submit">Continue to payment →</button>
      </div>
    </form>

  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>