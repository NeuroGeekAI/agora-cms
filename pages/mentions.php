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
<title>Mentions légales — <?= h(SITE_NAME) ?></title>
<meta name="description" content="Mentions légales de <?= h(SITE_NAME) ?> — Informations légales obligatoires.">
<meta name="robots" content="noindex,follow">
<link rel="canonical" href="<?= ag_canonical('mentions-legales/') ?>">
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
.legal-tag{display:inline-block;background:rgba(0,35,149,.2);color:#93c5fd;font-size:0.75rem;font-weight:700;padding:3px 10px;border-radius:999px;letter-spacing:.5px;margin-bottom:14px}
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
    <div class="legal-tag">LÉGAL</div>
    <h1>⚖️ Mentions légales</h1>
    <p>Informations légales obligatoires conformes à la loi française n° 2004-575 du 21 juin 2004</p>
  </div>
</div>
<main>
  <div class="legal-body">
    <div class="legal-section">
      <h2>🏛️ Éditeur du site</h2>
      <p><strong>Dénomination :</strong> <?= h(SITE_NAME) ?> — site personnel à caractère informatif et politique</p>
      <p><strong>Responsable de publication :</strong> Exploitant particulier, personne physique</p>
      <p><strong>Adresse électronique :</strong> <a href="mailto:<?= h(SITE_EMAIL) ?>"><?= h(SITE_EMAIL) ?></a></p>
      <p><strong>Site web :</strong> <a href="<?= h(SITE_URL) ?>"><?= h(SITE_URL) ?></a></p>
      <p><strong>Nature du site :</strong> Site d'information et d'opinion à caractère politique citoyen, sans affiliation à un parti politique enregistré.</p>
    </div>
    <div class="legal-section">
      <h2>🖥️ Hébergement</h2>
      <p><strong>Hébergeur :</strong> O2switch SAS</p>
      <p><strong>Adresse :</strong> Chemin des Pardiaux, 63000 Clermont-Ferrand, France</p>
      <p><strong>SIRET :</strong> 510 909 807 00024</p>
      <p><strong>Téléphone :</strong> +33 (0)4 44 44 60 40</p>
      <p><strong>Site :</strong> <a href="https://www.o2switch.fr" target="_blank" rel="noopener noreferrer">www.o2switch.fr</a></p>
      <p>Les serveurs sont localisés en <strong>France</strong>, soumis au droit français et aux réglementations européennes (RGPD).</p>
    </div>
    <div class="legal-section">
      <h2>📝 Propriété intellectuelle</h2>
      <p>L'ensemble des contenus publiés sur <?= h(SITE_NAME) ?> (textes, images, logos, graphismes) est la propriété de l'éditeur, sauf mention contraire, et est protégé par les lois françaises et internationales relatives à la propriété intellectuelle.</p>
      <p>Toute reproduction, représentation, modification, publication ou adaptation, totale ou partielle, des éléments du site est interdite sans accord écrit préalable de l'éditeur.</p>
      <p>Les marques et logos reproduits sur le site sont la propriété de leurs titulaires respectifs.</p>
    </div>
    <div class="legal-section">
      <h2>🔗 Liens hypertextes</h2>
      <p>Le site peut contenir des liens vers d'autres sites. Ces liens sont fournis à titre informatif uniquement. L'éditeur n'exerce aucun contrôle sur ces sites tiers et n'est pas responsable de leur contenu.</p>
      <p>Tout lien pointant vers <?= h(SITE_NAME) ?> doit faire l'objet d'une autorisation préalable de l'éditeur.</p>
    </div>
    <div class="legal-section">
      <h2>🛡️ Limitation de responsabilité</h2>
      <p>Les informations publiées sur ce site sont fournies à titre indicatif et ne constituent pas un conseil juridique, financier ou politique. L'éditeur s'efforce d'assurer l'exactitude des informations publiées, mais ne saurait être tenu responsable des erreurs, omissions ou résultats obtenus par l'utilisation de ces informations.</p>
      <p>L'éditeur se réserve le droit de modifier, à tout moment et sans préavis, le contenu de ce site.</p>
    </div>
    <div class="legal-section">
      <h2>⚖️ Droit applicable — Juridiction compétente</h2>
      <p>Le présent site est soumis au <strong>droit français</strong>. En cas de litige, les tribunaux français seront seuls compétents.</p>
      <p>Pour tout litige relatif à l'utilisation du site, il convient de contacter en premier lieu l'éditeur à l'adresse : <a href="mailto:<?= h(SITE_EMAIL) ?>"><?= h(SITE_EMAIL) ?></a></p>
    </div>
    <div class="legal-section">
      <h2>🍪 Cookies</h2>
      <p>Ce site utilise des cookies strictement nécessaires au fonctionnement du site (session, préférences d'accessibilité). Aucun cookie publicitaire ou de traçage tiers n'est utilisé.</p>
      <p>Vous pouvez configurer votre navigateur pour refuser les cookies, mais certaines fonctionnalités du site pourraient alors ne plus fonctionner correctement.</p>
      <p>Pour plus d'informations, consultez notre <a href="/confidentialite/">politique de confidentialité</a>.</p>
    </div>
    <p style="font-size:0.8rem;color:var(--text2);text-align:center;margin-top:32px">Dernière mise à jour : <?= date('d/m/Y') ?></p>
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

