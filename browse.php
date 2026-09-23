<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

$pageTitle = t('browse.title') . ' — FLF';

$q       = trim($_GET['q']   ?? '');
$cat     = trim($_GET['cat'] ?? '');
$allowed = ['IT', 'Service', 'Others'];
if (!in_array($cat, $allowed, true)) $cat = '';

$sql = "SELECT l.id, l.user_id, l.title, l.skill, l.bio, l.location, l.rate, l.rating, l.category, u.name
        FROM lancers l
        JOIN users u ON u.id = l.user_id";
$where  = [];
$params = [];

if ($q !== '') {
    $where[] = "(u.name LIKE ? OR l.title LIKE ? OR l.skill LIKE ? OR l.bio LIKE ? OR l.location LIKE ?)";
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($cat !== '') {
    $where[] = "l.category = ?";
    $params[] = $cat;
}
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY l.rating DESC, l.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lancers = $stmt->fetchAll();

// Human-readable label for the current category filter
$catLabel = '';
if ($cat === 'IT')      $catLabel = t('browse.filter_it');
if ($cat === 'Service') $catLabel = t('browse.filter_svc');
if ($cat === 'Others')  $catLabel = t('browse.filter_other');

require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
  <div class="container">
    <h1 class="typewriter"
        data-typewriter
        data-text="<?= e(t('browse.title')) ?>">
      <span class="typewriter__text"><?= e(t('browse.title')) ?></span><span class="typewriter__cursor" aria-hidden="true"></span>
    </h1>
    <p><?= e(t('browse.sub')) ?></p>

    <form class="search-form" method="get" action="browse.php" role="search">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
          <circle cx="11" cy="11" r="7"/>
          <line x1="20" y1="20" x2="16.65" y2="16.65"/>
        </svg>
        <input class="search-input" type="search" name="q"
               placeholder="<?= e(t('browse.search_ph')) ?>"
               aria-label="<?= e(t('browse.search_aria')) ?>"
               value="<?= e($q) ?>">
      </div>

      <select class="search-select" name="cat"
              aria-label="<?= e(t('browse.filter_label')) ?>">
        <option value=""     <?= $cat === ''        ? 'selected' : '' ?>><?= e(t('browse.filter_all'))   ?></option>
        <option value="IT"      <?= $cat === 'IT'      ? 'selected' : '' ?>><?= e(t('browse.filter_it'))    ?></option>
        <option value="Service" <?= $cat === 'Service' ? 'selected' : '' ?>><?= e(t('browse.filter_svc'))   ?></option>
        <option value="Others"  <?= $cat === 'Others'  ? 'selected' : '' ?>><?= e(t('browse.filter_other')) ?></option>
      </select>

      <button class="btn btn-primary" type="submit"><?= e(t('browse.search_btn')) ?></button>
    </form>

    <p class="result-meta">
      <?php if ($q !== '' || $cat !== ''): ?>
        <?php
          $pieces = [];
          if ($q   !== '') $pieces[] = t('browse.results_for', ['count' => count($lancers), 'q' => $q]);
          if ($cat !== '') $pieces[] = $catLabel;
          echo e(implode(' · ', $pieces));
        ?>
        &middot; <a href="browse.php"><?= e(t('browse.clear')) ?></a>
      <?php else: ?>
        <?= e(t('browse.showing_all', ['count' => count($lancers)])) ?>
      <?php endif; ?>
    </p>
  </div>
</section>

<div class="container">
  <?php if (!$lancers): ?>
    <div class="empty">
      <strong><?= e(t('browse.empty_t')) ?></strong>
      <p><?= str_replace(':link',
            '<a href="browse.php">' . e(t('browse.everyone')) . '</a>',
            e(t('browse.empty_s'))) ?></p>
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
          <?php if (!empty($l['skill']) || !empty($l['category'])): ?>
            <span class="badge">
              <?= e($l['skill'] ?: $l['category']) ?>
            </span>
          <?php endif; ?>
          <?php if (!empty($l['bio'])): ?>
            <p class="lancer-bio"><?= e($l['bio']) ?></p>
          <?php endif; ?>
          <div class="lancer-foot">
            <span class="rate"><?= number_format((float)$l['rating'], 1) ?>
              <small><?= e(t('browse.rating')) ?></small></span>
            <span class="loc"><?= e($l['location']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>