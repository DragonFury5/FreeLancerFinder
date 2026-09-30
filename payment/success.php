<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/lang.php';

$txId   = (int)($_GET['tx'] ?? 0);
$errors = [];
$rated  = false;

// --- Handle rating submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $txId > 0) {
    $stars = (int)($_POST['stars'] ?? 0);

    if ($stars < 1 || $stars > 5) {
        $errors['stars'] = t('succ.rate_error');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
        $stmt->execute([$txId]);
        $tx = $stmt->fetch();

        if ($tx && !$tx['rating']) {
            try {
                $pdo->beginTransaction();

                // 1. Save rating on the transaction
                $stmt = $pdo->prepare(
                    "UPDATE transactions
                     SET rating = ?, rated_at = NOW(), status = 'PAID'
                     WHERE id = ?"
                );
                $stmt->execute([$stars, $txId]);

                // 2. Recalculate the Lancer's average from all rated transactions
                if (!empty($tx['lancer_id'])) {
                    $avg = $pdo->prepare(
                        "SELECT AVG(rating) AS avg_r, COUNT(*) AS cnt
                         FROM transactions
                         WHERE lancer_id = ? AND rating IS NOT NULL"
                    );
                    $avg->execute([$tx['lancer_id']]);
                    $row = $avg->fetch();

                    if ($row && (int)$row['cnt'] > 0) {
                        $newAvg = round((float)$row['avg_r'], 1);
                        $upd = $pdo->prepare("UPDATE lancers SET rating = ? WHERE user_id = ?");
                        $upd->execute([$newAvg, $tx['lancer_id']]);
                    }
                }

                $pdo->commit();
                $rated = true;
            } catch (Throwable $e) {
                $pdo->rollBack();
                $errors['general'] = t('succ.rate_error');
            }
        }
    }
}

// --- Load transaction for display ---
$tx = null;
if ($txId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->execute([$txId]);
    $tx = $stmt->fetch();
}
// Sandbox: landing on success.php means the payment is confirmed.
// Flip status → PAID and set the 3-day hold timer.
if ($tx && !in_array(strtoupper($tx['status'] ?? ''), ['PAID','SUCCESS','SETTLED'], true)) {
    try {
        $upd = $pdo->prepare(
            "UPDATE transactions
             SET status = 'PAID',
                 available_at = DATE_ADD(NOW(), INTERVAL 3 DAY)
             WHERE id = ?"
        );
        $upd->execute([$txId]);
        $tx['status'] = 'PAID';
        $tx['available_at'] = date('Y-m-d H:i:s', strtotime('+3 days'));
    } catch (Throwable $e) {}
}

$pageTitle = t('succ.title') . ' — FLF';
require __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="pay-shell pay-shell--narrow">

    <div class="success-box">

      <div class="success-icon">
        <svg viewBox="0 0 24 24" aria-hidden="true">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>

      <h1 class="success-title"><?= e(t('succ.title')) ?></h1>
      <p class="success-thanks"><?= e(t('succ.thanks')) ?></p>

      <?php if ($tx): ?>

        <dl class="success-details">
          <div class="kv">
            <dt><?= e(t('succ.paid_to')) ?></dt>
            <dd><?= e($tx['lancer_name'] ?: '—') ?></dd>
          </div>
          <div class="kv">
            <dt><?= e(t('pi.sum_amount')) ?></dt>
            <dd>Rp <?= number_format((float)$tx['amount'], 0, ',', '.') ?></dd>
          </div>
          <?php if ((int)$tx['donation'] > 0): ?>
            <div class="kv">
              <dt><?= e(t('pi.sum_donation')) ?></dt>
              <dd>Rp <?= number_format((float)$tx['donation'], 0, ',', '.') ?></dd>
            </div>
          <?php endif; ?>
          <div class="kv kv--total">
            <dt><?= e(t('succ.total')) ?></dt>
            <dd>Rp <?= number_format((float)$tx['total'], 0, ',', '.') ?></dd>
          </div>
        </dl>

        <div class="success-rating">
          <?php if ($rated || $tx['rating']): ?>
            <p class="success-rating__thanks"><?= e(t('succ.rate_thanks')) ?></p>
            <div class="success-stars">
              <?php $stars = (int)($tx['rating'] ?? ($_POST['stars'] ?? 0)); ?>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <svg class="star <?= $i <= $stars ? 'is-on' : '' ?>"
                     viewBox="0 0 24 24" aria-hidden="true">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"/>
                </svg>
              <?php endfor; ?>
            </div>
          <?php else: ?>
            <h3><?= e(t('succ.rate_title')) ?></h3>
            <p class="hint"><?= e(t('succ.rate_hint')) ?></p>

            <form method="post">
              <div class="rating-stars" role="radiogroup"
                   aria-label="<?= e(t('succ.rate_title')) ?>">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <label class="rating-star" title="<?= $i ?> / 5">
                    <input type="radio" name="stars" value="<?= $i ?>"
                           <?= $i === 5 ? '' : '' ?> required>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"/>
                    </svg>
                  </label>
                <?php endfor; ?>
              </div>

              <button class="btn btn-primary btn-block" type="submit">
                <?= e(t('succ.rate_submit')) ?>
              </button>

              <?php if (!empty($errors['general'])): ?>
                <div class="err" style="margin-top:12px"><?= e($errors['general']) ?></div>
              <?php endif; ?>
            </form>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <p class="hint"><?= e(t('succ.not_found')) ?></p>
      <?php endif; ?>

      <div class="success-actions">
        <a class="btn btn-outline" href="../browse.php"><?= e(t('succ.back_browse')) ?></a>
        <a class="btn btn-primary"   href="../profile.php"><?= e(t('nav.profile')) ?></a>
      </div>

    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>