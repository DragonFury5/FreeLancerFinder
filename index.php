<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'FLF — FreeLanceFinder';
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-inner">
    <div class="hero-copy">
      <span class="eyebrow">Any skill · Any freelancer</span>
      <h1>Find the right <span class="hl">Lancer</span> for whatever you need done.</h1>
      <p class="lede">FLF — FreeLanceFinder connects you with freelancers across the country: developers, designers, writers, builders, photographers, musicians, and everything in between.</p>
      <div class="hero-actions">
        <a class="btn btn-primary btn-lg" href="browse.php">Browse Lancers</a>
        <a class="btn btn-outline btn-lg" href="register.php">Join as a Lancer</a>
      </div>
      <ul class="hero-stats">
        <li><strong>Every</strong><span>category</span></li>
        <li><strong>Local</strong><span>talent</span></li>
        <li><strong>Free</strong><span>to join</span></li>
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
      <h2>What people say about FLF</h2>
      <p>Real words from Lancers and the customers who hired them.</p>
    </div>

    <div class="slider" data-slider>
      <div class="slider-track" data-track tabindex="0" aria-label="Reviews, scroll horizontally">
        <?php foreach ($testimonials as $t): ?>
          <article class="t-card">
            <p class="t-message">&ldquo;<?= e($t['message']) ?>&rdquo;</p>
            <footer class="t-foot">
              <div class="t-person">
                <span class="avatar avatar-sm"><?= e(initials($t['name'])) ?></span>
                <span>
                  <strong><?= e($t['name']) ?></strong>
                  <small><?= e($t['role']) ?></small>
                </span>
              </div>
              <?= render_stars((float)$t['rating']) ?>
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
      <h2>Ready to meet them?</h2>
      <p>Every kind of freelancer is already here. Take a look and find yours.</p>
      <a class="btn btn-primary btn-lg" href="browse.php">Check them out</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>