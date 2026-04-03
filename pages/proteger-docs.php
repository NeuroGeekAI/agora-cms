<?php
/**
 * AgoraCMS — Outil Filigrane / Protection Documents
 * 100% CLIENT-SIDE — JavaScript Canvas API — ZÉRO DONNÉES ENVOYÉES AU SERVEUR
 * Vos documents ne quittent jamais votre appareil.
 */
define('AGORA', true);
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
check_maintenance();
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Protéger vos Documents — Filigrane Gratuit — <?= h(SITE_NAME) ?></title>
<meta name="description" content="Ajoutez un filigrane à vos documents pour éviter le vol et les abus. 100% local, rien n'est envoyé sur internet. Outil gratuit et souverain.">
<link rel="canonical" href="<?= ag_canonical('proteger-documents/') ?>">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.tool-hero{background:linear-gradient(135deg,#0a1628 0%,#0d1117 50%,#0a2818 100%);padding:70px 0;border-bottom:1px solid var(--border);text-align:center}
.privacy-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(5,150,105,.12);border:1px solid rgba(5,150,105,.35);color:#34d399;font-size:0.83rem;font-weight:700;padding:8px 18px;border-radius:999px;margin-bottom:20px}
.tool-layout{display:grid;grid-template-columns:1fr 1fr;gap:32px;max-width:1200px;margin:40px auto;padding:0 20px}
.tool-panel{background:var(--dark2);border:1px solid var(--border);border-radius:20px;padding:28px}
.panel-title{font-size:1rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text2);margin-bottom:24px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.field{margin-bottom:18px}
label{display:block;font-size:0.83rem;color:var(--text2);margin-bottom:7px;font-weight:600}
input[type=text],input[type=range],select{width:100%;background:var(--dark);border:1px solid var(--border);color:var(--text);padding:11px 14px;border-radius:10px;font-size:0.9rem;outline:none;transition:.2s;font-family:inherit}
input:focus,select:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(5,150,105,.15)}
.color-row{display:flex;gap:10px;align-items:center}
.color-btn{width:36px;height:36px;border-radius:8px;border:2px solid transparent;cursor:pointer;transition:.2s;flex-shrink:0}
.color-btn:hover,.color-btn.active{border-color:white;transform:scale(1.15)}
input[type=color]{width:44px;height:36px;border-radius:8px;border:2px solid var(--border);cursor:pointer;background:none;padding:2px}
.range-row{display:flex;align-items:center;gap:12px}
input[type=range]{padding:0;height:6px;accent-color:var(--green)}
.range-val{min-width:40px;text-align:right;font-size:0.88rem;color:#34d399;font-weight:700}
.upload-zone{border:2px dashed var(--border);border-radius:16px;padding:48px 20px;text-align:center;transition:.3s;cursor:pointer;position:relative}
.upload-zone:hover,.upload-zone.drag-over{border-color:var(--green);background:rgba(5,150,105,.05)}
.upload-zone input{position:absolute;inset:0;opacity:0;cursor:pointer}
.upload-icon{font-size:3rem;display:block;margin-bottom:12px}
.upload-label{color:var(--text2);font-size:0.95rem}
.upload-label strong{color:white}
.upload-types{font-size:0.78rem;color:var(--text2);margin-top:8px}
.canvas-wrap{background:#1a1a2e;border-radius:16px;overflow:auto;min-height:300px;display:flex;align-items:center;justify-content:center;position:relative}
#preview-canvas{max-width:100%;border-radius:8px;display:block}
.canvas-placeholder{text-align:center;color:var(--text2);padding:40px}
.canvas-placeholder .ph-icon{font-size:4rem;display:block;margin-bottom:14px}
.actions-bar{display:flex;gap:12px;flex-wrap:wrap;margin-top:20px}
.btn-dl{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:linear-gradient(135deg,var(--green),#059669);color:white;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:.2s}
.btn-dl:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(5,150,105,.35)}
.btn-dl:disabled{opacity:.4;cursor:not-allowed;transform:none}
.btn-reset{display:inline-flex;align-items:center;gap:8px;padding:14px 24px;background:var(--dark3);border:1px solid var(--border);color:var(--text);border-radius:12px;font-size:0.9rem;font-weight:600;cursor:pointer;transition:.2s}
.btn-reset:hover{border-color:var(--rouge);color:#fca5a5}
.preset-btn{display:inline-block;background:var(--dark3);border:1px solid var(--border);color:var(--text2);padding:6px 14px;border-radius:8px;font-size:0.8rem;cursor:pointer;margin-right:6px;margin-bottom:6px;transition:.2s}
.preset-btn:hover{border-color:var(--bleu);color:#93c5fd}
.privacy-box{background:rgba(5,150,105,.06);border:1px solid rgba(5,150,105,.25);border-radius:14px;padding:20px;margin-bottom:24px}
.privacy-box h3{color:#34d399;font-size:0.95rem;font-weight:700;margin-bottom:10px}
.privacy-box ul{list-style:none;padding:0}
.privacy-box li{color:var(--text2);font-size:0.85rem;padding:4px 0;display:flex;align-items:center;gap:8px}
.privacy-box li::before{content:'✓';color:#34d399;font-weight:700}
.tips-section{background:var(--dark2);border:1px solid var(--border);border-radius:20px;padding:28px;max-width:1200px;margin:0 auto 40px;padding-left:calc(20px + 1vw)}
.tips-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin-top:16px}
.tip-card{background:var(--dark3);border:1px solid var(--border);border-radius:12px;padding:18px}
.tip-card h4{font-size:0.9rem;font-weight:700;color:white;margin-bottom:6px}
.tip-card p{font-size:0.82rem;color:var(--text2)}
@media(max-width:900px){.tool-layout{grid-template-columns:1fr}}
</style>
</head>
<body>
<header class="site-header">
  <div class="header-top"><div class="tricolor-bar"><span></span><span></span><span></span></div></div>
  <div class="container header-inner">
    <a href="/" class="site-logo"><span class="logo-icon">🏛️</span><div><div class="logo-name"><?= h(SITE_NAME) ?></div><div class="logo-tagline"><?= h(SITE_TAGLINE) ?></div></div></a>
    <nav class="main-nav" id="main-nav">
      <a href="/" class="nav-link">Accueil</a>
      <a href="/manifeste/" class="nav-link">🔥 Manifeste</a>
      <a href="/lois/" class="nav-link">⚖️ Lois</a>
      <a href="/debats/" class="nav-link">💬 Débats</a>
      <a href="/proteger-documents/" class="nav-link active">🔒 Docs</a>
      <a href="/handicap/" class="nav-link nav-handicap">♿ Handicap</a>
    </nav>
    <div class="header-actions">
      <button class="accessibility-btn" id="accessibility-btn">♿</button>
      <button class="hamburger" id="hamburger">☰</button>
    </div>
  </div>
</header>
<div class="accessibility-panel" id="accessibility-panel" hidden>
  <div class="a11y-inner">
    <h3>♿ Accessibilité</h3>
    <div class="a11y-options">
      <button onclick="sv.fontSize(1)">A+</button><button onclick="sv.fontSize(-1)">A-</button>
      <button onclick="sv.toggleContrast()">🌗</button><button onclick="sv.toggleDyslexia()">📖</button><button onclick="sv.resetA11y()">↺</button>
    </div>
    <button class="a11y-close" onclick="sv.closeA11y()">✕</button>
  </div>
</div>
<!-- HERO -->
<section class="tool-hero">
  <div class="container">
    <div class="privacy-badge">🔒 100% LOCAL — VOS DONNÉES NE QUITTENT JAMAIS VOTRE APPAREIL</div>
    <h1 style="font-size:3rem;font-weight:900;color:white;margin-bottom:14px">Protégez vos Documents<br><span style="color:#34d399">avec un Filigrane Souverain</span></h1>
    <p style="color:var(--text2);font-size:1.05rem;max-width:700px;margin:0 auto;line-height:1.8">Ajoutez un filigrane personnalisé à vos documents avant de les partager. Évitez le détournement, tracez qui a reçu quoi. <strong style="color:white">Rien n'est uploadé — tout se passe dans votre navigateur.</strong></p>
  </div>
</section>
<!-- OUTIL PRINCIPAL -->
<div class="tool-layout">
  <!-- Panneau contrôles -->
  <div class="tool-panel">
    <div class="panel-title">⚙️ Paramètres du filigrane</div>
    <!-- Garanties vie privée -->
    <div class="privacy-box">
      <h3>🛡️ Garanties de confidentialité</h3>
      <ul>
        <li>Votre document ne quitte jamais votre ordinateur</li>
        <li>Aucun serveur — traitement 100% dans votre navigateur</li>
        <li>Aucune connexion réseau pendant le traitement</li>
        <li>Code JavaScript open-source, auditable</li>
        <li>Fonctionne même sans internet (hors ligne)</li>
      </ul>
    </div>
    <!-- Upload -->
    <div class="field">
      <label>📄 Votre document (image JPG, PNG, WEBP)</label>
      <div class="upload-zone" id="upload-zone">
        <input type="file" id="doc-input" accept="image/jpeg,image/png,image/webp,image/gif" aria-label="Charger votre document">
        <span class="upload-icon">📂</span>
        <div class="upload-label"><strong>Cliquez ou glissez votre image ici</strong></div>
        <div class="upload-types">JPG, PNG, WEBP — Traitement 100% local</div>
      </div>
    </div>
    <!-- Texte filigrane -->
    <div class="field">
      <label>✍️ Texte du filigrane</label>
      <input type="text" id="wm-text" value="USAGE LIMITÉ — NE PAS DIFFUSER" placeholder="Votre texte de filigrane...">
      <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
        <span style="font-size:0.78rem;color:var(--text2)">Modèles rapides :</span>
        <button class="preset-btn" onclick="setPreset('CONFIDENTIEL')">CONFIDENTIEL</button>
        <button class="preset-btn" onclick="setPreset('USAGE LIMITÉ')">USAGE LIMITÉ</button>
        <button class="preset-btn" onclick="setPreset('COPIE PERSONNELLE')">COPIE PERSONNELLE</button>
        <button class="preset-btn" onclick="setPreset('NE PAS DIFFUSER')">NE PAS DIFFUSER</button>
        <button class="preset-btn" onclick="setPreset('DOCUMENT OFFICIEL')">OFFICIEL</button>
        <button class="preset-btn" id="preset-date" onclick="setPreset('Pour ' + prompt('Destinataire :','Marie Dupont') + ' — ' + new Date().toLocaleDateString('fr-FR'))">Destinataire + Date</button>
      </div>
    </div>
    <!-- Couleur -->
    <div class="field">
      <label>🎨 Couleur du filigrane</label>
      <div class="color-row">
        <button class="color-btn active" style="background:rgba(237,41,57,0.5)" onclick="setColor('rgba(237,41,57,0.45)')" title="Rouge"></button>
        <button class="color-btn" style="background:rgba(0,35,149,0.5)" onclick="setColor('rgba(0,35,149,0.45)')" title="Bleu France"></button>
        <button class="color-btn" style="background:rgba(255,255,255,0.45)" onclick="setColor('rgba(255,255,255,0.4)')" title="Blanc"></button>
        <button class="color-btn" style="background:rgba(0,0,0,0.5)" onclick="setColor('rgba(0,0,0,0.4)')" title="Noir"></button>
        <button class="color-btn" style="background:rgba(5,150,105,0.5)" onclick="setColor('rgba(5,150,105,0.45)')" title="Vert"></button>
        <input type="color" id="custom-color" value="#ED2939" title="Couleur personnalisée" aria-label="Couleur personnalisée">
        <span style="font-size:0.8rem;color:var(--text2)">Personnalisée</span>
      </div>
    </div>
    <!-- Opacité -->
    <div class="field">
      <label>🔆 Opacité</label>
      <div class="range-row">
        <input type="range" id="wm-opacity" min="5" max="80" value="35" aria-label="Opacité du filigrane">
        <span class="range-val" id="opacity-val">35%</span>
      </div>
    </div>
    <!-- Taille police -->
    <div class="field">
      <label>🔠 Taille du texte</label>
      <div class="range-row">
        <input type="range" id="wm-size" min="20" max="120" value="48" aria-label="Taille du texte">
        <span class="range-val" id="size-val">48px</span>
      </div>
    </div>
    <!-- Angle -->
    <div class="field">
      <label>📐 Angle de rotation</label>
      <div class="range-row">
        <input type="range" id="wm-angle" min="-90" max="0" value="-35" aria-label="Angle du filigrane">
        <span class="range-val" id="angle-val">-35°</span>
      </div>
    </div>
    <!-- Mode répétition -->
    <div class="field">
      <label>🔁 Mode d'affichage</label>
      <select id="wm-mode">
        <option value="repeat">Répété en mosaïque (recommandé)</option>
        <option value="center">Centré unique</option>
        <option value="diagonal">Grande diagonale</option>
      </select>
    </div>
    <!-- Format export -->
    <div class="field">
      <label>💾 Format de téléchargement</label>
      <select id="wm-format">
        <option value="jpeg">JPG (plus petit)</option>
        <option value="png" selected>PNG (meilleure qualité)</option>
        <option value="webp">WebP (moderne)</option>
      </select>
    </div>
    <!-- Boutons action -->
    <div class="actions-bar">
      <button class="btn-dl" id="download-btn" disabled onclick="downloadWatermarked()">
        💾 Télécharger avec filigrane
      </button>
      <button class="btn-reset" onclick="resetTool()">↺ Réinitialiser</button>
    </div>
    <!-- Astuce sécurité -->
    <div style="margin-top:16px;padding:14px;background:rgba(217,119,6,.06);border:1px solid rgba(217,119,6,.2);border-radius:10px">
      <p style="color:#fbbf24;font-size:0.82rem">💡 <strong>Astuce sécurité :</strong> Pour les documents très sensibles, ajoutez aussi le nom/email du destinataire dans le filigrane. Si une copie fuite, vous saurez qui l'a partagée.</p>
    </div>
  </div>
  <!-- Panneau prévisualisation -->
  <div class="tool-panel">
    <div class="panel-title">👁️ Prévisualisation en temps réel</div>
    <div class="canvas-wrap" id="canvas-wrap">
      <div class="canvas-placeholder" id="canvas-placeholder">
        <span class="ph-icon">📄</span>
        <strong>Chargez un document pour commencer</strong>
        <p style="margin-top:8px;font-size:0.85rem">L'aperçu apparaîtra ici — rien ne sera envoyé sur internet</p>
      </div>
      <canvas id="preview-canvas" style="display:none"></canvas>
    </div>
    <div id="file-info" style="display:none;margin-top:14px;padding:12px;background:var(--dark3);border-radius:10px;font-size:0.83rem;color:var(--text2)"></div>
  </div>
</div>
<!-- CONSEILS DE PROTECTION -->
<div class="tips-section" style="padding:28px;margin-left:auto;margin-right:auto">
  <h2 style="font-size:1.2rem;font-weight:800;margin-bottom:4px">🛡️ Conseils pour protéger vos documents importants</h2>
  <p style="color:var(--text2);font-size:0.88rem;margin-bottom:20px">Ces pratiques simples peuvent éviter bien des problèmes.</p>
  <div class="tips-grid">
    <div class="tip-card"><h4>⏰ Durée sur les serveurs</h4><p>Ne laissez jamais un document sensible sur un serveur plus de 48h. Supprimez-le après utilisation. Les serveurs peuvent être hackés.</p></div>
    <div class="tip-card"><h4>🔖 Filigranier avant partage</h4><p>Avant d'envoyer un document par email ou WhatsApp, ajoutez un filigrane avec le nom du destinataire. Vous saurez qui a fuité si ça arrive.</p></div>
    <div class="tip-card"><h4>🔐 Chiffrer les documents sensibles</h4><p>Utilisez 7-Zip (gratuit) pour créer des archives chiffrées AES-256. Partagez le mot de passe par un autre canal (SMS ≠ email).</p></div>
    <div class="tip-card"><h4>📧 Email n'est pas sécurisé</h4><p>Un email est une carte postale — lisible par votre fournisseur. Pour l'ultra-sensible, utilisez ProtonMail (chiffrement bout-en-bout, serveurs en Suisse).</p></div>
    <div class="tip-card"><h4>🗑️ Suppression sécurisée</h4><p>Supprimer un fichier ne le détruit pas vraiment. Utilisez Eraser (Windows) ou shred (Linux) pour une suppression définitive et irréversible.</p></div>
    <div class="tip-card"><h4>📱 Méfiez-vous des apps mobiles</h4><p>Beaucoup d'apps de scan de documents (CamScanner, etc.) envoient vos fichiers sur des serveurs étrangers. Préférez les alternatives open-source.</p></div>
  </div>
</div>
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= h(SITE_NAME) ?> — Outil de filigrane 100% local — <a href="/confidentialite/">Politique de confidentialité</a></p>
    </div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
<script>
/**
 * ═══════════════════════════════════════════════════════════
 * WATERMARK TOOL — 100% Client-Side Canvas API
 * AUCUNE DONNÉE ENVOYÉE SUR LE RÉSEAU
 * ═══════════════════════════════════════════════════════════
 */
const wm = {
  img: null,
  color: 'rgba(237,41,57,0.45)',
  // ── INIT ──────────────────────────────────────────────
  init() {
    // Input fichier
    const input = document.getElementById('doc-input');
    input?.addEventListener('change', (e) => { if (e.target.files[0]) this.loadImage(e.target.files[0]); });
    // Drag & drop
    const zone = document.getElementById('upload-zone');
    zone?.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone?.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone?.addEventListener('drop', (e) => {
      e.preventDefault(); zone.classList.remove('drag-over');
      if (e.dataTransfer.files[0]) this.loadImage(e.dataTransfer.files[0]);
    });
    // Live update sur tous les contrôles
    ['wm-text','wm-opacity','wm-size','wm-angle','wm-mode'].forEach(id => {
      document.getElementById(id)?.addEventListener('input', () => this.render());
    });
    document.getElementById('custom-color')?.addEventListener('input', (e) => {
      const hex = e.target.value;
      const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
      const opVal = parseInt(document.getElementById('wm-opacity')?.value ?? 35) / 100;
      this.color = `rgba(${r},${g},${b},${opVal})`;
      this.render();
    });
    // Affichage valeurs range
    ['wm-opacity','wm-size','wm-angle'].forEach(id => {
      const el = document.getElementById(id);
      const valEl = document.getElementById(id.replace('wm-','') + '-val');
      el?.addEventListener('input', () => {
        if (!valEl) return;
        if (id === 'wm-opacity') valEl.textContent = el.value + '%';
        else if (id === 'wm-size') valEl.textContent = el.value + 'px';
        else if (id === 'wm-angle') valEl.textContent = el.value + '°';
        this.render();
      });
    });
  },
  // ── CHARGER IMAGE ─────────────────────────────────────
  loadImage(file) {
    if (!file.type.startsWith('image/')) { alert('⚠️ Seules les images sont acceptées (JPG, PNG, WEBP).'); return; }
    if (file.size > 20 * 1024 * 1024) { alert('⚠️ Image trop grande (max 20 Mo).'); return; }
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        this.img = img;
        document.getElementById('canvas-placeholder').style.display = 'none';
        document.getElementById('preview-canvas').style.display = 'block';
        document.getElementById('download-btn').disabled = false;
        const info = document.getElementById('file-info');
        info.style.display = 'block';
        info.innerHTML = `📄 <strong>${file.name}</strong> — ${Math.round(file.size/1024)} Ko — ${img.naturalWidth}×${img.naturalHeight}px — <span style="color:#34d399">✓ Non envoyé au serveur</span>`;
        this.render();
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  },
  // ── RENDU FILIGRANE ──────────────────────────────────
  render() {
    if (!this.img) return;
    const canvas = document.getElementById('preview-canvas');
    const ctx = canvas.getContext('2d');
    const maxW = 800;
    let w = this.img.naturalWidth, h = this.img.naturalHeight;
    if (w > maxW) { h = Math.round(h * maxW / w); w = maxW; }
    canvas.width = w; canvas.height = h;
    // Dessiner l'image originale
    ctx.drawImage(this.img, 0, 0, w, h);
    // Paramètres
    const text    = document.getElementById('wm-text')?.value || 'FILIGRANE';
    const opacity = parseInt(document.getElementById('wm-opacity')?.value ?? 35) / 100;
    const size    = parseInt(document.getElementById('wm-size')?.value ?? 48);
    const angle   = parseInt(document.getElementById('wm-angle')?.value ?? -35) * Math.PI / 180;
    const mode    = document.getElementById('wm-mode')?.value ?? 'repeat';
    // Extraire/recalculer la couleur avec la bonne opacité
    const baseColor = this.color.replace(/[\d.]+\)$/, opacity + ')');
    ctx.save();
    ctx.globalAlpha = 1;
    ctx.font = `bold ${size}px "Segoe UI", system-ui, sans-serif`;
    ctx.fillStyle = baseColor;
    ctx.strokeStyle = 'rgba(0,0,0,' + (opacity * 0.3) + ')';
    ctx.lineWidth = size / 20;
    if (mode === 'center') {
      ctx.translate(w / 2, h / 2);
      ctx.rotate(angle);
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.strokeText(text, 0, 0);
      ctx.fillText(text, 0, 0);
    } else if (mode === 'diagonal') {
      ctx.translate(w / 2, h / 2);
      ctx.rotate(angle);
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      const bigSize = Math.min(w, h) / (text.length * 0.65);
      ctx.font = `bold ${bigSize}px "Segoe UI", system-ui, sans-serif`;
      ctx.strokeText(text, 0, 0);
      ctx.fillText(text, 0, 0);
    } else {
      // Mode mosaïque
      const metrics = ctx.measureText(text);
      const tw = metrics.width + size * 2;
      const th = size * 2.5;
      for (let y = -size * 2; y < h + size * 2; y += th) {
        for (let x = -tw; x < w + tw; x += tw) {
          ctx.save();
          ctx.translate(x + tw / 2, y + th / 2);
          ctx.rotate(angle);
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.strokeText(text, 0, 0);
          ctx.fillText(text, 0, 0);
          ctx.restore();
        }
      }
    }
    ctx.restore();
  },
  // ── TÉLÉCHARGER ──────────────────────────────────────
  download() {
    if (!this.img) return;
    const canvas = document.getElementById('preview-canvas');
    const format = document.getElementById('wm-format')?.value ?? 'png';
    const mimeType = format === 'jpeg' ? 'image/jpeg' : format === 'webp' ? 'image/webp' : 'image/png';
    const quality = format === 'jpeg' ? 0.92 : undefined;
    // Créer canvas pleine résolution pour le téléchargement
    const dlCanvas = document.createElement('canvas');
    dlCanvas.width = this.img.naturalWidth;
    dlCanvas.height = this.img.naturalHeight;
    const ctx = dlCanvas.getContext('2d');
    ctx.drawImage(this.img, 0, 0);
    // Appliquer filigrane à la résolution originale
    const text    = document.getElementById('wm-text')?.value || 'FILIGRANE';
    const opacity = parseInt(document.getElementById('wm-opacity')?.value ?? 35) / 100;
    const scale   = this.img.naturalWidth / Math.min(this.img.naturalWidth, 800);
    const size    = parseInt(document.getElementById('wm-size')?.value ?? 48) * scale;
    const angle   = parseInt(document.getElementById('wm-angle')?.value ?? -35) * Math.PI / 180;
    const mode    = document.getElementById('wm-mode')?.value ?? 'repeat';
    const baseColor = this.color.replace(/[\d.]+\)$/, opacity + ')');
    const w = dlCanvas.width, h = dlCanvas.height;
    ctx.save();
    ctx.font = `bold ${size}px "Segoe UI", system-ui, sans-serif`;
    ctx.fillStyle = baseColor;
    ctx.strokeStyle = 'rgba(0,0,0,' + (opacity * 0.3) + ')';
    ctx.lineWidth = size / 20;
    if (mode === 'center' || mode === 'diagonal') {
      const s = mode === 'diagonal' ? Math.min(w,h) / (text.length * 0.65) : size;
      ctx.font = `bold ${s}px "Segoe UI", system-ui, sans-serif`;
      ctx.translate(w/2, h/2); ctx.rotate(angle);
      ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
      ctx.strokeText(text, 0, 0); ctx.fillText(text, 0, 0);
    } else {
      const metrics = ctx.measureText(text);
      const tw = metrics.width + size * 2, th = size * 2.5;
      for (let y = -size*2; y < h+size*2; y += th) {
        for (let x = -tw; x < w+tw; x += tw) {
          ctx.save(); ctx.translate(x+tw/2, y+th/2); ctx.rotate(angle);
          ctx.textAlign='center'; ctx.textBaseline='middle';
          ctx.strokeText(text,0,0); ctx.fillText(text,0,0); ctx.restore();
        }
      }
    }
    ctx.restore();
    // Télécharger
    dlCanvas.toBlob((blob) => {
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'document-protege-' + new Date().toISOString().slice(0,10) + '.' + format;
      a.click();
      setTimeout(() => URL.revokeObjectURL(url), 1000);
    }, mimeType, quality);
  }
};
function setColor(c) {
  wm.color = c;
  document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
  wm.render();
}
function setPreset(text) { document.getElementById('wm-text').value = text; wm.render(); }
function downloadWatermarked() { wm.download(); }
function resetTool() {
  wm.img = null;
  document.getElementById('doc-input').value = '';
  document.getElementById('preview-canvas').style.display = 'none';
  document.getElementById('canvas-placeholder').style.display = 'block';
  document.getElementById('download-btn').disabled = true;
  document.getElementById('file-info').style.display = 'none';
  document.getElementById('wm-text').value = 'USAGE LIMITÉ — NE PAS DIFFUSER';
}
document.addEventListener('DOMContentLoaded', () => { sv.init(); wm.init(); });
</script>
</body>
</html>

