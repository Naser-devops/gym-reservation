/* Gym Reservation — UI behaviour + GSAP motion.
   Everything is progressive enhancement: forms, links and numbers work without this file. */
(function () {
  'use strict';
  var root = document.documentElement;
  root.classList.add('js');
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function $all(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
  function ready(fn) { if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }

  /* ---- Navigation (site + admin) ---- */
  function initNav() {
    var nav = document.querySelector('[data-nav]');
    var toggle = document.querySelector('[data-nav-toggle]');
    if (nav && toggle) {
      var setOpen = function (open) { nav.setAttribute('data-open', open ? 'true' : 'false'); toggle.setAttribute('aria-expanded', open ? 'true' : 'false'); };
      toggle.addEventListener('click', function () { setOpen(nav.getAttribute('data-open') !== 'true'); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
      $all('a', nav).forEach(function (a) { a.addEventListener('click', function () { setOpen(false); }); });
    }
    var siteNav = document.querySelector('.site-nav');
    if (siteNav) {
      var onScroll = function () { siteNav.classList.toggle('is-scrolled', window.scrollY > 20); };
      onScroll(); window.addEventListener('scroll', onScroll, { passive: true });
    }
    var shell = document.querySelector('.admin-shell');
    var aToggle = document.querySelector('[data-admin-toggle]');
    if (shell && aToggle) {
      var aSet = function (open) { shell.setAttribute('data-open', open ? 'true' : 'false'); aToggle.setAttribute('aria-expanded', open ? 'true' : 'false'); };
      aToggle.addEventListener('click', function () { aSet(shell.getAttribute('data-open') !== 'true'); });
      var scrim = document.querySelector('.admin-scrim');
      if (scrim) scrim.addEventListener('click', function () { aSet(false); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape') aSet(false); });
    }
  }

  /* ---- Flash messages: dismiss button + auto-hide ---- */
  function initFlash() {
    $all('.flash').forEach(function (el) {
      var close = document.createElement('button');
      close.type = 'button'; close.className = 'flash__close'; close.setAttribute('aria-label', 'Dismiss message'); close.textContent = '\u00d7';
      var hide = function () { el.style.transition = 'opacity .3s, transform .3s'; el.style.opacity = '0'; el.style.transform = 'translateY(-6px)'; setTimeout(function () { var w = el.closest('.flash-wrap') || el; w.style.display = 'none'; }, 320); };
      close.addEventListener('click', hide); el.appendChild(close);
      if (el.classList.contains('flash--success')) setTimeout(hide, 6500);
    });
  }

  /* ---- Password show/hide (type=button, never submits) ---- */
  function initPasswords() {
    $all('input[type=password][data-toggle]').forEach(function (input) {
      var wrap = document.createElement('div'); wrap.className = 'pw-wrap';
      input.parentNode.insertBefore(wrap, input); wrap.appendChild(input);
      var btn = document.createElement('button');
      btn.type = 'button'; btn.className = 'pw-toggle'; btn.textContent = 'Show'; btn.setAttribute('aria-label', 'Show password');
      btn.addEventListener('click', function () {
        var show = input.type === 'password'; input.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Hide' : 'Show'; btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      });
      wrap.appendChild(btn);
    });
  }

  /* ---- Confirmation dialog for forms marked data-confirm ----
     The form still posts to the same PHP endpoint; this only asks first. */
  function initConfirm() {
    var forms = $all('form[data-confirm]');
    if (!forms.length) return;
    var dlg = null, pending = null;
    function build() {
      dlg = document.createElement('dialog'); dlg.className = 'confirm'; dlg.setAttribute('aria-labelledby', 'confirm-title');
      dlg.innerHTML = '<h3 id="confirm-title"></h3><p class="muted"></p><div class="btn-row"><button type="button" class="btn btn--ghost btn--sm" data-no>Keep it</button><button type="button" class="btn btn--primary btn--sm" data-yes>Confirm</button></div>';
      document.body.appendChild(dlg);
      dlg.querySelector('[data-no]').addEventListener('click', function () { dlg.close(); pending = null; });
      dlg.querySelector('[data-yes]').addEventListener('click', function () { var f = pending; dlg.close(); pending = null; if (f) { f.setAttribute('data-confirmed', '1'); HTMLFormElement.prototype.submit.call(f); } });
    }
    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (form.getAttribute('data-confirmed') === '1') return;
        e.preventDefault();
        var msg = form.getAttribute('data-confirm') || 'Are you sure?';
        if (typeof HTMLDialogElement === 'undefined') { if (window.confirm(msg)) { form.setAttribute('data-confirmed', '1'); HTMLFormElement.prototype.submit.call(form); } return; }
        if (!dlg) build();
        pending = form;
        dlg.querySelector('h3').textContent = form.getAttribute('data-confirm-title') || 'Are you sure?';
        dlg.querySelector('p').textContent = msg;
        dlg.showModal();
      });
    });
  }

  /* ---- Capacity meters: animate from 0 to the server-rendered value ---- */
  function initMeters() {
    var bars = $all('.meter > i');
    if (!bars.length || reduce || !('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target; el.style.setProperty('--fill', el.getAttribute('data-fill')); io.unobserve(el);
      });
    }, { threshold: .4 });
    bars.forEach(function (el) {
      var final = el.style.getPropertyValue('--fill').trim();
      if (!final) return;
      el.setAttribute('data-fill', final); el.style.setProperty('--fill', '0%'); io.observe(el);
    });
  }

  /* ---- GSAP motion ---- */
  function initMotion() {
    if (reduce || !window.gsap) return;
    var gsap = window.gsap;
    var hasST = !!window.ScrollTrigger;
    if (hasST) gsap.registerPlugin(window.ScrollTrigger);

    var hero = $all('[data-hero]');
    if (hero.length) gsap.from(hero, { y: 46, opacity: 0, duration: 1.05, ease: 'power3.out', stagger: .12, delay: .1, clearProps: 'transform,opacity' });

    var pageHead = $all('.page-head > *, .admin-title > *, .auth-card > h1, .auth-card > .eyebrow, .auth-card > p');
    if (pageHead.length) gsap.from(pageHead, { y: 22, opacity: 0, duration: .8, ease: 'power3.out', stagger: .08, clearProps: 'transform,opacity' });

    function revealOpts(el) {
      var o = { y: 30, opacity: 0, duration: .85, ease: 'power3.out', clearProps: 'transform,opacity' };
      if (hasST) o.scrollTrigger = { trigger: el, start: 'top 90%', once: true };
      return o;
    }
    $all('[data-reveal]').forEach(function (el) { gsap.from(el, revealOpts(el)); });
    $all('[data-stagger]').forEach(function (group) {
      var kids = Array.prototype.slice.call(group.children);
      if (!kids.length) return;
      var o = revealOpts(group); o.stagger = .08; o.y = 26;
      gsap.from(kids, o);
    });

    $all('[data-count]').forEach(function (el) {
      var target = parseInt(el.getAttribute('data-count'), 10);
      if (isNaN(target)) return;
      var obj = { v: 0 };
      var cfg = { v: target, duration: 1.4, ease: 'power2.out', onUpdate: function () { el.textContent = Math.round(obj.v); }, onComplete: function () { el.textContent = target; } };
      if (hasST) cfg.scrollTrigger = { trigger: el, start: 'top 95%', once: true };
      el.textContent = '0';
      gsap.to(obj, cfg);
    });
  }

  ready(function () {
    try { initNav(); initFlash(); initPasswords(); initConfirm(); initMeters(); } catch (e) { if (window.console) console.error(e); }
    try { initMotion(); } catch (e) { if (window.console) console.error(e); }
  });
})();
