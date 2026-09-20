(function () {
  'use strict';

  /* ---------- Dropdown ---------- */
  document.querySelectorAll('[data-dropdown]').forEach(function (dd) {
    var toggle = dd.querySelector('[data-dropdown-toggle]');
    var menu   = dd.querySelector('[data-dropdown-menu]');
    if (!toggle || !menu) return;

    function close() { menu.hidden = true;  toggle.setAttribute('aria-expanded', 'false'); }
    function open()  { menu.hidden = false; toggle.setAttribute('aria-expanded', 'true');  }

    toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      menu.hidden ? open() : close();
    });
    document.addEventListener('click', function (e) { if (!dd.contains(e.target)) close(); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { close(); toggle.focus(); }
    });
  });

  /* ---------- Testimonial slider ---------- */
  document.querySelectorAll('[data-slider]').forEach(function (slider) {
    var track   = slider.querySelector('[data-track]');
    var dotsBox = slider.querySelector('[data-dots]');
    if (!track || !dotsBox) return;

    var cards = Array.prototype.slice.call(track.children);
    if (!cards.length) return;

    var dots = [], index = 0, timer = null;
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function leftOf(card) { return card.offsetLeft - cards[0].offsetLeft; }

    cards.forEach(function (card, i) {
      var dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'dot';
      dot.setAttribute('aria-label', 'Go to review ' + (i + 1));
      dot.addEventListener('click', function () { goTo(i); restart(); });
      dotsBox.appendChild(dot);
      dots.push(dot);
    });

    function goTo(i) {
      index = Math.max(0, Math.min(i, cards.length - 1));
      track.scrollTo({ left: leftOf(cards[index]), behavior: reduced ? 'auto' : 'smooth' });
    }

    function sync() {
      var center = track.scrollLeft + track.clientWidth / 2;
      var best = 0, bestDist = Infinity;
      cards.forEach(function (card, i) {
        var c = leftOf(card) + card.offsetWidth / 2;
        var d = Math.abs(c - center);
        if (d < bestDist) { bestDist = d; best = i; }
      });
      index = best;
      dots.forEach(function (d, i) { d.classList.toggle('is-active', i === index); });
    }

    function next() { goTo(index >= cards.length - 1 ? 0 : index + 1); }
    function start() { if (!reduced) timer = setInterval(next, 5000); }
    function stop() { clearInterval(timer); }
    function restart() { stop(); start(); }

    track.addEventListener('scroll', function () {
      window.requestAnimationFrame(sync);
    }, { passive: true });

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);

    sync();
    start();
  });

  /* ---------- Register form: toggle Lancer fields ---------- */
  var roleRadios = document.querySelectorAll('input[name="role"]');
  var lancerFields = document.getElementById('lancerFields');
  if (roleRadios.length && lancerFields) {
    function syncRole() {
      var checked = document.querySelector('input[name="role"]:checked');
      lancerFields.hidden = !checked || checked.value !== 'lancer';
    }
    roleRadios.forEach(function (r) { r.addEventListener('change', syncRole); });
    syncRole();
  }
})();