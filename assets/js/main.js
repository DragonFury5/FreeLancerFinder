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

    /* ---------- Typewriter headings ---------- */
  document.querySelectorAll('[data-typewriter]').forEach(function (el) {
    var textEl = el.querySelector('.typewriter__text');
    if (!textEl) return;

    var full = el.getAttribute('data-text') || textEl.textContent || '';
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Respect reduced motion: leave the text as-is, no cursor animation needed.
    if (reduced || full === '') return;

    // Optional tuning via data attributes:
    var speed  = parseInt(el.getAttribute('data-speed')  || '55', 10); // ms per char
    var delay  = parseInt(el.getAttribute('data-delay')  || '250', 10); // ms before start

    // Start empty so we don't see a flash of full text before typing begins.
    textEl.textContent = '';

    var i = 0;
    function tick() {
      if (i >= full.length) return;
      // Append one character at a time.
      textEl.textContent += full.charAt(i);
      i++;
      setTimeout(tick, speed);
    }

    setTimeout(tick, delay);
  });

    /* ---------- Accordion ---------- */
  document.querySelectorAll('[data-accordion]').forEach(function (acc) {
    var single = acc.getAttribute('data-accordion') === 'single';
    var items  = acc.querySelectorAll('.accordion__item');

    items.forEach(function (item) {
      var trigger = item.querySelector('.accordion__trigger');
      var panel   = item.querySelector('.accordion__panel');
      if (!trigger || !panel) return;

      // Give each panel a unique id and link it to the trigger
      if (!panel.id) {
        panel.id = 'acc-panel-' + Math.random().toString(36).slice(2, 9);
      }
      trigger.setAttribute('aria-controls', panel.id);

      trigger.addEventListener('click', function () {
        var isOpen = trigger.getAttribute('aria-expanded') === 'true';

        // "single" mode = only one open at a time
        if (single) {
          items.forEach(function (other) {
            var t = other.querySelector('.accordion__trigger');
            var p = other.querySelector('.accordion__panel');
            if (t && p) {
              t.setAttribute('aria-expanded', 'false');
              p.setAttribute('data-open', 'false');
            }
          });
        }

        trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        panel.setAttribute('data-open', isOpen ? 'false' : 'true');
      });
    });
  });
})();