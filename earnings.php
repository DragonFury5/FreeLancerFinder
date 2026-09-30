<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/payment/config.php';     
require_once __DIR__ . '/includes/earnings.php';

require_login();

$me = current_user();
if (($me['role'] ?? '') !== 'lancer') {
    header('Location: browse.php');
    exit;
}

$lancerId = (int)$me['id'];
$balance  = compute_lancer_balance($pdo, $lancerId);
$min      = (int)TRIPAY_DISBURSEMENT_MIN;
$minFmt   = 'Rp ' . number_format($min, 0, ',', '.');

// --- Filters ---
$q   = trim($_GET['q'] ?? '');
$flt = trim($_GET['filter'] ?? '');
if (!in_array($flt, ['', 'paid', 'unpaid'], true)) $flt = '';

// --- Transactions query ---
$sql    = "SELECT * FROM transactions WHERE lancer_id = ?";
$params = [$lancerId];
$where  = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    $where[] = "(customer_name LIKE ? OR reference LIKE ? OR merchant_ref LIKE ?)";
    array_push($params, $like, $like, $like);
}
if ($flt === 'paid') {
    $where[] = "UPPER(status) IN ('PAID','SUCCESS','SETTLED')";
} elseif ($flt === 'unpaid') {
    $where[] = "UPPER(status) NOT IN ('PAID','SUCCESS','SETTLED','REFUNDED')";
}
if ($where) $sql .= ' AND ' . implode(' AND ', $where);
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// --- Withdrawals history ---
$wdStmt = $pdo->prepare(
    "SELECT * FROM withdrawals WHERE lancer_id = ? ORDER BY requested_at DESC"
);
$wdStmt->execute([$lancerId]);
$withdrawals = $wdStmt->fetchAll();

// --- Banners ---
$bannerFailed = $_GET['withdraw_failed'] ?? '';
$bannerSent   = !empty($_GET['withdrawn']);

$pageTitle = t('earn.title') . ' — FLF';
require __DIR__ . '/includes/header.php';

