/**
 * SOUVERAIN CMS — JavaScript principal
 * Accessibilité + Navigation + Partage + UI
 */
const sv = {
  // ── ACCESSIBILITÉ ────────────────────────────────────────────────────
  fontSize(delta) {
    const root = document.documentElement;
    const current = parseFloat(getComputedStyle(root).fontSize);
    const next = Math.min(24, Math.max(12, current + delta * 2));
    root.style.setProperty('--font-size', next + 'px');
    localStorage.setItem('sv_fontsize', next);
  },
  toggleContrast() {
    document.body.classList.toggle('high-contrast');
    localStorage.setItem('sv_contrast', document.body.classList.contains('high-contrast') ? '1' : '0');
  },
  toggleDyslexia() {
    document.body.classList.toggle('dyslexia-font');
    localStorage.setItem('sv_dyslexia', document.body.classList.contains('dyslexia-font') ? '1' : '0');
  },
  resetA11y() {
    document.documentElement.style.removeProperty('--font-size');
    document.body.classList.remove('high-contrast', 'dyslexia-font');
    localStorage.removeItem('sv_fontsize');
    localStorage.removeItem('sv_contrast');
    localStorage.removeItem('sv_dyslexia');
  },
  openA11y() {
    const panel = document.getElementById('accessibility-panel');
    if (panel) { panel.hidden = false; panel.focus && panel.focus(); }
  },
  closeA11y() {
    const panel = document.getElementById('accessibility-panel');
    if (panel) panel.hidden = true;
  },
  loadA11y() {
    const fs = localStorage.getItem('sv_fontsize');
    if (fs) document.documentElement.style.setProperty('--font-size', fs + 'px');
    if (localStorage.getItem('sv_contrast') === '1') document.body.classList.add('high-contrast');
    if (localStorage.getItem('sv_dyslexia') === '1') document.body.classList.add('dyslexia-font');
  },
  // ── PARTAGE ──────────────────────────────────────────────────────────
  copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
      const btn = document.querySelector('.share-copy');
      if (btn) { btn.textContent = '✅ Lien copié !'; setTimeout(() => { btn.textContent = '🔗 Copier le lien'; }, 2000); }
    });
  },
  // ── NAVIGATION MOBILE ────────────────────────────────────────────────
  initNav() {
    const hamburger = document.getElementById('hamburger');
    const nav = document.getElementById('main-nav');
    if (!hamburger || !nav) return;
    const closeNav = () => {
      nav.classList.remove('open');
      hamburger.textContent = '☰';
      hamburger.setAttribute('aria-expanded', 'false');
    };
    hamburger.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = nav.classList.toggle('open');
      hamburger.textContent = open ? '✕' : '☰';
      hamburger.setAttribute('aria-expanded', String(open));
    });
    nav.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', closeNav);
    });
    document.addEventListener('click', (e) => {
      if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
        closeNav();
      }
    });
    window.addEventListener('resize', () => {
      if (window.innerWidth > 1100) closeNav();
    });
  },
  // ── INIT ─────────────────────────────────────────────────────────────
  init() {
    this.loadA11y();
    this.initNav();
    // Bouton accessibilité
    const a11yBtn = document.getElementById('accessibility-btn');
    if (a11yBtn) a11yBtn.addEventListener('click', () => {
      const panel = document.getElementById('accessibility-panel');
      if (panel) panel.hidden = !panel.hidden;
    });
    // Fermer panel au clic extérieur
    document.addEventListener('click', (e) => {
      const panel = document.getElementById('accessibility-panel');
      const btn = document.getElementById('accessibility-btn');
      if (panel && !panel.hidden && !panel.contains(e.target) && btn && !btn.contains(e.target)) {
        panel.hidden = true;
      }
    });
    // Lazy loading images
    if ('IntersectionObserver' in window) {
      const imgs = document.querySelectorAll('img[loading="lazy"]');
      const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { obs.unobserve(e.target); } });
      });
      imgs.forEach(img => obs.observe(img));
    }
    // Progression lecture article
    const article = document.querySelector('.article-content');
    if (article) {
      const bar = document.createElement('div');
      bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:linear-gradient(90deg,#002395,#ED2939);z-index:999;transition:width .1s;width:0';
      document.body.appendChild(bar);
      window.addEventListener('scroll', () => {
        const total = document.body.scrollHeight - window.innerHeight;
        bar.style.width = (window.scrollY / total * 100) + '%';
      });
    }
    // Smooth reveal au scroll
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.style.opacity = 1; e.target.style.transform = 'translateY(0)'; } });
    }, { threshold: 0.1 });
    document.querySelectorAll('.article-card, .revolution-card, .proposition').forEach(el => {
      el.style.cssText += 'opacity:0;transform:translateY(20px);transition:opacity .5s,transform .5s';
      observer.observe(el);
    });
  }
};
document.addEventListener('DOMContentLoaded', () => {
  sv.init();
  // SouveraineVoix — module audio accessibilité (chargement différé)
  const s = document.createElement('script');
  s.src = '/assets/js/audio-accessibility.js';
  s.defer = true;
  document.head.appendChild(s);
});
