<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

require_login();

$me = current_user();
$viewId = isset($_GET['id']) ? (int)$_GET['id'] : (int)$me['id'];
$isSelf = $viewId === (int)$me['id'];

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isSelf) {
    $newRate = (int)($_POST['rate'] ?? 0);
    if ($newRate < 0)         $newRate = 0;
    if ($newRate > 100000000) $newRate = 100000000;

    try {
        $stmt = $pdo->prepare("UPDATE lancers SET rate = ? WHERE user_id = ?");
        $stmt->execute([$newRate, (int)$me['id']]);
        $success = t('profile.rate_saved');
    } catch (Throwable $e) {
        $errors['rate'] = t('profile.rate_error');
    }
}

$stmt = $pdo->prepare(
        "SELECT u.id, u.name, u.email, u.role, u.created_at,
            l.title, l.skill, l.bio, l.location, l.rate, l.rating, l.category
     FROM users u
     LEFT JOIN lancers l ON l.user_id = u.id
     WHERE u.id = ?"
);
$stmt->execute([$viewId]);
$profile = $stmt->fetch();

if (!$profile) {
    http_response_code(404);
    $pageTitle = t('profile.not_found') . ' — FLF';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="empty"><strong>' . e(t('profile.not_found')) . '</strong>'
       . '<p><a href="browse.php">' . e(t('profile.back')) . '</a></p></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $profile['name'] . ' — FLF';
$isLancer  = $profile['role'] === 'lancer' && $profile['title'] !== null;

require __DIR__ . '/includes/header.php';
?>

<div class="container">

  <section class="profile-hero">
    <span class="avatar avatar-lg"><?= e(initials($profile['name'])) ?></span>
    <div>
      <h1><?= e($profile['name']) ?></h1>

      <p class="sub">
        <?= $isLancer ? e($profile['title']) : e(t('profile.customer')) ?>
        <?php if ($isLancer && !empty($profile['location'])): ?>
          &nbsp;·&nbsp;<span class="loc-inline">📍 <?= e($profile['location']) ?></span>
        <?php endif; ?>
      </p>

      <div class="meta">
               <?php if ($isLancer && $profile['skill']): ?>
          <span>🏷️ <?= e($profile['skill']) ?></span>
        <?php endif; ?>
        <?php if ($isLancer && !empty($profile['category'])): ?>
          <span>📂 <?= e($profile['category']) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <div class="profile-grid">

    <div class="panel">
      <h2><?= e(t('profile.about')) ?></h2>
      <?php if ($isLancer && !empty($profile['bio'])): ?>
        <p><?= nl2br(e($profile['bio'])) ?></p>
      <?php elseif ($isLancer): ?>
        <p class="hint"><?= e(t('profile.no_bio')) ?></p>
      <?php else: ?>
        <p><?= e(t('profile.customer_no')) ?></p>
      <?php endif; ?>

      <?php if ($isLancer): ?>
              <div class="rate-box">
                 <?php if ($isSelf): ?>
            <form method="post" class="rate-edit">
              <label for="rate" class="rate-edit__label"><?= e(t('profile.rate_label')) ?></label>
              <div class="rate-edit__row">
                <span class="rate-edit__prefix">Rp</span>
                <input type="number" id="rate" name="rate"
                       class="input rate-edit__input"
                       value="<?= (int)$profile['rate'] ?>"
                       min="0" step="1000" required>
                <button class="btn btn-primary" type="submit">
                  <?= e(t('profile.rate_save')) ?>
                </button>
              </div>
              <p class="hint"><?= e(t('profile.rate_hint')) ?></p>

              <?php if ($success): ?>
                <div class="alert alert-success" style="margin-top:12px"><?= e($success) ?></div>
              <?php endif; ?>
              <?php if (!empty($errors['rate'])): ?>
                <div class="err"><?= e($errors['rate']) ?></div>
              <?php endif; ?>
            </form>
          <?php else: ?>
            <span class="rate-box__label"><?= e(t('profile.rate_label')) ?></span>
            <span class="rate-box__value">
              Rp <?= number_format((float)$profile['rate'], 0, ',', '.') ?>
            </span>
            <?php if (!empty($profile['category'])): ?>
              <span class="rate-box__label" style="margin-top:14px"><?= e(t('profile.category')) ?></span>
              <span class="rate-box__value" style="font-size:1.05rem"><?= e($profile['category']) ?></span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <aside class="panel">
      <h2><?= e(t('profile.details')) ?></h2>
      <dl style="margin:0">
        <div class="kv"><dt><?= e(t('profile.role')) ?></dt><dd><?= e(ucfirst($profile['role'])) ?></dd></div>
        <?php if ($isLancer): ?>
          <div class="kv"><dt><?= e(t('profile.rating')) ?></dt><dd><?= number_format((float)$profile['rating'], 1) ?> / 5</dd></div>
          <div class="kv"><dt><?= e(t('profile.skill')) ?></dt><dd><?= e($profile['skill']) ?></dd></div>
          <div class="kv"><dt><?= e(t('profile.location')) ?></dt><dd><?= e($profile['location'] ?: '—') ?></dd></div>
        <?php else: ?>
          <div class="kv"><dt><?= e(t('profile.member_since')) ?></dt><dd><?= date('M j, Y', strtotime($profile['created_at'])) ?></dd></div>
        <?php endif; ?>
      </dl>
    </aside>

    <?php if ($isLancer && !$isSelf): ?>
      <aside class="panel" style="grid-column:1 / -1">
        <h2><?= e(t('profile.hire_h')) ?></h2>
        <p class="hint" style="margin-bottom:18px">
          <?= e(t('profile.hire_text', ['name' => $profile['name']])) ?>
        </p>

        <dl style="margin:0 0 18px">
          <div class="kv"><dt><?= e(t('profile.hire_amount')) ?></dt>
            <dd>Rp <?= number_format((float)$profile['rate'], 0, ',', '.') ?></dd></div>
          <div class="kv"><dt><?= e(t('profile.location')) ?></dt>
            <dd><?= e($profile['location'] ?: '—') ?></dd></div>
          <div class="kv"><dt><?= e(t('profile.skill')) ?></dt>
            <dd><?= e($profile['skill'] ?: '—') ?></dd></div>
        </dl>

        <a class="btn btn-primary btn-lg"
           href="payment/pay.php?lancer_id=<?= (int)$profile['id'] ?>">
          <?= e(t('profile.pay_btn')) ?>
        </a>
      </aside>
    <?php endif; ?>

      <?php if ($isSelf && $isLancer): ?>
      <?php
        $earnStmt = $pdo->prepare(
            "SELECT
                SUM(CASE WHEN UPPER(status) IN ('PAID','SUCCESS','SETTLED') THEN total ELSE 0 END) AS paid,
                SUM(CASE WHEN UPPER(status) IN ('PAID','SUCCESS','SETTLED') THEN 0
                         WHEN UPPER(status) = 'REFUNDED' THEN 0
                         ELSE total END) AS pending,
                SUM(CASE WHEN UPPER(status) <> 'REFUNDED' THEN total ELSE 0 END) AS total_all,
                SUM(CASE WHEN UPPER(status) <> 'REFUNDED'
                         AND YEAR(created_at) = YEAR(NOW())
                         AND MONTH(created_at) = MONTH(NOW())
                         THEN total ELSE 0 END) AS this_month
             FROM transactions WHERE lancer_id = ?"
        );
        $earnStmt->execute([(int)$profile['id']]);
        $earn = $earnStmt->fetch() ?: [];
      ?>
      <aside class="panel" style="grid-column:1 / -1">
        <h2><?= e(t('earn.card_label')) ?></h2>

        <div class="earn-card">
          <span class="earn-card__label"><?= e(t('earn.card_total')) ?></span>
          <span class="earn-card__total">Rp <?= number_format((float)($earn['total_all'] ?? 0), 0, ',', '.') ?></span>

          <div class="earn-card__row">
            <span>
              <?= e(t('earn.card_pending')) ?>
              <strong>Rp <?= number_format((float)($earn['pending'] ?? 0), 0, ',', '.') ?></strong>
            </span>
            <span>
              <?= e(t('earn.card_month')) ?>
              <strong>Rp <?= number_format((float)($earn['this_month'] ?? 0), 0, ',', '.') ?></strong>
            </span>
          </div>

          <a class="btn btn-outline earn-card__cta" href="earnings.php">
            <?= e(t('earn.card_view')) ?>
          </a>
        </div>
      </aside>
    <?php elseif ($isSelf): ?>
      <aside class="panel" style="grid-column:1 / -1">
        <h2><?= e(t('profile.quick')) ?></h2>
        <p style="margin:0 0 16px">
          <a class="btn btn-outline" href="browse.php"><?= e(t('profile.browse_btn')) ?></a>
        </p>
      </aside>
    <?php endif; ?>

  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>