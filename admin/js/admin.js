(function () {
  'use strict';
  if (typeof window.ADM === 'undefined') return;
  var CSRF = window.ADM.csrf;
  var API = 'admin/api.php';

  function toast(msg, isError) {
    var t = document.getElementById('admToast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'admToast';
      t.className = 'adm-toast';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.background = isError ? '#b3261e' : '#222';
    t.classList.add('show');
    clearTimeout(t._h);
    t._h = setTimeout(function () { t.classList.remove('show'); }, 2200);
  }

  function post(action, formDataExtra) {
    var fd = (formDataExtra instanceof FormData) ? formDataExtra : new FormData();
    fd.append('action', action);
    fd.append('csrf', CSRF);
    return fetch(API, { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.ok) { toast(data.error || 'Chyba', true); }
        return data;
      })
      .catch(function () { toast('Chyba spojení se serverem', true); return { ok: false }; });
  }

  /* ================= EDIT MODE TOGGLE ================= */
  var body = document.body;
  function applyMode() {
    var on = localStorage.getItem('trukra_edit_mode') !== 'off';
    body.classList.toggle('is-editing', on);
    var btn = document.getElementById('admModeBtn');
    if (btn) btn.textContent = on ? '✏️ Úpravy: zapnuty' : '👁 Náhled pro návštěvníky';
  }
  applyMode();

  document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'admModeBtn') {
      var on = localStorage.getItem('trukra_edit_mode') !== 'off';
      localStorage.setItem('trukra_edit_mode', on ? 'off' : 'on');
      applyMode();
    }
  });

  /* ================= MOBILNÍ ADMIN PANEL (rozbalovací FAB) ================= */
  var admBar = document.getElementById('admBar');
  var admBarToggle = document.getElementById('admBarToggle');
  if (admBar && admBarToggle) {
    admBarToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = admBar.classList.toggle('is-open');
      admBarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    // klik mimo panel ho zavře
    document.addEventListener('click', function (e) {
      if (admBar.classList.contains('is-open') && !admBar.contains(e.target)) {
        admBar.classList.remove('is-open');
        admBarToggle.setAttribute('aria-expanded', 'false');
      }
    });
    // po kliknutí na akci uvnitř panelu (kromě přepínače úprav) se panel sám zavře
    admBar.querySelector('.adm-bar__panel').addEventListener('click', function (e) {
      if (e.target && e.target.id !== 'admModeBtn') {
        admBar.classList.remove('is-open');
        admBarToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ================= TEXTOVÁ POLE (contenteditable) ================= */
  function bindEditable(el) {
    el.setAttribute('contenteditable', 'true');
    el.setAttribute('spellcheck', 'false');
    var original = el.innerHTML;
    el.addEventListener('focus', function () { original = el.innerHTML; });
    el.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && el.dataset.singleline === '1') { e.preventDefault(); el.blur(); }
    });
    el.addEventListener('blur', function () {
      if (el.innerHTML === original) return;
      var fd = new FormData();
      fd.append('value', el.innerHTML);
      var promise;
      if (el.dataset.key) {
        fd.append('key', el.dataset.key);
        promise = post('save_text', fd);
      } else if (el.dataset.table && el.dataset.id && el.dataset.field) {
        fd.append('table', el.dataset.table);
        fd.append('id', el.dataset.id);
        fd.append('field', el.dataset.field);
        promise = post('save_field', fd);
      } else return;
      promise.then(function (res) {
        if (res.ok) {
          el.classList.add('just-saved');
          setTimeout(function () { el.classList.remove('just-saved'); }, 700);
        } else {
          el.innerHTML = original;
        }
      });
    });
  }
  document.querySelectorAll('[data-editable="true"]').forEach(bindEditable);

  /* ================= HVĚZDIČKY U RECENZÍ ================= */
  document.querySelectorAll('[data-stars-id]').forEach(function (wrap) {
    wrap.querySelectorAll('span.star').forEach(function (star) {
      star.addEventListener('click', function () {
        var val = parseInt(star.dataset.v, 10);
        var fd = new FormData();
        fd.append('table', 'reviews'); fd.append('id', wrap.dataset.starsId);
        fd.append('field', 'stars'); fd.append('value', val);
        post('save_field', fd).then(function (res) {
          if (res.ok) {
            wrap.querySelectorAll('span.star').forEach(function (s2) {
              s2.classList.toggle('on', parseInt(s2.dataset.v, 10) <= val);
            });
          }
        });
      });
    });
  });

  /* ================= IKONY (select) ================= */
  document.querySelectorAll('.adm-icon-select').forEach(function (sel) {
    sel.addEventListener('change', function () {
      var table = sel.dataset.table, id = sel.dataset.id;
      var fd = new FormData();
      fd.append('table', table); fd.append('id', id);
      fd.append('field', 'icon'); fd.append('value', sel.value);
      post('save_field', fd).then(function (res) {
        if (res.ok) {
          var use = sel.closest('.card, .usp__i').querySelector('use');
          if (use) use.setAttribute('href', '#' + sel.value);
          toast('Ikona uložena');
        }
      });
    });
  });

  /* ================= OBRÁZKY NASTAVENÍ (hero pozadí, foto o nás…) ================= */
  document.querySelectorAll('.adm-img-btn[data-img-key]').forEach(function (btn) {
    var input = document.createElement('input');
    input.type = 'file'; input.accept = 'image/*'; input.style.display = 'none';
    btn.parentElement.appendChild(input);
    btn.addEventListener('click', function () { input.click(); });
    input.addEventListener('change', function () {
      if (!input.files[0]) return;
      var fd = new FormData();
      fd.append('key', btn.dataset.imgKey);
      fd.append('file', input.files[0]);
      toast('Nahrávám obrázek…');
      post('upload_setting_image', fd).then(function (res) {
        if (res.ok) {
          var img = btn.parentElement.querySelector('img');
          if (img) img.src = res.url + '?v=' + Date.now();
          var sources = btn.parentElement.parentElement ? btn.parentElement.parentElement.querySelectorAll('source') : [];
          toast('Obrázek uložen');
        } else {
          toast(res.error || 'Chyba nahrávání', true);
        }
      });
    });
  });

  /* ================= SEKCE — ZOBRAZIT / SKRÝT ================= */
  document.querySelectorAll('.adm-switch input[data-section-key]').forEach(function (input) {
    input.addEventListener('change', function () {
      var key = input.dataset.sectionKey;
      var fd = new FormData();
      fd.append('key', key);
      fd.append('visible', input.checked ? '1' : '0');
      post('toggle_section', fd).then(function (res) {
        if (res.ok) {
          var sec = document.querySelector('[data-section="' + key + '"]');
          if (sec) sec.classList.toggle('adm-section-off', !input.checked);
          toast(input.checked ? 'Sekce zobrazena návštěvníkům' : 'Sekce skryta návštěvníkům');
        } else {
          input.checked = !input.checked;
        }
      });
    });
  });

  /* ================= PŘIDAT / SMAZAT POLOŽKU (services, steps, faq, usp, stats) ================= */
  document.querySelectorAll('[data-add-table]').forEach(function (tile) {
    tile.addEventListener('click', function () {
      var fd = new FormData();
      fd.append('table', tile.dataset.addTable);
      post('add_item', fd).then(function (res) {
        if (res.ok) { location.reload(); } // nová položka se vykreslí serverem
      });
    });
  });
  document.querySelectorAll('.adm-del-btn[data-del-table]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault(); e.stopPropagation();
      if (!confirm('Opravdu smazat tuto položku?')) return;
      var fd = new FormData();
      fd.append('table', btn.dataset.delTable);
      fd.append('id', btn.dataset.delId);
      post('delete_item', fd).then(function (res) {
        if (res.ok) { location.reload(); }
      });
    });
  });

  /* ================= GALERIE: "+" a mazání ================= */
  var galModal = document.getElementById('galModal');
  var galAddTile = document.getElementById('galAddTile');
  if (galAddTile && galModal) {
    galAddTile.addEventListener('click', function () { galModal.classList.add('show'); });
    galModal.querySelector('.btn-cancel').addEventListener('click', function () { galModal.classList.remove('show'); });
    galModal.addEventListener('click', function (e) { if (e.target === galModal) galModal.classList.remove('show'); });

    galModal.querySelector('.btn-ok').addEventListener('click', function () {
      var fileInput = galModal.querySelector('#galFile');
      if (!fileInput.files.length) { toast('Vyberte alespoň jeden soubor', true); return; }
      var fd = new FormData();
      fd.append('category', galModal.querySelector('#galCategory').value);
      fd.append('title', galModal.querySelector('#galTitle').value);
      fd.append('subtitle', galModal.querySelector('#galSubtitle').value);
      for (var i = 0; i < fileInput.files.length; i++) {
        fd.append('files[]', fileInput.files[i]);
      }
      var n = fileInput.files.length;
      toast(n > 1 ? 'Nahrávám ' + n + ' souborů…' : 'Nahrávám referenci…');
      post('gallery_add', fd).then(function (res) {
        if (res.ok) {
          if (res.errors && res.errors.length) {
            toast('Nahráno ' + res.added + ' z ' + n + ', chyby: ' + res.errors.join('; '), true);
            setTimeout(function () { location.reload(); }, 2500);
          } else {
            location.reload();
          }
        } else {
          toast(res.error || 'Chyba nahrávání', true);
        }
      });
    });
  }

  document.querySelectorAll('.gal__del').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault(); e.stopPropagation();
      if (!confirm('Smazat tuto referenci z galerie?')) return;
      var fd = new FormData();
      fd.append('id', btn.dataset.id);
      post('gallery_delete', fd).then(function (res) {
        if (res.ok) { location.reload(); }
      });
    });
  });

})();
