<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/lang.php';

$pageTitle = 'FLF — FreeLanceFinder';
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-inner">
    <div class="hero-copy">
      <span class="eyebrow"><?= e(t('hero.eyebrow')) ?></span>
      <h1><?= t('hero.title') ?></h1>
      <p class="lede"><?= e(t('hero.lede')) ?></p>
      <div class="hero-actions">
        <a class="btn btn-primary btn-lg" href="browse.php"><?= e(t('hero.cta_browse')) ?></a>
        <a class="btn btn-outline btn-lg" href="register.php"><?= e(t('hero.cta_join')) ?></a>
      </div>
      <ul class="hero-stats">
        <li><strong><?= e(t('hero.stat1_t')) ?></strong><span><?= e(t('hero.stat1_s')) ?></span></li>
        <li><strong><?= e(t('hero.stat2_t')) ?></strong><span><?= e(t('hero.stat2_s')) ?></span></li>
        <li><strong><?= e(t('hero.stat3_t')) ?></strong><span><?= e(t('hero.stat3_s')) ?></span></li>
      </ul>
    </div>

    <div class="hero-visual" aria-hidden="true">
      <div class="tile tile-1"><svg viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
      <div class="tile tile-2"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></div>
      <div class="tile tile-3"><svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div>
      <div class="tile tile-4"><svg viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg></div>
      <div class="tile tile-5"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg></div>
      <div class="tile tile-6"><svg viewBox="0 0 24 24"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <h2><?= e(t('reviews.heading')) ?></h2>
      <p><?= e(t('reviews.sub')) ?></p>
    </div>

    <div class="slider" data-slider>
      <div class="slider-track" data-track tabindex="0" aria-label="Reviews">
        <?php foreach ($testimonials as $tst): ?>
          <article class="t-card">
            <p class="t-message">&ldquo;<?= e($tst['message']) ?>&rdquo;</p>
            <footer class="t-foot">
              <div class="t-person">
                <span class="avatar avatar-sm"><?= e(initials($tst['name'])) ?></span>
                <span>
                  <strong><?= e($tst['name']) ?></strong>
                  <small><?= e($tst['role']) ?></small>
                </span>
              </div>
              <?= render_stars((float)$tst['rating']) ?>
            </footer>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="slider-dots" data-dots></div>
    </div>
  </div>
</section>

<section class="section cta-section">
  <div class="container">
    <div class="cta-card">
      <h2><?= e(t('cta.heading')) ?></h2>
      <p><?= e(t('cta.text')) ?></p>
      <a class="btn btn-primary btn-lg" href="browse.php"><?= e(t('cta.button')) ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>