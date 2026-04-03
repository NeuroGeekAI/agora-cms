<?php
/**
 * AgoraCMS — Page de maintenance
 * Affichée à tous les visiteurs quand maintenance = 1
 * L'admin (connecté) bypasse automatiquement
 */
if (!defined('AGORA')) define('AGORA', true);
$site_name = defined('SITE_NAME') ? SITE_NAME : 'AgoraCMS';
$site_tagline = defined('SITE_TAGLINE') ? SITE_TAGLINE : 'La voix souveraine de la France libre';
http_response_code(503);
header('Retry-After: 3600');
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Maintenance — <?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="robots" content="noindex, nofollow">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bleu:#002395;--rouge:#ED2939;--blanc:#FFFFFF;--or:#C9A227;--bg:#0a0a14;--bg2:#0f0f1e;--text:#d0d4e8;--text2:#7880a4;--border:#1a1f3a;--border2:#252b4a}
html,body{height:100%;background:var(--bg);color:var(--text);font-family:'Segoe UI',system-ui,-apple-system,sans-serif;overflow:hidden}
/* Stars background */
.stars{position:fixed;inset:0;z-index:0;overflow:hidden}
.stars::before,.stars::after{content:'';position:absolute;inset:0;background-image:radial-gradient(1px 1px at 20% 30%,rgba(255,255,255,.4) 0%,transparent 100%),radial-gradient(1px 1px at 80% 10%,rgba(255,255,255,.3) 0%,transparent 100%),radial-gradient(1px 1px at 50% 60%,rgba(255,255,255,.25) 0%,transparent 100%),radial-gradient(1px 1px at 30% 80%,rgba(255,255,255,.35) 0%,transparent 100%),radial-gradient(1px 1px at 70% 50%,rgba(255,255,255,.2) 0%,transparent 100%);background-repeat:repeat;background-size:300px 300px}
.stars::after{background-size:500px 500px;opacity:.5;animation:twinkle 8s ease-in-out infinite alternate}
@keyframes twinkle{0%{opacity:.3}100%{opacity:.7}}
/* Tricolor accent */
.tricolor{position:fixed;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--bleu) 33.33%,var(--blanc) 33.33%,var(--blanc) 66.66%,var(--rouge) 66.66%);z-index:10}
/* Main */
.page{position:relative;z-index:1;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;text-align:center}
/* Box */
.box{background:rgba(15,15,30,.96);border:1px solid var(--border2);border-radius:16px;padding:52px 64px;max-width:660px;width:100%;box-shadow:0 8px 60px rgba(0,35,149,.15),0 0 0 1px rgba(201,162,39,.08);position:relative;overflow:hidden}
.box::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--bleu),var(--or),var(--rouge));opacity:.7}
/* Coat of arms / icon */
.icon{font-size:3.5rem;margin-bottom:16px;display:block;filter:drop-shadow(0 0 16px rgba(201,162,39,.5))}
/* Badge */
.badge{display:inline-flex;align-items:center;gap:8px;background:rgba(201,162,39,.08);border:1px solid rgba(201,162,39,.25);color:var(--or);font-size:0.7rem;font-weight:700;padding:5px 16px;border-radius:20px;letter-spacing:3px;text-transform:uppercase;margin-bottom:20px}
/* Title */
h1{font-size:2.2rem;font-weight:900;color:var(--blanc);line-height:1.15;margin-bottom:8px;letter-spacing:-0.5px}
h1 span{background:linear-gradient(135deg,var(--bleu) 0%,var(--or) 50%,var(--rouge) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.subtitle{font-size:0.88rem;color:var(--text2);margin-bottom:28px;letter-spacing:1px;font-style:italic}
/* Divider */
.divider{display:flex;align-items:center;gap:12px;margin:24px 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--border2))}
.divider span{font-size:1rem}
/* Progress */
.progress-wrap{margin:20px 0}
.progress-label{font-size:0.7rem;letter-spacing:2px;color:var(--text2);text-transform:uppercase;margin-bottom:8px;font-family:'Courier New',monospace}
.progress-track{height:4px;background:var(--border2);border-radius:2px;overflow:hidden}
.progress-fill{height:100%;background:linear-gradient(90deg,var(--bleu),var(--or),var(--rouge));border-radius:2px;animation:prog 10s ease-in-out infinite alternate}
@keyframes prog{0%{width:12%}100%{width:88%}}
/* Status items */
.status-list{display:flex;flex-direction:column;gap:8px;text-align:left;margin:20px 0;background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:8px;padding:16px 20px}
.status-item{display:flex;align-items:center;gap:10px;font-size:0.82rem;font-family:'Courier New',monospace;color:var(--text2)}
.status-item .dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.dot-ok{background:#00cc66;box-shadow:0 0 6px #00cc66}
.dot-prog{background:var(--or);box-shadow:0 0 6px var(--or);animation:pulse .8s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
/* Message */
.msg{font-size:0.9rem;color:var(--text2);line-height:1.8;margin-bottom:8px}
/* Flag stripe */
.flag-row{display:flex;justify-content:center;gap:6px;margin:20px 0}
.flag-stripe{width:28px;height:4px;border-radius:2px}
/* Admin link */
.admin-link{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);border:1px solid var(--border2);color:var(--text2);font-size:0.72rem;padding:9px 20px;border-radius:8px;letter-spacing:1px;text-decoration:none;text-transform:uppercase;transition:all .2s;margin-top:16px}
.admin-link:hover{background:rgba(201,162,39,.08);border-color:rgba(201,162,39,.3);color:var(--or)}
/* Footer */
.foot{position:fixed;bottom:16px;font-size:0.65rem;color:var(--border2);letter-spacing:2px;text-transform:uppercase}
@media(max-width:560px){.box{padding:32px 20px}h1{font-size:1.6rem}}
</style>
</head>
<body>
<div class="tricolor"></div>
<div class="stars"></div>
<div class="page">
  <div class="box">
    <span class="icon">⚜️</span>
    <div class="badge">🔧 Maintenance en cours</div>
    <h1>Le site sera<br><span>bientôt de retour</span></h1>
    <p class="subtitle"><?= htmlspecialchars($site_tagline, ENT_QUOTES, 'UTF-8') ?></p>
    <div class="divider"><span>🇫🇷</span></div>
    <div class="status-list">
      <div class="status-item"><span class="dot dot-ok"></span> Sécurité renforcée — Chiffrement SSL actif</div>
      <div class="status-item"><span class="dot dot-ok"></span> Base de données — Mise à jour effectuée</div>
      <div class="status-item"><span class="dot dot-prog"></span> Déploiement du nouveau contenu en cours...</div>
    </div>
    <div class="progress-wrap">
      <div class="progress-label">// Déploiement en cours</div>
      <div class="progress-track"><div class="progress-fill"></div></div>
    </div>
    <p class="msg">
      Nous mettons à jour la plateforme pour vous offrir<br>
      une meilleure expérience citoyenne.<br>
      Merci de votre patience.
    </p>
    <div class="flag-row">
      <div class="flag-stripe" style="background:var(--bleu)"></div>
      <div class="flag-stripe" style="background:var(--blanc);border:1px solid rgba(255,255,255,.1)"></div>
      <div class="flag-stripe" style="background:var(--rouge)"></div>
    </div>
    <a href="/admin/" class="admin-link">🔐 Accès Administration</a>
  </div>
</div>
<div class="foot"><?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?> — <?= date('Y') ?></div>
</body>
</html>
