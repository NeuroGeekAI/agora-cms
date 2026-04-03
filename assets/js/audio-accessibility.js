/**
 * ═══════════════════════════════════════════════════════════════
 * SOUVERAIN CMS — SouveraineVoix™
 * Module d'accessibilité audio innovant — Première mondiale
 * Web Speech API + Web Audio API — Zéro dépendance externe
 *
 * FONCTIONNALITÉS UNIQUES :
 *  - Lecture au survol (500ms) avec contexte sémantique
 *  - Tones sonores différents selon le type d'élément (H1, lien, bouton...)
 *  - Waveform animée en temps réel
 *  - Mode Audiobook : lit toute la page automatiquement
 *  - Narration au scroll (IntersectionObserver)
 *  - Navigation clavier : Tab → lecture automatique
 *  - Résumé de page à l'activation
 *  - Vitesse réglable (0.5x → 2x)
 *  - Raccourci Alt+A
 *  - Préférences persistantes (localStorage)
 * ═══════════════════════════════════════════════════════════════
 */
const SouveraineVoix = (() => {
    // ── État ─────────────────────────────────────────────────────────────
    const S = {
        on:        false,
        book:      false,
        rate:      parseFloat(localStorage.getItem('sv_vrate') || '1.0'),
        voiceFR:   null,
        hoverT:    null,
        scrollObs: null,
        bookQueue: [],
        bookIdx:   0,
    };
    const synth = window.speechSynthesis;
    let audioCtx = null;
    // ── Voix française ───────────────────────────────────────────────────
    function loadVoices() {
        const vs = synth.getVoices();
        S.voiceFR = vs.find(v => v.lang === 'fr-FR')
                 || vs.find(v => v.lang.startsWith('fr'))
                 || vs[0];
    }
    if (synth.onvoiceschanged !== undefined) synth.onvoiceschanged = loadVoices;
    loadVoices();
    // ── TTS principal ─────────────────────────────────────────────────────
    function speak(text, priority = false) {
        if (!synth || !text?.trim()) return;
        if (priority) synth.cancel();
        const u = new SpeechSynthesisUtterance(text);
        if (S.voiceFR) u.voice = S.voiceFR;
        u.rate  = S.rate;
        u.lang  = 'fr-FR';
        u.pitch = 1.05;
        u.onstart = () => HUD.show(text);
        u.onend   = () => HUD.hide();
        u.onerror = () => HUD.hide();
        synth.speak(u);
    }
    function stop() {
        synth.cancel();
        HUD.hide();
        hlClear();
    }
    // ── Descriptions sémantiques contextuelles ────────────────────────────
    const TAGS_H = { h1:'Titre principal', h2:'Section', h3:'Sous-section', h4:'Point', h5:'Détail', h6:'Note' };
    const MAX = 260;
    function cut(t) { return t.length > MAX ? t.substring(0, MAX) + '…' : t; }
    function desc(el) {
        if (!el?.tagName) return null;
        const tag  = el.tagName.toLowerCase();
        const text = (el.innerText || el.textContent || '').trim().replace(/\s+/g, ' ');
        // Titres
        if (TAGS_H[tag])  return `${TAGS_H[tag]} : ${cut(text)}`;
        // Liens
        if (tag === 'a' && text) {
            const ext = (el.getAttribute('href') || '').startsWith('http');
            return `Lien${ext ? ' externe' : ''} : ${cut(text)}`;
        }
        // Images
        if (tag === 'img') {
            const alt = el.getAttribute('alt');
            return alt ? `Image : ${cut(alt)}` : 'Image décorative sans description';
        }
        // Boutons
        if (tag === 'button' || el.getAttribute('role') === 'button') return `Bouton : ${cut(text)}`;
        // Champs
        if (tag === 'input') {
            const lbl = el.getAttribute('aria-label') || el.getAttribute('placeholder') || el.name || '';
            return `Champ ${el.type || 'texte'} : ${lbl}`;
        }
        // Chiffres choc, stats
        if (el.classList?.contains('chiffre-num') || el.classList?.contains('unix-stat')) return `Statistique : ${cut(text)}`;
        // Slogans
        if (el.classList?.contains('slogan-text') || el.id === 'slogan-text') return `Slogan : ${cut(text)}`;
        // Paragraphes et listes
        if (['p','li','td','th','label','time'].includes(tag) && text.length > 2) return cut(text);
        return null;
    }
    // ── Web Audio API — Tones harmoniques ────────────────────────────────
    // Chaque type d'élément a sa propre signature sonore (note musicale)
    const TONES = {
        heading: { f:523.25, t:'sine',     d:0.18, v:0.12 }, // Do5 — solennel
        link:    { f:880,    t:'sine',     d:0.07, v:0.09 }, // La5 — vif
        button:  { f:659.25, t:'triangle', d:0.11, v:0.10 }, // Mi5 — décisionnel
        image:   { f:392,    t:'sine',     d:0.22, v:0.08 }, // Sol4 — doux
        stat:    { f:440,    t:'sawtooth', d:0.14, v:0.07 }, // La4 — impact
        section: { f:349.23, t:'sine',     d:0.28, v:0.07 }, // Fa4 — transition
    };
    function tone(type) {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const cfg = TONES[type] || TONES.section;
            const osc  = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            // Filtre passe-bas pour un son doux (pas agressif)
            const flt  = audioCtx.createBiquadFilter();
            flt.type = 'lowpass';
            flt.frequency.value = 2000;
            osc.connect(flt);
            flt.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = cfg.t;
            osc.frequency.setValueAtTime(cfg.f, audioCtx.currentTime);
            gain.gain.setValueAtTime(cfg.v, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + cfg.d);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + cfg.d);
        } catch (e) {}
    }
    function toneOf(el) {
        const tag = el.tagName?.toLowerCase() || '';
        if (/^h[1-6]$/.test(tag)) return 'heading';
        if (tag === 'a') return 'link';
        if (tag === 'button') return 'button';
        if (tag === 'img') return 'image';
        if (el.classList?.contains('chiffre-num') || el.classList?.contains('unix-stat')) return 'stat';
        return null;
    }
    // ── Waveform HUD animé ────────────────────────────────────────────────
    const HUD = {
        el: null,
        raf: null,
        bars: [],
        create() {
            const d = document.createElement('div');
            d.id = 'sv-hud';
            d.setAttribute('aria-live', 'polite');
            d.setAttribute('role', 'status');
            d.innerHTML =
                '<div class="sv-hud-left">'
              +   '<span class="sv-hud-icon">🔊</span>'
              +   '<div class="sv-hud-bars">'
              +     Array(8).fill('<span class="sv-hud-bar"></span>').join('')
              +   '</div>'
              + '</div>'
              + '<span class="sv-hud-txt"></span>'
              + '<button class="sv-hud-stop" onclick="SouveraineVoix.stop()" title="Arrêter (Échap)">✕</button>';
            document.body.appendChild(d);
            this.el   = d;
            this.bars = [...d.querySelectorAll('.sv-hud-bar')];
        },
        show(text) {
            if (!this.el) return;
            this.el.querySelector('.sv-hud-txt').textContent =
                text.length > 70 ? text.substring(0, 70) + '…' : text;
            this.el.classList.add('sv-hud-on');
            this._anim();
        },
        hide() {
            if (!this.el) return;
            this.el.classList.remove('sv-hud-on');
            if (this.raf) { cancelAnimationFrame(this.raf); this.raf = null; }
            this.bars.forEach(b => b.style.height = '3px');
        },
        _anim() {
            const step = () => {
                if (!this.el?.classList.contains('sv-hud-on')) return;
                this.bars.forEach((b, i) => {
                    const h = 3 + Math.sin(Date.now() / 120 + i * 0.9) * 10 + Math.random() * 8;
                    b.style.height = Math.max(3, h) + 'px';
                });
                this.raf = requestAnimationFrame(step);
            };
            if (this.raf) cancelAnimationFrame(this.raf);
            step();
        },
    };
    // ── Highlight visuel de l'élément en cours de lecture ────────────────
    function hlSet(el)   { hlClear(); if (el) el.classList.add('sv-reading'); }
    function hlClear()   { document.querySelectorAll('.sv-reading').forEach(e => e.classList.remove('sv-reading')); }
    // ── Hover listeners ───────────────────────────────────────────────────
    const SEL = 'h1,h2,h3,h4,h5,h6,p,a,button,img[alt],li,.slogan-text,.chiffre-num,.project-name,.unix-stat,.article-title,label,time,blockquote';
    function onEnter(e) {
        if (!S.on) return;
        clearTimeout(S.hoverT);
        S.hoverT = setTimeout(() => {
            const d = desc(e.target);
            if (!d) return;
            const t = toneOf(e.target);
            if (t) tone(t);
            speak(d, true);
            hlSet(e.target);
        }, 500);
    }
    function onLeave() { clearTimeout(S.hoverT); }
    function attachHover() {
        document.querySelectorAll(SEL).forEach(el => {
            el.addEventListener('mouseenter', onEnter);
            el.addEventListener('mouseleave', onLeave);
        });
    }
    function detachHover() {
        clearTimeout(S.hoverT);
        document.querySelectorAll(SEL).forEach(el => {
            el.removeEventListener('mouseenter', onEnter);
            el.removeEventListener('mouseleave', onLeave);
        });
        hlClear();
    }
    // ── Navigation clavier (Tab) ─────────────────────────────────────────
    function onFocusIn(e) {
        if (!S.on) return;
        const d = desc(e.target);
        if (!d) return;
        const t = toneOf(e.target);
        if (t) tone(t);
        speak(d, true);
        hlSet(e.target);
    }
    // ── Narration au scroll ───────────────────────────────────────────────
    // Quand une nouvelle section entre dans le viewport → ton + annonce titre
    function initScroll() {
        if (S.scrollObs) S.scrollObs.disconnect();
        S.scrollObs = new IntersectionObserver((entries) => {
            if (!S.on || synth.speaking) return;
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const h = entry.target.querySelector('h2,h3');
                if (h) { tone('section'); speak(desc(h) || h.innerText, false); }
            });
        }, { threshold: 0.75 });
        document.querySelectorAll('section,.handicap-section,.revolution-card').forEach(el => S.scrollObs.observe(el));
    }
    // ── Mode Audiobook ────────────────────────────────────────────────────
    // Lit toute la page du haut en bas comme un livre audio, avec scroll auto
    function bookStart() {
        S.book  = true;
        S.bookQueue = [...document.querySelectorAll('h1,h2,h3,p,li')].filter(el => {
            const t = (el.innerText || '').trim();
            return t.length > 8
                && !el.closest('#sv-hud')
                && !el.closest('.site-header')
                && !el.closest('.accessibility-panel')
                && !el.closest('style,script');
        });
        S.bookIdx = 0;
        speak('Mode livre audio activé. Lecture de la page en cours.', true);
        setTimeout(bookNext, 1600);
        bookUI(true);
    }
    function bookNext() {
        if (!S.book || S.bookIdx >= S.bookQueue.length) { bookStop(); return; }
        const el = S.bookQueue[S.bookIdx++];
        const d  = desc(el);
        if (!d) { bookNext(); return; }
        hlSet(el);
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const t = toneOf(el);
        if (t) tone(t);
        const u = new SpeechSynthesisUtterance(d);
        if (S.voiceFR) u.voice = S.voiceFR;
        u.rate = S.rate;
        u.lang = 'fr-FR';
        u.onstart = () => HUD.show(d);
        u.onend   = () => { HUD.hide(); setTimeout(bookNext, 250); };
        u.onerror = () => { HUD.hide(); setTimeout(bookNext, 250); };
        synth.speak(u);
    }
    function bookStop() {
        S.book = false;
        synth.cancel();
        HUD.hide();
        hlClear();
        bookUI(false);
    }
    function bookUI(active) {
        const btn = document.getElementById('sv-book-btn');
        if (!btn) return;
        btn.textContent = active ? '⏹ Arrêter' : '📖 Audiobook';
        btn.classList.toggle('sv-btn-on', active);
    }
    // ── Résumé de page ────────────────────────────────────────────────────
    function summary() {
        const h1 = document.querySelector('h1')?.innerText || document.title;
        const s2 = document.querySelectorAll('h2').length;
        const ar = document.querySelectorAll('.article-card, article').length;
        let s = `Bienvenue. Page : ${h1}. `;
        if (s2) s += `${s2} section${s2 > 1 ? 's' : ''}. `;
        if (ar) s += `${ar} article${ar > 1 ? 's' : ''}. `;
        s += 'Passez la souris sur n\'importe quel texte pour l\'entendre, ou utilisez la touche Tab pour naviguer.';
        return s;
    }
    // ── Injection UI dans le panel accessibilité ──────────────────────────
    function buildUI() {
        const opts = document.querySelector('.a11y-options');
        if (!opts) return;
        // Bouton Audio On/Off
        const vBtn = document.createElement('button');
        vBtn.id = 'sv-voice-btn';
        vBtn.textContent = '🔊 Audio';
        vBtn.title = 'Lecture audio au survol (Alt+A)';
        vBtn.setAttribute('aria-pressed', 'false');
        vBtn.addEventListener('click', toggle);
        opts.appendChild(vBtn);
        // Bouton Audiobook
        const bBtn = document.createElement('button');
        bBtn.id = 'sv-book-btn';
        bBtn.textContent = '📖 Audiobook';
        bBtn.title = 'Lire toute la page du début à la fin';
        bBtn.addEventListener('click', () => { if (S.book) { bookStop(); } else { if (!S.on) toggle(); bookStart(); } });
        opts.appendChild(bBtn);
        // Curseur vitesse — s'insère dans le panel sous les boutons
        const inner = opts.closest('.a11y-inner');
        if (!inner) return;
        const speedWrap = document.createElement('div');
        speedWrap.className = 'sv-speed';
        speedWrap.innerHTML =
            '<label class="sv-speed-label" for="sv-speed-range">⚡ Vitesse voix</label>'
          + '<div class="sv-speed-ctrl">'
          +   '<input id="sv-speed-range" type="range" min="0.5" max="2.0" step="0.1" value="' + S.rate + '">'
          +   '<span id="sv-speed-val">' + S.rate.toFixed(1) + 'x</span>'
          + '</div>';
        speedWrap.querySelector('#sv-speed-range').addEventListener('input', e => {
            S.rate = parseFloat(e.target.value);
            speedWrap.querySelector('#sv-speed-val').textContent = S.rate.toFixed(1) + 'x';
            localStorage.setItem('sv_vrate', S.rate);
        });
        const closeBtn = inner.querySelector('.a11y-close');
        if (closeBtn) inner.insertBefore(speedWrap, closeBtn);
        else inner.appendChild(speedWrap);
    }
    // ── Toggle principal ──────────────────────────────────────────────────
    function toggle() {
        S.on = !S.on;
        localStorage.setItem('sv_voice', S.on ? '1' : '0');
        if (S.on) {
            attachHover();
            document.addEventListener('focusin', onFocusIn);
            initScroll();
            speak(summary(), true);
        } else {
            detachHover();
            document.removeEventListener('focusin', onFocusIn);
            if (S.scrollObs) S.scrollObs.disconnect();
            if (S.book) bookStop();
            stop();
        }
        const btn = document.getElementById('sv-voice-btn');
        if (btn) {
            btn.textContent = S.on ? '🔇 Audio Off' : '🔊 Audio';
            btn.classList.toggle('sv-btn-on', S.on);
            btn.setAttribute('aria-pressed', S.on);
        }
    }
    // ── Init ──────────────────────────────────────────────────────────────
    function init() {
        if (!('speechSynthesis' in window)) return;
        const ready = () => {
            HUD.create();
            buildUI();
            if (localStorage.getItem('sv_voice') === '1') toggle();
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
        else ready();
        // Raccourcis clavier
        document.addEventListener('keydown', e => {
            if (e.altKey && e.key.toLowerCase() === 'a') { e.preventDefault(); toggle(); }
            if (e.key === 'Escape') { stop(); if (S.book) bookStop(); }
        });
    }
    return { init, toggle, stop, speak };
})();
SouveraineVoix.init();
