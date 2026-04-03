<?php
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
check_maintenance();
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Politique de confidentialité — <?= h(SITE_NAME) ?></title>
<meta name="description" content="Politique de confidentialité de <?= h(SITE_NAME) ?> — Comment nous protégeons vos données personnelles (RGPD).">
<meta name="robots" content="noindex,follow">
<link rel="canonical" href="<?= ag_canonical('confidentialite/') ?>">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.legal-hero{background:linear-gradient(135deg,#0d1117 0%,#161b22 100%);border-bottom:1px solid var(--border);padding:60px 0;text-align:center}
.legal-hero h1{font-size:2.2rem;font-weight:900;margin-bottom:12px}
.legal-hero p{color:var(--text2);font-size:1rem}
.legal-body{max-width:860px;margin:0 auto;padding:60px 20px}
.legal-section{background:var(--dark2);border:1px solid var(--border);border-radius:16px;padding:28px 32px;margin-bottom:24px}
.legal-section h2{font-size:1.15rem;font-weight:800;color:white;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.legal-section p,.legal-section li{color:var(--text2);font-size:0.92rem;line-height:1.75;margin-bottom:8px}
.legal-section ul{padding-left:20px}
.legal-section strong{color:white}
.legal-section a{color:#60a5fa}
.legal-section a:hover{color:#93c5fd}
.legal-tag{display:inline-block;background:rgba(5,150,105,.2);color:#34d399;font-size:0.75rem;font-weight:700;padding:3px 10px;border-radius:999px;letter-spacing:.5px;margin-bottom:14px}
.rgpd-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(5,150,105,.1);border:1px solid rgba(5,150,105,.3);color:#34d399;padding:10px 18px;border-radius:10px;font-size:0.88rem;font-weight:700;margin-bottom:24px}
.rights-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px}
.right-item{background:var(--dark3);border-radius:10px;padding:14px;font-size:0.88rem}
.right-item strong{display:block;color:white;margin-bottom:4px;font-size:0.9rem}
.right-item span{color:var(--text2)}
@media(max-width:600px){.rights-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<header class="site-header">
  <div class="header-top"><div class="tricolor-bar"><span></span><span></span><span></span></div></div>
  <div class="container header-inner">
    <a href="/" class="site-logo"><span class="logo-icon">🏛️</span><div><div class="logo-name"><?= h(SITE_NAME) ?></div></div></a>
    <nav class="main-nav">
      <a href="/" class="nav-link">Accueil</a>
      <a href="/handicap/" class="nav-link nav-handicap">♿ Handicap</a>
      <a href="/manifeste/" class="nav-link">Programme</a>
      <a href="/rejoindre/" class="nav-link">Rejoindre</a>
      <a href="/contact/" class="nav-link">Contact</a>
    </nav>
    <div class="header-actions">
      <form class="search-form" action="/recherche/" method="get">
        <input type="search" name="q" placeholder="Rechercher..." aria-label="Rechercher">
        <button type="submit">🔍</button>
      </form>
      <button class="accessibility-btn" onclick="document.getElementById('a11y-panel').removeAttribute('hidden')" aria-label="Accessibilité">♿</button>
      <button class="hamburger" id="menu-toggle" aria-label="Menu">☰</button>
    </div>
  </div>
</header>
<div class="legal-hero">
  <div class="container">
    <div class="legal-tag">RGPD ✅</div>
    <h1>🔒 Politique de confidentialité</h1>
    <p>Nous respectons votre vie privée — conforme au Règlement Général sur la Protection des Données (RGPD / UE 2016/679)</p>
  </div>
</div>
<main>
  <div class="legal-body">
    <div class="legal-section">
      <div class="rgpd-badge">✅ Site conforme RGPD — Aucune donnée vendue à des tiers</div>
      <h2>👤 Responsable du traitement</h2>
      <p><strong>Site :</strong> <?= h(SITE_NAME) ?> — <a href="<?= h(SITE_URL) ?>"><?= h(SITE_URL) ?></a></p>
      <p><strong>Contact DPO :</strong> <a href="mailto:<?= h(SITE_EMAIL) ?>"><?= h(SITE_EMAIL) ?></a></p>
      <p>Toute demande relative à vos données personnelles peut être adressée à l'adresse email ci-dessus.</p>
    </div>
    <div class="legal-section">
      <h2>📊 Données collectées et finalités</h2>
      <p>Nous collectons uniquement les données strictement nécessaires au fonctionnement du site :</p>
      <ul>
        <li><strong>Formulaire de contact :</strong> nom, adresse email, message — finalité : répondre à votre demande</li>
        <li><strong>Pétitions :</strong> prénom, adresse email, pays — finalité : comptabilisation et envoi de confirmation</li>
        <li><strong>Logs serveur :</strong> adresse IP anonymisée (hash SHA-256), navigateur, date/heure — finalité : sécurité et prévention des abus</li>
        <li><strong>Cookies de session :</strong> identifiant de session temporaire — finalité : fonctionnement du site</li>
        <li><strong>Préférences d'accessibilité :</strong> stockées localement dans votre navigateur (localStorage) — finalité : mémoriser vos préférences</li>
      </ul>
    </div>
    <div class="legal-section">
      <h2>⏱️ Durée de conservation</h2>
      <ul>
        <li><strong>Messages de contact :</strong> conservés 12 mois maximum, puis supprimés</li>
        <li><strong>Signatures de pétitions :</strong> conservées le temps de la pétition, supprimées à la clôture</li>
        <li><strong>Logs serveur :</strong> conservés 30 jours maximum</li>
        <li><strong>Cookies de session :</strong> supprimés à la fermeture du navigateur</li>
      </ul>
    </div>
    <div class="legal-section">
      <h2>🍪 Cookies utilisés</h2>
      <p>Ce site utilise uniquement des cookies <strong>strictement nécessaires</strong> :</p>
      <ul>
        <li><code>ag_session</code> — Cookie de session PHP, durée : session navigateur</li>
        <li><code>sv_a11y_*</code> — Préférences d'accessibilité (localStorage, pas un cookie)</li>
      </ul>
      <p><strong>Aucun</strong> cookie publicitaire, cookie de traçage ou cookie tiers (Google Analytics, Facebook Pixel, etc.) n'est utilisé sur ce site.</p>
    </div>
    <div class="legal-section">
      <h2>🔐 Sécurité des données</h2>
      <p>Nous mettons en œuvre des mesures techniques et organisationnelles pour protéger vos données :</p>
      <ul>
        <li>Connexion chiffrée HTTPS (TLS 1.3) avec certificat Let's Encrypt</li>
        <li>Hébergement en France chez O2switch (données soumises au droit français)</li>
        <li>Adresses IP anonymisées par hachage cryptographique (SHA-256 + sel)</li>
        <li>Mots de passe hachés avec bcrypt (coût 12)</li>
        <li>Accès aux données limité au strict minimum</li>
        <li>Aucune transmission de données à des tiers commerciaux</li>
      </ul>
    </div>
    <div class="legal-section">
      <h2>⚡ Vos droits RGPD</h2>
      <p>Conformément au RGPD (Règlement UE 2016/679) et à la loi Informatique et Libertés, vous disposez des droits suivants :</p>
      <div class="rights-grid">
        <div class="right-item"><strong>✅ Droit d'accès</strong><span>Obtenir une copie de vos données</span></div>
        <div class="right-item"><strong>✏️ Droit de rectification</strong><span>Corriger vos données inexactes</span></div>
        <div class="right-item"><strong>🗑️ Droit à l'effacement</strong><span>Demander la suppression de vos données</span></div>
        <div class="right-item"><strong>⛔ Droit d'opposition</strong><span>Vous opposer au traitement</span></div>
        <div class="right-item"><strong>📦 Droit à la portabilité</strong><span>Recevoir vos données dans un format lisible</span></div>
        <div class="right-item"><strong>⏸️ Droit de limitation</strong><span>Limiter l'utilisation de vos données</span></div>
      </div>
      <p style="margin-top:16px">Pour exercer ces droits, contactez-nous : <a href="mailto:<?= h(SITE_EMAIL) ?>"><?= h(SITE_EMAIL) ?></a></p>
      <p>En cas de réponse insatisfaisante, vous pouvez saisir la <strong>CNIL</strong> : <a href="https://www.cnil.fr/fr/plaintes" target="_blank" rel="noopener noreferrer">www.cnil.fr/fr/plaintes</a></p>
    </div>
    <div class="legal-section">
      <h2>🔄 Modifications de cette politique</h2>
      <p>Cette politique de confidentialité peut être mise à jour à tout moment. La date de dernière modification est indiquée ci-dessous. Nous vous invitons à la consulter régulièrement.</p>
    </div>
    <p style="font-size:0.8rem;color:var(--text2);text-align:center;margin-top:32px">Dernière mise à jour : <?= date('d/m/Y') ?> — Version 1.0</p>
  </div>
</main>
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= h(SITE_NAME) ?> — <a href="/mentions-legales/">Mentions légales</a> — <a href="/confidentialite/">Confidentialité</a></p>
    </div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>

