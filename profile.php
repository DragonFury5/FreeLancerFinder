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

$pageTitle  = $profile['name'] . ' — FLF';
$isLancer   = $profile['role'] === 'lancer' && $profile['title'] !== null;

require __DIR__ . '/includes/header.php';
?>

<div class="container">

  <section class="profile-hero">
    <span class="avatar avatar-lg"><?= e(initials($profile['name'])) ?></span>
    <div>
      <h1><?= e($profile['name']) ?></h1>

      <!-- Location right beside the title, plain text -->
      <p class="sub">
        <?= $isLancer ? e($profile['title']) : 'Customer on FLF' ?>
        <?php if ($isLancer && !empty($profile['location'])): ?>
          &nbsp;·&nbsp;<span class="loc-inline">📍 <?= e($profile['location']) ?></span>
        <?php endif; ?>
      </p>

      <div class="meta">
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
          <div class="kv"><dt>Location</dt><dd><?= e($profile['location'] ?: '—') ?></dd></div>
        <?php else: ?>
          <div class="kv"><dt>Member since</dt><dd><?= date('M j, Y', strtotime($profile['created_at'])) ?></dd></div>
        <?php endif; ?>
      </dl>
    </aside>

    <?php if ($isLancer && !$isSelf): ?>
      <!-- Payment panel: only for other people's Lancer profiles -->
      <aside class="panel" style="grid-column:1 / -1">
        <h2>Hire this Lancer</h2>
        <p class="hint" style="margin-bottom:18px">
          Start a secure sandbox payment via Tripay. The amount is pre-filled with
          <strong><?= e($profile['name']) ?></strong>'s rate — you can change it before paying.
        </p>

        <dl style="margin:0 0 18px">
          <div class="kv"><dt>Amount</dt>   <dd>Rp <?= number_format((float)$profile['rate'], 0, ',', '.') ?></dd></div>
          <div class="kv"><dt>Location</dt> <dd><?= e($profile['location'] ?: '—') ?></dd></div>
          <div class="kv"><dt>Skill</dt>    <dd><?= e($profile['skill'] ?: '—') ?></dd></div>
        </dl>

        <a class="btn btn-primary btn-lg"
           href="payment/pay.php?lancer_id=<?= (int)$profile['id'] ?>">
          Pay this Lancer →
        </a>
      </aside>
    <?php endif; ?>

    <?php if ($isSelf): ?>
      <aside class="panel" style="grid-column:1 / -1">
        <h2>Quick actions</h2>
        <p style="margin:0 0 16px">
          <a class="btn btn-outline" href="browse.php">Browse Lancers</a>
        </p>
      </aside>
    <?php endif; ?>

  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>