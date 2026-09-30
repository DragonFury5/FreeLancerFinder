<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/earnings.php';
require_once __DIR__ . '/includes/tripay.php';

require_login();

$me = current_user();
if (($me['role'] ?? '') !== 'lancer') {
    header('Location: browse.php');
    exit;
}

$lancerId = (int)$me['id'];

// --- Load Lancer ---
$stmt = $pdo->prepare("SELECT * FROM lancers WHERE user_id = ?");
$stmt->execute([$lancerId]);
$lancer = $stmt->fetch();
if (!$lancer) { header('Location: browse.php'); exit; }

// --- Balance ---
$balance   = compute_lancer_balance($pdo, $lancerId);
$available = $balance['available'];

$fee = (int)TRIPAY_DISBURSEMENT_FEE;
$min = (int)TRIPAY_DISBURSEMENT_MIN;

$errors = [];
$old = [
    'amount'            => '',
    'lancer_covers_fee' => 0,
    'bank'              => $lancer['payout_bank'],
    'account'           => $lancer['payout_account'],
    'account_name'      => $lancer['payout_name'],
];

// --- Handle POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount       = (int)str_replace('.', '', $_POST['amount'] ?? '0');
    $lancerCovers = !empty($_POST['lancer_covers_fee']) ? 1 : 0;
    $flfCovers    = $lancerCovers ? 0 : 1;   // DB column: 1 = FLF pays, 0 = Lancer pays
    $bank         = strtoupper(trim($_POST['bank'] ?? ''));
    $acc          = trim($_POST['account'] ?? '');
    $accName      = trim($_POST['account_name'] ?? '');

    $old = [
        'amount'            => $amount,
        'lancer_covers_fee' => $lancerCovers,
        'bank'              => $bank,
        'account'           => $acc,
        'account_name'      => $accName,
    ];

    $deduction = $amount + ($lancerCovers ? $fee : 0);

    if ($bank === '' || $acc === '' || $accName === '') {
        $errors['payout'] = t('wd.err_payout');
    }
    if ($amount < $min) {
        $errors['amount'] = t('wd.err_min', ['min' => 'Rp ' . number_format($min, 0, ',', '.')]);
    }
    if ($deduction > $available) {
        $errors['amount'] = t('wd.err_balance');
    }

    if (!$errors) {
        // Save payout details if changed
        if ($bank !== $lancer['payout_bank']
            || $acc !== $lancer['payout_account']
            || $accName !== $lancer['payout_name']) {
            $upd = $pdo->prepare(
                "UPDATE lancers SET payout_bank=?, payout_account=?, payout_name=? WHERE user_id=?"
            );
            $upd->execute([$bank, $acc, $accName, $lancerId]);
        }

        // Insert withdrawal with a fresh receipt id
        $receiptId = generate_receipt_id($pdo);

        $ins = $pdo->prepare(
            "INSERT INTO withdrawals
             (receipt_id, lancer_id, amount, fee, flf_covers_fee, bank, account, account_name, status, note)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDING', '')"
        );
        $ins->execute([$receiptId, $lancerId, $amount, $fee, $flfCovers, $bank, $acc, $accName]);
        $wid = (int)$pdo->lastInsertId();

        // Fire Tripay (or simulate)
        $apiOk  = false;
        $apiRef = '';
        $apiErr = '';

        if (TRIPAY_DISBURSEMENT_ENABLED) {
            $result = tripay_disbursement_create([
                'amount'       => $amount,
                'bank'         => $bank,
                'account'      => $acc,
                'account_name' => $accName,
                'note'         => 'FLF withdrawal ' . $receiptId,
            ]);
            $apiRef = $result['merchant_ref'] ?? '';
            if (!empty($result['ok'])) {
                $apiOk = true;
            } else {
                $apiErr = $result['error'] ?? 'Unknown error';
            }
        } else {
            $apiOk  = true;
            $apiRef = 'SIMULATED-' . $wid;
        }

        if ($apiOk) {
            $upd = $pdo->prepare(
                "UPDATE withdrawals
                 SET status='SENT', tripay_ref=?, processed_at=NOW()
                 WHERE id=?"
            );
            $upd->execute([$apiRef, $wid]);
            header('Location: earnings.php?withdrawn=' . urlencode($receiptId));
            exit;
        } else {
            $upd = $pdo->prepare(
                "UPDATE withdrawals
                 SET status='REJECTED', note=?, processed_at=NOW()
                 WHERE id=?"
            );
            $upd->execute([substr($apiErr, 0, 250), $wid]);
            header('Location: earnings.php?withdraw_failed=' . urlencode(substr($apiErr, 0, 120)));
            exit;
        }
    }
}

$pageTitle = t('wd.title') . ' — FLF';
require __DIR__ . '/includes/header.php';

$minFormatted = 'Rp ' . number_format($min, 0, ',', '.');
$feeFormatted = 'Rp ' . number_format($fee, 0, ',', '.');
?>

