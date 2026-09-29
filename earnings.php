<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

require_login();

$me = current_user();
if (($me['role'] ?? '') !== 'lancer') {
    // Customers have no earnings — send them to browse.
    header('Location: browse.php');
    exit;
}

$lancerId = (int)$me['id'];

/** Normalize any Tripay status into our 3 canonical states. */
function earnings_status(string $raw): string {
    $s = strtoupper(trim($raw));
    if (in_array($s, ['PAID', 'SUCCESS', 'SETTLED'], true)) return 'PAID';
    if ($s === 'REFUNDED') return 'REFUNDED';
    return 'UNPAID';
}

function rp_local($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }

// --- Filters ---
$q    = trim($_GET['q']    ?? '');
$flt  = trim($_GET['filter'] ?? '');       // '', 'paid', 'unpaid'
$allowedFilters = ['', 'paid', 'unpaid'];
if (!in_array($flt, $allowedFilters, true)) $flt = '';

// --- Build query ---
$sql    = "SELECT * FROM transactions WHERE lancer_id = ?";
$params = [$lancerId];

$where = [];

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

// --- Stats (always over the full pool for this Lancer) ---
$statStmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS n,
        SUM(CASE WHEN UPPER(status) IN ('PAID','SUCCESS','SETTLED') THEN total ELSE 0 END) AS paid,
        SUM(CASE WHEN UPPER(status) IN ('PAID','SUCCESS','SETTLED') THEN 0
                 WHEN UPPER(status) = 'REFUNDED' THEN 0
                 ELSE total END) AS pending,
        SUM(CASE WHEN UPPER(status) <> 'REFUNDED' THEN total ELSE 0 END) AS total_all,
        SUM(CASE WHEN UPPER(status) <> 'REFUNDED'
                 AND YEAR(created_at) = YEAR(NOW())
                 AND MONTH(created_at) = MONTH(NOW())
                 THEN total ELSE 0 END) AS this_month
     FROM transactions
     WHERE lancer_id = ?"
);
$statStmt->execute([$lancerId]);
$stats = $statStmt->fetch() ?: [];

$pageTitle = t('earn.title') . ' — FLF';
require __DIR__ . '/includes/header.php';
?>

<div class="container">

  <div class="admin-head">
    <h1><?= e(t('earn.title')) ?></h1>
    <p><?= e(t('earn.sub')) ?></p>
  </div>

  <div class="earn-stats">
    <div class="earn-stat earn-stat--accent">
      <span class="earn-stat__label"><?= e(t('earn.stat_total')) ?></span>
      <span class="earn-stat__value"><?= rp_local($stats['total_all'] ?? 0) ?></span>
    </div>
    <div class="earn-stat earn-stat--money">
      <span class="earn-stat__label"><?= e(t('earn.stat_paid')) ?></span>
      <span class="earn-stat__value"><?= rp_local($stats['paid'] ?? 0) ?></span>
    </div>
    <div class="earn-stat earn-stat--pending">
      <span class="earn-stat__label"><?= e(t('earn.stat_pending')) ?></span>
      <span class="earn-stat__value"><?= rp_local($stats['pending'] ?? 0) ?></span>
    </div>
    <div class="earn-stat">
      <span class="earn-stat__label"><?= e(t('earn.stat_month')) ?></span>
      <span class="earn-stat__value"><?= rp_local($stats['this_month'] ?? 0) ?></span>
    </div>
  </div>

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
        $qs = http_build_query(array_filter([
          'q'      => $q,
          'filter' => $key,
        ], fn($v) => $v !== ''));
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
        if ($q !== '')   $pieces[] = t('earn.results_for', ['count' => count($rows), 'q' => $q]);
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

    <!-- Desktop table -->
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
            <?php
              $st  = earnings_status($t['status'] ?? '');
              $cls = strtolower($st);
            ?>
            <tr>
              <td><?= date('M j, Y H:i', strtotime($t['created_at'])) ?></td>
              <td class="name">
                <?= e($t['customer_name'] !== '' ? $t['customer_name'] : t('earn.guest')) ?>
              </td>
              <td class="num"><strong><?= rp_local($t['total']) ?></strong></td>
              <td>
                <span class="earn-badge earn-badge--<?= $cls ?>">
                  <?= e($st === 'PAID' ? t('earn.filter_paid') : ($st === 'REFUNDED' ? $st : t('earn.filter_unpaid'))) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Mobile cards -->
    <div class="earn-cards">
      <?php foreach ($rows as $t): ?>
        <?php
          $st  = earnings_status($t['status'] ?? '');
          $cls = strtolower($st);
        ?>
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

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>