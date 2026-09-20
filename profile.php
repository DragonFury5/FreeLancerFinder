<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$me = current_user();
$viewId = isset($_GET['id']) ? (int)$_GET['id'] : (int)$me['id'];
$isSelf = $viewId === (int)$me['id'];

$stmt = $pdo->prepare(
    "SELECT u.id, u.name, u.email, u.role, u.created_at,
            l.title, l.skill, l.bio, l.location, l.rate, l.rating
     FROM users u
     LEFT JOIN lancers l ON l.user_id = u.id
     WHERE u.id = ?"
);
$stmt->execute([$viewId]);
$profile = $stmt->fetch();

if (!$profile) {
    http_response_code(404);
    $pageTitle = 'Not found — FLF';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="empty"><strong>Profile not found.</strong><p><a href="browse.php">Back to Lancers</a></p></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $profile['name'] . ' — FLF';
require __DIR__ . '/includes/header.php';

$isLancer = $profile['role'] === 'lancer' && $profile['title'] !== null;
?>

<div class="container">

  <section class="profile-hero">
    <span class="avatar avatar-lg"><?= e(initials($profile['name'])) ?></span>
    <div>
      <h1><?= e($profile['name']) ?></h1>
      <p class="sub"><?= $isLancer ? e($profile['title']) : 'Customer on FLF' ?></p>
      <div class="meta">
        <?php if ($isLancer && $profile['location']): ?>
          <span>📍 <?= e($profile['location']) ?></span>
        <?php endif; ?>
        <?php if ($isLancer && $profile['skill']): ?>
          <span>🏷️ <?= e($profile['skill']) ?></span>
        <?php endif; ?>
        <span>🗓️ Joined <?= date('M Y', strtotime($profile['created_at'])) ?></span>
        <?php if ($isSelf): ?>
          <span>✉️ <?= e($profile['email']) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <div class="profile-grid">
    <div class="panel">
      <h2>About</h2>
      <?php if ($isLancer && !empty($profile['bio'])): ?>
        <p><?= nl2br(e($profile['bio'])) ?></p>
      <?php elseif ($isLancer): ?>
        <p class="hint">No bio yet.</p>
      <?php else: ?>
        <p>This customer hasn't shared public details.</p>
      <?php endif; ?>
    </div>

    <aside class="panel">
      <h2>Details</h2>
      <dl style="margin:0">
        <div class="kv"><dt>Role</dt><dd><?= e(ucfirst($profile['role'])) ?></dd></div>
        <?php if ($isLancer): ?>
          <div class="kv"><dt>Rating</dt><dd><?= number_format((float)$profile['rating'], 1) ?> / 5</dd></div>
          <div class="kv"><dt>Rate</dt><dd>Rp <?= number_format((float)$profile['rate'], 0, ',', '.') ?></dd></div>
          <div class="kv"><dt>Skill</dt><dd><?= e($profile['skill']) ?></dd></div>
          <div class="kv"><dt>Location</dt><dd><?= e($profile['location']) ?></dd></div>
        <?php else: ?>
          <div class="kv"><dt>Member since</dt><dd><?= date('M j, Y', strtotime($profile['created_at'])) ?></dd></div>
        <?php endif; ?>
      </dl>

      <?php if ($isSelf): ?>
        <p style="margin-top:20px">
          <a class="btn btn-outline btn-block" href="browse.php">Browse Lancers</a>
        </p>
      <?php endif; ?>
    </aside>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>