<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/lang.php';

require_admin();

$q = trim($_GET['q'] ?? '');

$sql = "SELECT id, name, email, role, created_at FROM users";
$params = [];
if ($q !== '') {
    $sql .= " WHERE name LIKE ? OR email LIKE ? OR role LIKE ?";
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Stats (always across the whole table)
$total     = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$lancers   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='lancer'")->fetchColumn();
$customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$admins    = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();

$pageTitle = 'Actions — FLF Admin';
require __DIR__ . '/includes/header.php';
?>

<div class="container">

  <div class="admin-head">
    <h1>Actions</h1>
    <p>All registered accounts on FLF — customers, Lancers, and admins.</p>
  </div>

  <div class="admin-stats">
    <div class="admin-stat admin-stat--accent">
      <span class="admin-stat__label">Total users</span>
      <span class="admin-stat__value"><?= $total ?></span>
    </div>
    <div class="admin-stat">
      <span class="admin-stat__label">Lancers</span>
      <span class="admin-stat__value"><?= $lancers ?></span>
    </div>
    <div class="admin-stat">
      <span class="admin-stat__label">Customers</span>
      <span class="admin-stat__value"><?= $customers ?></span>
    </div>
    <div class="admin-stat">
      <span class="admin-stat__label">Admins</span>
      <span class="admin-stat__value"><?= $admins ?></span>
    </div>
  </div>

  <form class="admin-search" method="get" action="index.php" role="search">
    <svg viewBox="0 0 24 24" aria-hidden="true">
      <circle cx="11" cy="11" r="7"/>
      <line x1="20" y1="20" x2="16.65" y2="16.65"/>
    </svg>
    <input type="search" name="q"
           placeholder="Search users by name, email, or role…"
           aria-label="Search users"
           value="<?= e($q) ?>">
  </form>

  <p class="result-meta">
    <?php if ($q !== ''): ?>
      <?= count($users) ?> result<?= count($users) === 1 ? '' : 's' ?> for
      &ldquo;<?= e($q) ?>&rdquo; &middot; <a href="index.php">clear</a>
    <?php else: ?>
      Showing all <?= count($users) ?> users
    <?php endif; ?>
  </p>

  <?php if (!$users): ?>
    <div class="admin-empty">
      <strong>No users found.</strong>
      <p>Try a different keyword, or <a href="index.php">see everyone</a>.</p>
    </div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td class="num"><?= (int)$u['id'] ?></td>
              <td class="name"><?= e($u['name']) ?></td>
              <td class="email"><?= e($u['email']) ?></td>
              <td>
                <span class="role-badge role-badge--<?= e($u['role']) ?>">
                  <?= e(ucfirst($u['role'])) ?>
                </span>
              </td>
              <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>