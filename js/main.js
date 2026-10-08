/* ==========================================================
   Truhlářství Kratochvíl — interakce (bez knihoven)
   Vše je v blocích safe(), aby případná chyba v jedné části
   nikdy neshodila zbytek stránky.
   ========================================================== */
(function () {
  'use strict';

  var $  = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  function safe(name, fn) { try { fn(); } catch (e) { if (window.console) console.warn('[' + name + ']', e); } }

  /* ---------- Rok v patičce ---------- */
  safe('rok', function () {
    var el = $('#rok');
    if (el) el.textContent = new Date().getFullYear();
  });

  /* ---------- Lišta s upozorněním ---------- */
  safe('announce', function () {
    var bar = $('#announceBar');
    if (!bar) return;
    var key = 'trukra_announce_dismissed';
    var msgKey = bar.getAttribute('data-msg-key') || '';
    try {
      if (localStorage.getItem(key) === msgKey) bar.classList.add('is-dismissed');
    } catch (e) { /* localStorage nedostupný, lišta zůstane vidět */ }

    var xBtn = $('#announceClose');
    if (xBtn) {
      xBtn.addEventListener('click', function () {
        bar.classList.add('is-dismissed');
        try { localStorage.setItem(key, msgKey); } catch (e) {}
      });
    }
  });

  /* ---------- MOBILNÍ MENU ---------- */
  var closeMenu = function () {};

  safe('menu', function () {
    var burger = $('#burger');
    var menu   = $('#menu');
    var scrim  = $('#scrim');
    var xBtn   = $('#menuClose');
    if (!burger || !menu || !scrim) return;

    var lastFocus = null;

    function open() {
      lastFocus = document.activeElement;
      document.body.classList.add('menu-open');
      burger.setAttribute('aria-expanded', 'true');
      burger.setAttribute('aria-label', 'Zavřít menu');
      setTimeout(function () { if (xBtn) xBtn.focus(); }, 240);
    }

    closeMenu = function () {
      if (!document.body.classList.contains('menu-open')) return;
      document.body.classList.remove('menu-open');
      burger.setAttribute('aria-expanded', 'false');
      burger.setAttribute('aria-label', 'Otevřít menu');
      if (lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch (e) {} }
    };

    burger.addEventListener('click', function (e) {
      e.preventDefault();
      document.body.classList.contains('menu-open') ? closeMenu() : open();
    });

    if (xBtn) xBtn.addEventListener('click', closeMenu);
    scrim.addEventListener('click', closeMenu);

    // klik na odkaz v menu zavře menu; odkazy na sociální sítě
    // se otevírají v nové záložce, menu se přesto zavře
    $$('a', menu).forEach(function (a) { a.addEventListener('click', closeMenu); });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 1080) closeMenu();
    });

    menu.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab') return;
      var f = $$('a[href], button', menu);
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
  });

  /* ---------- Sticky hlavička + tlačítko nahoru ---------- */
  safe('scroll', function () {
    var hdr = $('#hdr'), totop = $('#totop');
    function onScroll() {
      var y = window.pageYOffset || document.documentElement.scrollTop || 0;
      if (hdr) hdr.classList.toggle('is-stuck', y > 8);
      if (totop) totop.classList.toggle('is-on', y > 550);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  });

  /* ---------- Odhalování obsahu ---------- */
  safe('reveal', function () {
    var els = $$('.reveal');
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('is-in'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en, i) {
        if (!en.isIntersecting) return;
        var el = en.target;
        setTimeout(function () { el.classList.add('is-in'); }, (i % 4) * 80);
        io.unobserve(el);
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
    els.forEach(function (el) { io.observe(el); });
  });

  /* ---------- Aktivní položka menu ---------- */
  safe('spy', function () {
    var sections = $$('main section[id]');
    var links = $$('.nav__l, .menu__nav a');
    if (!sections.length || !links.length || !('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var href = '#' + en.target.id;
        links.forEach(function (l) { l.classList.toggle('is-on', l.getAttribute('href') === href); });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    sections.forEach(function (s) { io.observe(s); });
  });

  /* ---------- Počítadla ---------- */
  safe('counter', function () {
    var nums = $$('[data-count]');
    if (!nums.length) return;
    if (!('IntersectionObserver' in window)) {
      nums.forEach(function (n) { n.textContent = n.getAttribute('data-count'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target;
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        var t0 = null, dur = 1300;
        function step(ts) {
          if (t0 === null) t0 = ts;
          var p = Math.min((ts - t0) / dur, 1);
          el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('cs-CZ');
          if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
        io.unobserve(el);
      });
    }, { threshold: 0.4 });
    nums.forEach(function (n) { io.observe(n); });
  });

  /* ---------- Filtr referencí + lightbox ---------- */
  safe('gallery', function () {
    var items = $$('.gal__i');
    if (!items.length) return;

    var galBox = $('#gal');
    $$('.filter').forEach(function (btn) {
      btn.addEventListener('click', function () {
        $$('.filter').forEach(function (b) { b.classList.remove('is-on'); });
        btn.classList.add('is-on');
        var f = btn.getAttribute('data-f');
        if (galBox) galBox.classList.toggle('is-filtered', f !== '*');
        items.forEach(function (it) {
          it.classList.toggle('is-off', !(f === '*' || it.getAttribute('data-cat') === f));
        });
      });
    });

    var lb = $('#lb'), lbImg = $('#lbImg'), lbVideo = $('#lbVideo'), lbCap = $('#lbCap'), idx = 0;
    if (!lb || !lbImg) return;

    function visible() {
      return items.filter(function (i) { return !i.classList.contains('is-off'); });
    }
    function show(i) {
      var list = visible();
      if (!list.length) return;
      idx = (i + list.length) % list.length;
      var fig = list[idx];
      var isVideo = fig.getAttribute('data-type') === 'video';
      var s = $('figcaption strong', fig), t = $('figcaption span', fig);
      lbCap.textContent = (s ? s.textContent : '') + (t ? ' — ' + t.textContent : '');
      if (isVideo && lbVideo) {
        var vid = $('video', fig);
        lbImg.style.display = 'none';
        lbVideo.style.display = '';
        lbVideo.src = vid ? vid.currentSrc || vid.src : '';
        lbVideo.load();
      } else {
        if (lbVideo) { lbVideo.pause(); lbVideo.removeAttribute('src'); lbVideo.style.display = 'none'; }
        var img = $('img', fig);
        lbImg.style.display = '';
        lbImg.src = img.src;
        lbImg.alt = img.alt || '';
      }
    }
    function openLb(i) {
      show(i);
      lb.classList.add('is-on');
      lb.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
    function closeLb() {
      lb.classList.remove('is-on');
      lb.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      if (lbVideo) lbVideo.pause();
      setTimeout(function () { lbImg.src = ''; }, 300);
    }

    items.forEach(function (fig) {
      fig.addEventListener('click', function () { openLb(visible().indexOf(fig)); });
      fig.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openLb(visible().indexOf(fig)); }
      });
    });

    var x = $('#lbClose'), p = $('#lbPrev'), n = $('#lbNext');
    if (x) x.addEventListener('click', closeLb);
    if (p) p.addEventListener('click', function () { show(idx - 1); });
    if (n) n.addEventListener('click', function () { show(idx + 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) closeLb(); });

    var sx = 0;
    lb.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - sx;
      if (Math.abs(dx) > 50) show(dx > 0 ? idx - 1 : idx + 1);
    });

    document.addEventListener('keydown', function (e) {
      if (!lb.classList.contains('is-on')) return;
      if (e.key === 'Escape') closeLb();
      if (e.key === 'ArrowLeft') show(idx - 1);
      if (e.key === 'ArrowRight') show(idx + 1);
    });

    window.__lbOpen = function () { return lb.classList.contains('is-on'); };
  });

  /* ---------- Esc zavírá menu ---------- */
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (window.__lbOpen && window.__lbOpen()) return;
    closeMenu();
  });

  /* ---------- Odeslání poptávky ---------- */
  safe('form', function () {
    var form = $('#form'), msg = $('#formMsg');
    if (!form || !window.fetch) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      msg.className = 'form__m';
      msg.textContent = '';

      if (!form.checkValidity()) { form.reportValidity(); return; }

      var btn = form.querySelector('button[type="submit"]');
      var orig = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Odesílám…';

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'fetch' }
      })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (!d || !d.ok) throw new Error('err');
          msg.className = 'form__m ok';
          msg.textContent = 'Děkujeme! Poptávku máme a ozveme se do 24 hodin.';
          form.reset();
        })
        .catch(function () {
          msg.className = 'form__m err';
          msg.innerHTML = 'Odeslání se nezdařilo. Zavolejte prosím na <a href="tel:+420776594326">776 594 326</a> nebo napište na <a href="mailto:trukra@seznam.cz">trukra@seznam.cz</a>.';
        })
        .then(function () {
          btn.disabled = false;
          btn.textContent = orig;
        });
    });
  });
})();