<div class="container">
  <div class="pay-shell pay-shell--narrow">

    <div class="pay-head">
      <h1><?= e(t('wd.title')) ?></h1>
      <p class="sub"><?= e(t('wd.sub')) ?></p>
    </div>

    <div class="earn-stats" style="margin-top:0">
      <div class="earn-stat earn-stat--money">
        <span class="earn-stat__label"><?= e(t('wd.available')) ?></span>
        <span class="earn-stat__value">Rp <?= number_format($available, 0, ',', '.') ?></span>
      </div>
      <?php if ($balance['held'] > 0): ?>
        <div class="earn-stat earn-stat--pending">
          <span class="earn-stat__label"><?= e(t('wd.held')) ?></span>
          <span class="earn-stat__value">Rp <?= number_format($balance['held'], 0, ',', '.') ?></span>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($available < $min): ?>
      <div class="alert alert-error" style="margin-top:20px">
        <?= e(t('wd.err_too_small', ['min' => $minFormatted])) ?>
      </div>
      <div class="pay-actions">
        <a class="btn btn-outline" href="earnings.php">← <?= e(t('nav.earnings')) ?></a>
      </div>

    <?php else: ?>

      <?php if (!empty($errors['payout'])): ?>
        <div class="alert alert-error" style="margin-top:20px"><?= e($errors['payout']) ?></div>
      <?php endif; ?>

      <form method="post" class="wd-form" style="margin-top:24px">

        <h2 style="font-size:1.05rem;margin-bottom:6px"><?= e(t('wd.payout_title')) ?></h2>
        <p class="hint" style="margin-bottom:16px"><?= e(t('wd.payout_hint')) ?></p>

        <div class="field-row">
          <div class="field">
            <label for="bank"><?= e(t('wd.bank')) ?></label>
            <input class="input" id="bank" name="bank" type="text"
                   placeholder="<?= e(t('wd.bank_ph')) ?>"
                   value="<?= e($old['bank']) ?>" required>
          </div>
          <div class="field">
            <label for="account"><?= e(t('wd.account')) ?></label>
            <input class="input" id="account" name="account"
                   type="tel" inputmode="numeric" pattern="[0-9]+"
                   value="<?= e($old['account']) ?>" required>
          </div>
        </div>

        <div class="field">
          <label for="account_name"><?= e(t('wd.account_name')) ?></label>
          <input class="input" id="account_name" name="account_name" type="text"
                 value="<?= e($old['account_name']) ?>" required>
        </div>

        <hr style="border:0;border-top:1px solid var(--line);margin:22px 0">

        <div class="field">
          <label for="amount"><?= e(t('wd.amount_lbl')) ?></label>
          <input class="input" id="amount" name="amount" type="text"
                 data-thousands inputmode="numeric"
                 value="<?= e((string)$old['amount']) ?>" required>
          <p class="hint"><?= e(t('wd.amount_hint', ['min' => $minFormatted])) ?></p>
          <?php if (!empty($errors['amount'])): ?>
            <div class="err"><?= e($errors['amount']) ?></div>
          <?php endif; ?>
        </div>

        <div class="pay-donation">
          <label class="pay-donation__toggle" for="lancer_covers_fee">
            <input type="checkbox" id="lancer_covers_fee" name="lancer_covers_fee" value="1"
                   <?= !empty($old['lancer_covers_fee']) ? 'checked' : '' ?>>
            <span>
              <?= e(t('wd.cover_fee', ['fee' => $feeFormatted])) ?>
              <small><?= e(t('wd.cover_fee_s')) ?></small>
            </span>
          </label>
        </div>

        <dl class="pay-total" style="flex-direction:column;align-items:stretch;gap:8px">
          <div style="display:flex;justify-content:space-between">
            <dt><?= e(t('wd.you_receive')) ?></dt>
            <dd id="wdReceive">Rp 0</dd>
          </div>
          <div style="display:flex;justify-content:space-between">
            <dt><?= e(t('wd.fee')) ?></dt>
            <dd id="wdFee">—</dd>
          </div>
          <div style="display:flex;justify-content:space-between;padding-top:10px;border-top:1px solid var(--line)">
            <dt><?= e(t('wd.deducted')) ?></dt>
            <dd id="wdDeducted">Rp 0</dd>
          </div>
        </dl>

        <div class="pay-actions">
          <a class="btn btn-outline" href="earnings.php"><?= e(t('common.cancel')) ?></a>
          <button class="btn btn-primary btn-lg" type="submit"><?= e(t('wd.submit')) ?></button>
        </div>

      </form>

    <?php endif; ?>

  </div>
</div>

<script>
(function () {
  var fee    = <?= (int)$fee ?>;
  var amtEl  = document.getElementById('amount');
  var cbEl   = document.getElementById('lancer_covers_fee');
  var recv   = document.getElementById('wdReceive');
  var feeEl  = document.getElementById('wdFee');
  var ded    = document.getElementById('wdDeducted');
  var locale = <?= json_encode(current_lang() === 'id' ? 'id-ID' : 'en-US') ?>;

  function rp(n){ return 'Rp ' + (n || 0).toLocaleString(locale); }

  function calc() {
    var a = parseInt((amtEl.value || '0').replace(/\D/g, ''), 10) || 0;
    var covers = cbEl.checked;   // checked = Lancer pays the fee
    var deduction = a + (covers ? fee : 0);

    recv.textContent = rp(a);
    ded.textContent  = rp(deduction);

    if (covers) {
      feeEl.textContent = rp(fee) + ' (<?= e(t('wd.fee_you')) ?>)';
    } else {
      feeEl.textContent = rp(fee) + ' (<?= e(t('wd.fee_flf')) ?>)';
    }
  }

  amtEl.addEventListener('input', calc);
  cbEl.addEventListener('change', calc);
  calc();
})();
</script>

<?php require __DIR__ . '/includes/footer.php';