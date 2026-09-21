</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <p><?= t('footer.copy', ['year' => date('Y')]) ?></p>
    <p class="footer-links">
      <a href="<?= e($baseUrl) ?>index.php"><?= e(t('nav.home')) ?></a>
      <a href="<?= e($baseUrl) ?>browse.php"><?= e(t('nav.browse')) ?></a>
    </p>
  </div>
</footer>

<script src="<?= e($baseUrl) ?>assets/js/main.js"></script>
</body>
</html>