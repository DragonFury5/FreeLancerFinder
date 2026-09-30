<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/lang.php';
require_once __DIR__ . '/../includes/earnings.php';

require_admin();

$q = trim($_GET['q'] ?? '');

$sql = "SELECT w.*, u.name AS lancer_name
        FROM withdrawals w
        JOIN users u ON u.id = w.lancer_id";
$params = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $sql .= " WHERE w.receipt_id LIKE ? OR w.tripay_ref LIKE ?
                    OR u.name LIKE ? OR w.bank LIKE ? OR w.status LIKE ?";
    $params = [$like, $like, $like, $like, $like];
}
$sql .= " ORDER BY w.requested_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$tot = $pdo->query(
    "SELECT
       COUNT(*) AS n,
       COALESCE(SUM(CASE WHEN status='SENT'     THEN amount ELSE 0 END),0) AS sent,
       COALESCE(SUM(CASE WHEN status='PENDING'  THEN amount ELSE 0 END),0) AS pending,
       COALESCE(SUM(CASE WHEN status='REJECTED' THEN amount ELSE 0 END),0) AS rejected
     FROM withdrawals"
)->fetch();

$pageTitle = 'Receipts — FLF Admin';
require __DIR__ . '/includes/header.php';

function rp($n){ return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
?>

<div class="container">

  <div class="admin-head">
    <h1>Withdrawal receipts</h1>
    <p>Every disbursement attempt. Click a row to view its receipt.</p>
  </div>

  <div class="admin-stats">
    <div class="admin-stat admin-stat--accent">
      <span class="admin-stat__label">Withdrawals</span>
      <span class="admin-stat__value"><?= (int)$tot['n'] ?></span>
    </div>
    <div class="admin-stat admin-stat--money">
      <span class="admin-stat__label">Sent</span>
      <span class="admin-stat__value"><?= rp($tot['sent']) ?></span>
    </div>
    <div class="admin-stat">
      <span class="admin-stat__label">Pending</span>
      <span class="admin-stat__value"><?= rp($tot['pending']) ?></span>
    </div>
    <div class="admin-stat">
      <span class="admin-stat__label">Rejected</span>
      <span class="admin-stat__value"><?= rp($tot['rejected']) ?></span>
    </div>
  </div>

  <form class="admin-search" method="get" action="receipts.php" role="search">
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <circle cx="11" cy="11" r="7"/>
      <line x1="20" y1="20" x2="16.65" y2="16.65"/>
    </svg>
    <input type="search" name="q"
           placeholder="Search by receipt, ref, Lancer, bank…"
           aria-label="Search receipts"
           value="<?= e($q) ?>">
  </form>

  <p class="result-meta">
    <?php if ($q !== ''): ?>
      <?= count($rows) ?> result<?= count($rows) === 1 ? '' : 's' ?> for
      &ldquo;<?= e($q) ?>&rdquo; &middot; <a href="receipts.php">clear</a>
    <?php else: ?>
      Showing all <?= count($rows) ?> receipt<?= count($rows) === 1 ? '' : 's' ?>
    <?php endif; ?>
  </p>

  <?php if (!$rows): ?>
    <div class="admin-empty">
      <strong>No withdrawals yet.</strong>
      <p>Once a Lancer requests a withdrawal, it will show up here.</p>
    </div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Receipt</th>
            <th>Lancer</th>
            <th>Bank</th>
            <th class="num">Amount</th>
            <th class="num">Fee</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $w): ?>
            <?php
              $cls = ['SENT'=>'paid','PENDING'=>'pending','REJECTED'=>'failed'][$w['status']] ?? 'pending';
            ?>
            <tr onclick="window.location='../receipt.php?id=<?= e(urlencode($w['receipt_id'])) ?>'"
                style="cursor:pointer">
              <td class="name" style="font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.85rem">
                <?= e($w['receipt_id']) ?>
              </td>
              <td><?= e($w['lancer_name']) ?></td>
              <td><?= e($w['bank']) ?> · <?= e(mask_account($w['account'])) ?></td>
              <td class="num"><strong><?= rp($w['amount']) ?></strong></td>
              <td class="num">
                <?= rp($w['fee']) ?>
                <small style="color:var(--muted);font-size:.72rem">·
                  <?= $w['flf_covers_fee'] ? 'FLF' : 'Lancer' ?>
                </small>
              </td>
              <td>
                <span class="status-badge status-badge--<?= $cls ?>">
                  <?= e($w['status']) ?>
                </span>
              </td>
              <td><?= date('M j, Y H:i', strtotime($w['requested_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>