function rp_local($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
?>

<div class="container">

  <?php if ($bannerSent): ?>
    <div class="alert alert-success" style="margin-top:24px">
      <?= e(t('earn.banner_sent')) ?>
    </div>
  <?php endif; ?>
  <?php if ($bannerFailed !== ''): ?>
    <div class="alert alert-error" style="margin-top:24px">
      <?= e(t('earn.banner_failed', ['msg' => $bannerFailed])) ?>
    </div>
  <?php endif; ?>

  <div class="admin-head">
    <h1><?= e(t('earn.title')) ?></h1>
    <p><?= e(t('earn.sub')) ?></p>
  </div>

  <!-- 4 stat cards -->
  <div class="earn-stats">
    <div class="earn-stat earn-stat--accent">
      <span class="earn-stat__label"><?= e(t('earn.stat_total')) ?></span>
      <span class="earn-stat__value"><?= rp_local($balance['grand_total']) ?></span>
    </div>
    <div class="earn-stat earn-stat--money">
      <span class="earn-stat__label"><?= e(t('earn.stat_available')) ?></span>
      <span class="earn-stat__value"><?= rp_local($balance['available']) ?></span>
    </div>
    <div class="earn-stat earn-stat--pending">
      <span class="earn-stat__label"><?= e(t('earn.stat_held')) ?></span>
      <span class="earn-stat__value"><?= rp_local($balance['held']) ?></span>
    </div>
    <div class="earn-stat">
      <span class="earn-stat__label"><?= e(t('earn.stat_withdrawn')) ?></span>
      <span class="earn-stat__value"><?= rp_local($balance['withdrawn']) ?></span>
    </div>
  </div>

  <!-- Withdraw CTA -->
  <?php if ($balance['available'] >= $min): ?>
    <div style="margin:24px 0 12px">
      <a class="btn btn-primary btn-lg" href="withdraw.php">
        <?= e(t('earn.withdraw_btn')) ?> → <?= rp_local($balance['available']) ?>
      </a>
    </div>
  <?php else: ?>
    <p class="result-meta" style="text-align:left;margin:20px 0 8px">
      <?= e(t('earn.min_hint', ['min' => $minFmt])) ?>
    </p>
  <?php endif; ?>

  <!-- Search + filter -->
  <form class="admin-search" method="get" action="earnings.php" role="search">
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <circle cx="11" cy="11" r="7"/>
      <line x1="20" y1="20" x2="16.65" y2="16.65"/>
    </svg>
    <input type="search" name="q"
           placeholder="<?= e(t('earn.search_ph')) ?>"
           aria-label="<?= e(t('earn.search_ph')) ?>"
           value="<?= e($q) ?>">
    <input type="hidden" name="filter" value="<?= e($flt) ?>">
  </form>

  <div class="earn-pills">
    <?php
      $pills = [
        ['',       t('earn.filter_all')],
        ['paid',   t('earn.filter_paid')],
        ['unpaid', t('earn.filter_unpaid')],
      ];
    ?>
    <?php foreach ($pills as [$key, $label]): ?>
      <?php
        $qs = http_build_query(array_filter(['q'=>$q,'filter'=>$key], fn($v) => $v !== ''));
        $href = 'earnings.php' . ($qs ? '?' . $qs : '');
      ?>
      <a class="earn-pill <?= $flt === $key ? 'is-active' : '' ?>" href="<?= e($href) ?>">
        <?= e($label) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <p class="result-meta" style="text-align:left">
    <?php if ($q !== '' || $flt !== ''): ?>
      <?php
        $pieces = [];
        if ($q !== '')   $pieces[] = t('earn.results_for', ['count'=>count($rows),'q'=>$q]);
        if ($flt !== '') $pieces[] = $flt === 'paid' ? t('earn.filter_paid') : t('earn.filter_unpaid');
        echo e(implode(' · ', $pieces));
      ?>
      &middot; <a href="earnings.php"><?= e(t('earn.clear')) ?></a>
    <?php else: ?>
      <?= e(t('earn.showing_all', ['count' => count($rows)])) ?>
    <?php endif; ?>
  </p>

  <?php if (!$rows): ?>
    <div class="admin-empty">
      <strong><?= e(t('earn.empty_t')) ?></strong>
      <p><?= e(t('earn.empty_s')) ?></p>
    </div>
  <?php else: ?>

    <div class="earn-table-wrap">
      <table class="earn-table">
        <thead>
          <tr>
            <th><?= e(t('earn.table_date')) ?></th>
            <th><?= e(t('earn.table_customer')) ?></th>
            <th class="num"><?= e(t('earn.table_amount')) ?></th>
            <th><?= e(t('earn.table_status')) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $t): ?>
            <?php $st = earnings_status($t['status'] ?? ''); $cls = strtolower($st); ?>
            <tr>
              <td><?= date('M j, Y H:i', strtotime($t['created_at'])) ?></td>
              <td class="name"><?= e($t['customer_name'] !== '' ? $t['customer_name'] : t('earn.guest')) ?></td>
              <td class="num"><strong><?= rp_local($t['total']) ?></strong></td>
              <td><span class="earn-badge earn-badge--<?= $cls ?>">
                <?= e($st === 'PAID' ? t('earn.filter_paid') : ($st === 'REFUNDED' ? $st : t('earn.filter_unpaid'))) ?>
              </span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="earn-cards">
      <?php foreach ($rows as $t): ?>
        <?php $st = earnings_status($t['status'] ?? ''); $cls = strtolower($st); ?>
        <div class="earn-card-item">
          <div class="earn-card-item__top">
            <span class="earn-card-item__name">
              <?= e($t['customer_name'] !== '' ? $t['customer_name'] : t('earn.guest')) ?>
            </span>
            <span class="earn-badge earn-badge--<?= $cls ?>">
              <?= e($st === 'PAID' ? t('earn.filter_paid') : ($st === 'REFUNDED' ? $st : t('earn.filter_unpaid'))) ?>
            </span>
          </div>
          <div class="earn-card-item__amount"><?= rp_local($t['total']) ?></div>
          <div class="earn-card-item__date"><?= date('M j, Y H:i', strtotime($t['created_at'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

  <!-- Withdrawals history -->
  <h2 style="margin-top:16px"><?= e(t('earn.withdrawals')) ?></h2>

  <?php if (!$withdrawals): ?>
    <div class="admin-empty" style="margin-bottom:64px">
      <p style="margin:0"><?= e(t('earn.wd_empty')) ?></p>
    </div>
  <?php else: ?>
    <div class="earn-table-wrap">
      <table class="earn-table">
        <thead>
          <tr>
            <th><?= e(t('earn.wd_date')) ?></th>
            <th class="num"><?= e(t('earn.wd_amount')) ?></th>
            <th class="num"><?= e(t('earn.wd_fee')) ?></th>
            <th><?= e(t('earn.wd_status')) ?></th>
            <th><?= e(t('earn.wd_note')) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($withdrawals as $w): ?>
            <?php
              $map = ['SENT'=>'paid','PENDING'=>'unpaid','REJECTED'=>'refunded'];
              $cls = $map[$w['status']] ?? 'unpaid';
              $lbl = $w['status'] === 'SENT' ? t('earn.wd_sent')
                   : ($w['status'] === 'REJECTED' ? t('earn.wd_rejected') : t('earn.wd_pending'));
            ?>
            <tr>
              <td><?= date('M j, Y H:i', strtotime($w['requested_at'])) ?></td>
              <td class="num"><strong><?= rp_local($w['amount']) ?></strong></td>
              <td class="num">
                <?= rp_local($w['fee']) ?>
                <small style="color:var(--muted);font-size:.72rem">
                  · <?= $w['flf_covers_fee'] ? 'FLF' : 'You' ?>
                </small>
              </td>
              <td><span class="earn-badge earn-badge--<?= $cls ?>"><?= e($lbl) ?></span></td>
                           <td style="font-size:.82rem;color:var(--muted)">
                <?php if (!empty($w['note'])): ?>
                  <div style="margin-bottom:6px"><?= e($w['note']) ?></div>
                <?php endif; ?>

                <?php if ($w['status'] === 'SENT'): ?>
                  <a class="btn btn-outline"
                     style="padding:4px 12px;font-size:.78rem"
                     href="receipt.php?id=<?= e(urlencode($w['receipt_id'])) ?>">
                    View receipt
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php';?>