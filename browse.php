<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Find Lancers — FLF';
$q = trim($_GET['q'] ?? '');

$sql = "SELECT l.id, l.user_id, l.title, l.skill, l.bio, l.location, l.rate, l.rating, u.name
        FROM lancers l
        JOIN users u ON u.id = l.user_id";
$params = [];

if ($q !== '') {
    $sql .= " WHERE u.name LIKE ? OR l.title LIKE ? OR l.skill LIKE ? OR l.bio LIKE ? OR l.location LIKE ?";
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like, $like];
}
$sql .= " ORDER BY l.rating DESC, l.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lancers = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
  <div class="container">
    <h1>Find your Lancer</h1>
    <p>Developers, designers, writers, builders, photographers — whoever you need, they're here.</p>

    <form class="search-form" method="get" action="browse.php" role="search">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="11" cy="11" r="7"/>
          <line x1="20" y1="20" x2="16.65" y2="16.65"/>
        </svg>
        <input
          class="search-input"
          type="search"
          name="q"
          placeholder="Search for Lancers!"
          value="<?= e($q) ?>"
          aria-label="Search for Lancers"
        >
      </div>
      <button class="btn btn-primary" type="submit">Search</button>
    </form>

    <p class="result-meta">
      <?php if ($q !== ''): ?>
        <?= count($lancers) ?> result<?= count($lancers) === 1 ? '' : 's' ?> for &ldquo;<?= e($q) ?>&rdquo;
        &middot; <a href="browse.php">clear</a>
      <?php else: ?>
        Showing all <?= count($lancers) ?> Lancers
      <?php endif; ?>
    </p>
  </div>
</section>

<div class="container">
  <?php if (!$lancers): ?>
    <div class="empty">
      <strong>No Lancers found.</strong>
      <p>Try a different keyword or <a href="browse.php">browse everyone</a>.</p>
    </div>
  <?php else: ?>
    <div class="lancer-grid">
      <?php foreach ($lancers as $l): ?>
        <a class="lancer-card" href="profile.php?id=<?= (int)$l['user_id'] ?>">
          <div class="lancer-top">
            <span class="avatar avatar-sm"><?= e(initials($l['name'])) ?></span>
            <div>
              <div class="lancer-name"><?= e($l['name']) ?></div>
              <div class="lancer-title"><?= e($l['title']) ?></div>
            </div>
          </div>
          <?php if ($l['skill'] !== ''): ?>
            <span class="badge"><?= e($l['skill']) ?></span>
          <?php endif; ?>
          <?php if (!empty($l['bio'])): ?>
            <p class="lancer-bio"><?= e($l['bio']) ?></p>
          <?php endif; ?>
          <div class="lancer-foot">
            <span class="rate"><?= number_format((float)$l['rating'], 1) ?> <small>rating</small></span>
            <span class="loc"><?= e($l['location']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>