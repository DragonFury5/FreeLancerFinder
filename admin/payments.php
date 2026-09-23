<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/lang.php';

require_admin();

$q = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM transactions";
$params = [];
if ($q !== '') {
    $sql .= " WHERE merchant_ref LIKE ? OR reference LIKE ? OR lancer_name LIKE ?
                    OR status LIKE ? OR method LIKE ?";
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like, $like];
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Totals across every transaction
$tot = $pdo->query(
    "SELECT COUNT(*) AS n,
            COALESCE(SUM(amount),0)   AS amount,
            COALESCE(SUM(donation),0) AS donation,
            COALESCE(SUM(total),0)    AS total
     FROM transactions"
)->fetch();

$pageTitle = 'Payment — FLF Admin';
require __DIR__ . '/includes/header.php';

function rp($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
?>

<div class="container">

  <div class="admin-head">
    <h1>Payment</h1>
    <p>Every transaction created through the Tripay sandbox.</p>
  </div>

  <div class="admin-stats">
    <div class="admin-stat admin-stat--accent">
      <span class="admin-stat__label">Transactions</span>
      <span class="admin-stat__value"><?= (int)$tot['n'] ?></span>
    </div>
    <div class="admin-stat">
      <span class="admin-stat__label">Transferred to Lancers</span>
      <span class="admin-stat__value"><?= rp($tot['amount']) ?></span>
    </div>
    <div class="admin-stat admin-stat--money">
      <span class="admin-stat__label">Donations to FLF</span>
      <span class="admin-stat__value"><?= rp($tot['donation']) ?></span>
    </div>
    <div class="admin-stat">
      <span class="admin-stat__label">Grand total</span>
      <span class="admin-stat__value"><?= rp($tot['total']) ?></span>
    </div>
  </div>

  <form class="admin-search" method="get" action="payments.php" role="search">
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <circle cx="11" cy="11" r="7"/>
      <line x1="20" y1="20" x2="16.65" y2="16.65"/>
    </svg>
    <input type="search" name="q"
           placeholder="Search by reference, Lancer, status, or method…"
           aria-label="Search transactions"
           value="<?= e($q) ?>">
  </form>

  <p class="result-meta">
    <?php if ($q !== ''): ?>
      <?= count($rows) ?> result<?= count($rows) === 1 ? '' : 's' ?> for
      &ldquo;<?= e($q) ?>&rdquo; &middot; <a href="payments.php">clear</a>
    <?php else: ?>
      Showing all <?= count($rows) ?> transaction<?= count($rows) === 1 ? '' : 's' ?>
    <?php endif; ?>
  </p>

  <?php if (!$rows): ?>
    <div class="admin-empty">
      <strong>No transactions yet.</strong>
      <p>Once someone pays through a Lancer profile, the entry will show up here.</p>
    </div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Merchant ref</th>
            <th>Lancer</th>
            <th>Method</th>
            <th class="num">Amount</th>
            <th class="num">Donation</th>
            <th class="num">Total</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $t): ?>
            <tr>
              <td class="name"><?= e($t['merchant_ref']) ?></td>
              <td><?= e($t['lancer_name'] ?: '—') ?></td>
              <td><?= e($t['method']) ?></td>
              <td class="num"><?= rp($t['amount']) ?></td>
              <td class="num"><?= $t['donation'] > 0 ? rp($t['donation']) : '—' ?></td>
              <td class="num"><strong><?= rp($t['total']) ?></strong></td>
              <td>
                <span class="status-badge status-badge--<?= e(strtolower($t['status'])) ?>">
                  <?= e($t['status']) ?>
                </span>
              </td>
              <td><?= date('M j, Y H:i', strtotime($t['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>