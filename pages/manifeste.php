<?php
/**
 * AgoraCMS — Manifeste & Vision citoyen
 * Slogans, projets étudiants, révolution technologique française
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
check_maintenance();
// Slogans rotatifs — la force de la France
$slogans = [
    ["texte" => "Équiper l'armée française d'un OS souverain basé sur Linux — c'est possible, c'est urgent, c'est moins cher.", "auteur" => "Vision Défense & Numérique", "icon" => "🛡️"],
    ["texte" => "Un étudiant français qui code un OS libre vaut plus pour notre indépendance qu'un abonnement Microsoft à vie.", "auteur" => "Vision Éducation & Innovation", "icon" => "🎓"],
    ["texte" => "Vos données restent en France ou elles ne restent pas. La souveraineté numérique n'est pas négociable.", "auteur" => "Vision Numérique", "icon" => "💻"],
    ["texte" => "12 millions de Français handicapés sont une force économique dormante. Réveillons-la maintenant.", "auteur" => "Vision Inclusive", "icon" => "♿"],
    ["texte" => "La France a inventé le Minitel avant Internet. Elle peut inventer l'Internet souverain de demain.", "auteur" => "Vision Innovation", "icon" => "🚀"],
    ["texte" => "Arrêtons de subventionner Amazon, Google et Microsoft. Finançons nos ingénieurs français.", "auteur" => "Vision Économique", "icon" => "📈"],
    ["texte" => "Un citoyen informé est un citoyen libre. Les lois sont publiques — rendons-les lisibles par tous.", "auteur" => "Vision Démocratique", "icon" => "⚖️"],
    ["texte" => "L'indépendance technologique de la France commence dans les universités françaises, pas dans la Silicon Valley.", "auteur" => "Vision citoyen", "icon" => "🏛️"],
    ["texte" => "Rembourser les technologies d'assistance comme on rembourse les médicaments — c'est de l'investissement, pas du coût.", "auteur" => "Vision Santé & Handicap", "icon" => "🏥"],
    ["texte" => "Un OS libre dans chaque administration française = 2 milliards d'euros économisés chaque année.", "auteur" => "Vision Budget", "icon" => "💰"],
];
// Projets étudiants citoyens (démonstration)
$projets_etudiants = [
    ["nom" => "FranceOS", "desc" => "Distribution Linux optimisée pour les administrations françaises, ANSSI certifiée, développée par des étudiants de l'INRIA et de Polytechnique.", "status" => "En développement", "techs" => ["Linux", "Kernel", "ANSSI"], "lien" => "#"],
    ["nom" => "LexiCitoyen", "desc" => "Application mobile qui traduit les lois françaises en langage simple et accessible. Développée par des étudiants en droit et en informatique.", "status" => "Beta ouverte", "techs" => ["PHP", "NLP", "API Légifrance"], "lien" => "#"],
    ["nom" => "DataFR Shield", "desc" => "Outil open-source de chiffrement et de filigrane pour protéger vos documents personnels sans les envoyer sur un serveur tiers.", "status" => "Disponible", "techs" => ["JavaScript", "Canvas API", "WebCrypto"], "lien" => "/proteger-documents/"],
    ["nom" => "GouvernementScore", "desc" => "Plateforme citoyenne qui note les décisions des élus en temps réel basée sur leurs promesses électorales vs actions concrètes.", "status" => "Concept", "techs" => ["Open Data", "API", "Blockchain"], "lien" => "#"],
    ["nom" => "FranceMesh", "desc" => "Réseau de communication décentralisé fonctionnant sans internet central — résistant à toute censure ou panne d'infrastructure.", "status" => "Recherche", "techs" => ["Mesh Network", "LoRa", "P2P"], "lien" => "#"],
    ["nom" => "SanctionTracker", "desc" => "Base de données citoyenne des sanctions européennes et françaises — qui sanctionne qui, pourquoi, avec quels résultats.", "status" => "En développement", "techs" => ["Open Data", "Vue.js", "PostgreSQL"], "lien" => "#"],
];
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manifeste citoyen — <?= h(SITE_NAME) ?></title>
<meta name="description" content="Le manifeste de la France souveraine : révolution numérique, indépendance technologique, projets étudiants innovants. Arrêtons d'infantiliser les citoyens français.">
<link rel="canonical" href="<?= ag_canonical('manifeste/') ?>">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
/* ── SLOGANS SLIDER ─────────────────────────────────── */
.slogans-section{background:linear-gradient(135deg,#0a0a1a 0%,#0d1117 50%,#1a0505 100%);padding:80px 0;overflow:hidden;position:relative}
.slogans-section::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23002395' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.slogan-display{max-width:900px;margin:0 auto;text-align:center;position:relative;z-index:1}
.slogan-quotes{font-size:6rem;color:var(--rouge);opacity:.3;line-height:.5;margin-bottom:20px}
.slogan-text{font-size:1.8rem;font-weight:800;color:white;line-height:1.4;min-height:100px;transition:all .5s;margin-bottom:24px}
.slogan-author{font-size:0.9rem;color:var(--text2);letter-spacing:2px;text-transform:uppercase}
.slogan-icon{font-size:2rem;margin-bottom:16px;display:block}
.slogan-dots{display:flex;justify-content:center;gap:8px;margin-top:32px}
.slogan-dot{width:8px;height:8px;border-radius:50%;background:var(--border);transition:.3s;cursor:pointer;border:none}
.slogan-dot.active{background:var(--rouge);width:24px;border-radius:4px}
.slogan-nav{display:flex;gap:16px;justify-content:center;margin-top:20px}
.slogan-btn{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:white;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:1rem;transition:.2s}
.slogan-btn:hover{background:rgba(255,255,255,.15)}
/* ── VISION UNIX ────────────────────────────────────── */
.unix-section{background:var(--dark2);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:80px 0}
.unix-hero{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;max-width:1100px;margin:0 auto}
.unix-text .tag{display:inline-block;background:rgba(5,150,105,.15);border:1px solid rgba(5,150,105,.3);color:#34d399;font-size:0.78rem;font-weight:700;padding:5px 14px;border-radius:999px;letter-spacing:2px;margin-bottom:20px}
.unix-text h2{font-size:2.5rem;font-weight:900;line-height:1.2;margin-bottom:20px;color:white}
.unix-text h2 em{color:#34d399;font-style:normal}
.unix-text p{color:var(--text2);font-size:1.05rem;line-height:1.8;margin-bottom:16px}
.unix-stats{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:32px}
.unix-stat{background:var(--dark3);border:1px solid var(--border);border-radius:12px;padding:20px;text-align:center}
.unix-stat .num{font-size:2rem;font-weight:900;color:#34d399}
.unix-stat .lab{font-size:0.78rem;color:var(--text2);margin-top:4px}
.unix-visual{background:var(--dark3);border:1px solid var(--border);border-radius:20px;padding:28px;font-family:'Courier New',monospace}
.terminal{background:#000;border-radius:12px;padding:20px}
.terminal-bar{display:flex;gap:6px;margin-bottom:16px}
.terminal-bar span{width:12px;height:12px;border-radius:50%}
.t-red{background:#ff5f56}.t-yellow{background:#ffbd2e}.t-green{background:#27c93f}
.terminal-line{color:#33ff33;font-size:0.88rem;line-height:1.8}
.terminal-line .cmd{color:#60a5fa}
.terminal-line .ok{color:#34d399}
.terminal-line .warn{color:#fbbf24}
.cursor{display:inline-block;width:8px;height:16px;background:#33ff33;animation:blink 1s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0}}
/* ── PROJETS ÉTUDIANTS ──────────────────────────────── */
.students-section{padding:80px 0}
.students-header{text-align:center;margin-bottom:60px}
.students-tag{display:inline-block;background:rgba(124,58,237,.15);border:1px solid rgba(124,58,237,.3);color:#a78bfa;font-size:0.78rem;font-weight:700;padding:5px 14px;border-radius:999px;letter-spacing:2px;margin-bottom:16px}
.projects-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:24px}
.project-card{background:var(--dark2);border:1px solid var(--border);border-radius:20px;padding:28px;transition:all .3s;position:relative;overflow:hidden}
.project-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--bleu),var(--rouge))}
.project-card:hover{transform:translateY(-8px);border-color:rgba(124,58,237,.4);box-shadow:0 20px 60px rgba(124,58,237,.15)}
.project-status{display:inline-block;font-size:0.72rem;font-weight:700;padding:4px 12px;border-radius:999px;margin-bottom:14px}
.status-available{background:rgba(5,150,105,.15);color:#34d399;border:1px solid rgba(5,150,105,.3)}
.status-beta{background:rgba(0,35,149,.15);color:#93c5fd;border:1px solid rgba(0,35,149,.3)}
.status-dev{background:rgba(217,119,6,.15);color:#fbbf24;border:1px solid rgba(217,119,6,.3)}
.status-concept{background:rgba(124,58,237,.15);color:#a78bfa;border:1px solid rgba(124,58,237,.3)}
.status-research{background:rgba(107,114,128,.15);color:#9ca3af;border:1px solid rgba(107,114,128,.3)}
.project-name{font-size:1.3rem;font-weight:900;color:white;margin-bottom:10px}
.project-desc{color:var(--text2);font-size:0.9rem;line-height:1.7;margin-bottom:16px}
.project-techs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px}
.tech-tag{background:var(--dark3);border:1px solid var(--border);color:var(--muted);font-size:0.72rem;padding:3px 10px;border-radius:6px;font-family:monospace}
.project-link{display:inline-flex;align-items:center;gap:6px;color:#60a5fa;font-size:0.88rem;font-weight:600;transition:.2s}
.project-link:hover{gap:10px;text-decoration:none}
/* ── APPEL ÉTUDIANTS ────────────────────────────────── */
.student-cta{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%);border-radius:24px;padding:48px;text-align:center;max-width:900px;margin:48px auto 0}
.student-cta h3{font-size:2rem;font-weight:900;color:white;margin-bottom:14px}
.student-cta p{color:rgba(255,255,255,.8);font-size:1.05rem;max-width:600px;margin:0 auto 28px}
/* ── CHIFFRES CHOC ──────────────────────────────────── */
.chiffres-section{padding:80px 0;background:var(--dark2);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
.chiffres-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center}
.chiffre-item{padding:32px 20px}
.chiffre-num{font-size:3rem;font-weight:900;background:linear-gradient(135deg,var(--bleu),var(--rouge));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.chiffre-label{font-size:1rem;font-weight:700;color:white;margin:8px 0 4px}
.chiffre-source{font-size:0.75rem;color:var(--text2)}
@media(max-width:768px){
  .unix-hero{grid-template-columns:1fr}
  .chiffres-grid{grid-template-columns:1fr 1fr}
  .slogan-text{font-size:1.3rem}
}
</style>
</head>
<body>
<!-- HEADER -->
<header class="site-header">
  <div class="header-top"><div class="tricolor-bar"><span></span><span></span><span></span></div></div>
  <div class="container header-inner">
    <a href="/" class="site-logo"><span class="logo-icon">🏛️</span><div><div class="logo-name"><?= h(SITE_NAME) ?></div><div class="logo-tagline"><?= h(SITE_TAGLINE) ?></div></div></a>
    <nav class="main-nav" id="main-nav">
      <a href="/" class="nav-link">Accueil</a>
      <a href="/manifeste/" class="nav-link active">🔥 Manifeste</a>
      <a href="/lois/" class="nav-link">⚖️ Lois</a>
      <a href="/debats/" class="nav-link">💬 Débats</a>
      <a href="/etudiants/" class="nav-link">🎓 Étudiants</a>
      <a href="/proteger-documents/" class="nav-link">🔒 Docs</a>
      <a href="/handicap/" class="nav-link nav-handicap">♿ Handicap</a>
    </nav>
    <div class="header-actions">
      <button class="accessibility-btn" id="accessibility-btn" aria-label="Accessibilité">♿</button>
      <button class="hamburger" id="hamburger">☰</button>
    </div>
  </div>
</header>
<div class="accessibility-panel" id="accessibility-panel" hidden>
  <div class="a11y-inner">
    <h3>♿ Accessibilité</h3>
    <div class="a11y-options">
      <button onclick="sv.fontSize(1)">A+</button><button onclick="sv.fontSize(-1)">A-</button>
      <button onclick="sv.toggleContrast()">🌗 Contraste</button>
      <button onclick="sv.toggleDyslexia()">📖 Dyslexie</button>
      <button onclick="sv.resetA11y()">↺ Reset</button>
    </div>
    <button class="a11y-close" onclick="sv.closeA11y()">✕</button>
  </div>
</div>
<!-- HERO MANIFESTE -->
<div style="background:linear-gradient(135deg,#0d1117 0%,#1a0a2e 50%,#0a1a0d 100%);padding:80px 0;text-align:center;border-bottom:1px solid var(--border)">
  <div class="container">
    <div style="display:inline-block;background:rgba(237,41,57,.15);border:1px solid rgba(237,41,57,.3);color:#fca5a5;font-size:0.78rem;font-weight:700;padding:6px 18px;border-radius:999px;letter-spacing:2px;margin-bottom:20px">MANIFESTE citoyen 2026</div>
    <h1 style="font-size:3.5rem;font-weight:900;color:white;line-height:1.15;margin-bottom:20px">
      La France arrête<br><span style="background:linear-gradient(90deg,#002395,#ED2939);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">de subir.</span><br>Elle décide.
    </h1>
    <p style="color:var(--text2);font-size:1.15rem;max-width:650px;margin:0 auto 36px;line-height:1.8">Souveraineté numérique, indépendance technologique, citoyens éduqués et libres. Pas de discours — des actes, des projets, des outils concrets.</p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
      <a href="#vision-unix" class="btn-primary">💻 Vision Linux pour la France</a>
      <a href="#projets-etudiants" class="btn-secondary">🎓 Projets étudiants</a>
    </div>
  </div>
</div>
<!-- SLOGANS ROTATIFS -->
<section class="slogans-section" aria-label="Slogans citoyens">
  <div class="container">
    <div class="slogan-display">
      <div class="slogan-quotes">"</div>
      <span class="slogan-icon" id="slogan-icon"><?= $slogans[0]['icon'] ?></span>
      <p class="slogan-text" id="slogan-text" aria-live="polite"><?= h($slogans[0]['texte']) ?></p>
      <p class="slogan-author" id="slogan-author">— <?= h($slogans[0]['auteur']) ?></p>
      <div class="slogan-nav">
        <button class="slogan-btn" id="prev-slogan" aria-label="Slogan précédent">← Précédent</button>
        <button class="slogan-btn" id="next-slogan" aria-label="Slogan suivant">Suivant →</button>
      </div>
      <div class="slogan-dots" role="tablist">
        <?php foreach ($slogans as $i => $s): ?>
        <button class="slogan-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" aria-label="Slogan <?= $i+1 ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<!-- CHIFFRES CHOC -->
<section class="chiffres-section">
  <div class="container">
    <div class="chiffres-grid">
      <div class="chiffre-item">
        <div class="chiffre-num">2 Mds€</div>
        <div class="chiffre-label">Économisés/an avec Linux dans toutes les admins</div>
        <div class="chiffre-source">Source : estimation AFUL / DSI État</div>
      </div>
      <div class="chiffre-item">
        <div class="chiffre-num">400 000</div>
        <div class="chiffre-label">Étudiants en informatique en France — un potentiel inexploité</div>
        <div class="chiffre-source">Source : Ministère de l'Enseignement Supérieur 2025</div>
      </div>
      <div class="chiffre-item">
        <div class="chiffre-num">97%</div>
        <div class="chiffre-label">Des serveurs du monde tournent sous Linux. Pas les admins françaises.</div>
        <div class="chiffre-source">Source : W3Techs / Linux Foundation 2025</div>
      </div>
      <div class="chiffre-item">
        <div class="chiffre-num">0€</div>
        <div class="chiffre-label">Coût de licence pour un OS souverain basé sur Linux/UNIX</div>
        <div class="chiffre-source">Open Source Initiative</div>
      </div>
    </div>
  </div>
</section>
<!-- VISION UNIX / LINUX -->
<section class="unix-section" id="vision-unix">
  <div class="unix-hero container">
    <div class="unix-text">
      <div class="tag">🛡️ RÉVOLUTION DÉFENSE & NUMÉRIQUE</div>
      <h2>Équiper l'armée française d'un OS <em>souverain</em> — maintenant.</h2>
      <p>La France dépense des milliards en licences Microsoft chaque année. Pour des logiciels dont le code source est fermé, hébergés sur des serveurs américains, potentiellement accessibles par la NSA.</p>
      <p>La solution existe. Elle s'appelle Linux. Elle est <strong>gratuite, auditée, souveraine</strong>. L'Allemagne l'a fait avec Munich. La Gendarmerie nationale française l'a fait avec GendBuntu. Il faut généraliser.</p>
      <p>Nos étudiants en informatique peuvent créer <strong>FranceOS</strong> — un système d'exploitation 100% français, certifié ANSSI, déployable dans toutes les administrations, écoles et hôpitaux.</p>
      <div class="unix-stats">
        <div class="unix-stat"><div class="num">+1800</div><div class="lab">postes GendBuntu (Gendarmerie déjà sur Linux)</div></div>
        <div class="unix-stat"><div class="num">0€</div><div class="lab">licence pour Linux vs 500€+/poste Microsoft</div></div>
        <div class="unix-stat"><div class="num">100%</div><div class="lab">code source auditable = zéro backdoor caché</div></div>
        <div class="unix-stat"><div class="num">Munich</div><div class="lab">Ville allemande passée à Linux : -8M€/an de coûts</div></div>
      </div>
    </div>
    <div class="unix-visual">
      <p style="color:var(--text2);font-size:0.82rem;margin-bottom:12px;font-family:system-ui">💻 Terminal FranceOS — Simulation</p>
      <div class="terminal">
        <div class="terminal-bar"><span class="t-red"></span><span class="t-yellow"></span><span class="t-green"></span></div>
        <div class="terminal-line"><span class="cmd">$ sudo apt install france-securite</span></div>
        <div class="terminal-line"><span class="ok">✓</span> Chiffrement AES-256 activé</div>
        <div class="terminal-line"><span class="ok">✓</span> Pare-feu ANSSI certifié</div>
        <div class="terminal-line"><span class="ok">✓</span> Données hébergées en France</div>
        <div class="terminal-line"><span class="ok">✓</span> Aucune télémétrie vers l'étranger</div>
        <div class="terminal-line"><span class="warn">!</span> Microsoft Windows détecté → migration proposée</div>
        <div class="terminal-line"><span class="cmd">$ franceOS --souverain --gratuit --libre</span></div>
        <div class="terminal-line"><span class="ok">✓</span> 🇫🇷 La France reprend le contrôle.<span class="cursor"></span></div>
      </div>
      <div style="margin-top:16px;padding:16px;background:rgba(5,150,105,.08);border:1px solid rgba(5,150,105,.2);border-radius:10px">
        <p style="color:#34d399;font-size:0.85rem;font-weight:700">🏆 Déjà fait avec succès :</p>
        <p style="color:var(--text2);font-size:0.82rem;margin-top:4px">GendBuntu (Gendarmerie) · LiMux Munich · Assemblée Nationale partielle · DINUM (partiellement)</p>
      </div>
    </div>
  </div>
</section>
<!-- PROJETS ÉTUDIANTS -->
<section class="students-section" id="projets-etudiants">
  <div class="container">
    <div class="students-header">
      <div class="students-tag">🎓 GÉNÉRATION citoyen</div>
      <h2 style="font-size:2.2rem;font-weight:900;margin-bottom:14px">Projets étudiants révolutionnaires</h2>
      <p style="color:var(--text2);font-size:1.05rem;max-width:700px;margin:0 auto">Ces étudiants français construisent l'indépendance technologique de la France. Pas à Washington, pas à Shanghai — <strong>à Paris, Lyon, Bordeaux, Rennes.</strong></p>
    </div>
    <div class="projects-grid">
      <?php foreach ($projets_etudiants as $p):
        $status_map = [
          'Disponible'         => 'status-available',
          'Beta ouverte'       => 'status-beta',
          'En développement'   => 'status-dev',
          'Concept'            => 'status-concept',
        ];
        $status_class = $status_map[$p['status']] ?? 'status-research';
      ?>
      <div class="project-card">
        <span class="project-status <?= $status_class ?>"><?= h($p['status']) ?></span>
        <h3 class="project-name">🔧 <?= h($p['nom']) ?></h3>
        <p class="project-desc"><?= h($p['desc']) ?></p>
        <div class="project-techs">
          <?php foreach ($p['techs'] as $tech): ?>
          <span class="tech-tag"><?= h($tech) ?></span>
          <?php endforeach; ?>
        </div>
        <a href="<?= h($p['lien']) ?>" class="project-link">
          <?= $p['lien'] === '#' ? 'Suivre le projet' : 'Accéder au projet' ?> →
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Appel à rejoindre -->
    <div class="student-cta">
      <h3>🚀 Tu es étudiant(e) en informatique, droit ou sciences ?</h3>
      <p>Rejoins le mouvement. Propose ton projet. Reçois du soutien, de la visibilité et des contacts dans l'écosystème citoyen français. <strong>Tes idées méritent d'être entendues.</strong></p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
        <a href="/contact/" class="btn-primary" style="background:white;color:#4c1d95">🎓 Proposer mon projet</a>
        <a href="/rejoindre/" class="btn-secondary" style="border-color:rgba(255,255,255,.3);color:white">Rejoindre le mouvement</a>
      </div>
    </div>
  </div>
</section>
<!-- FOOTER -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= h(SITE_NAME) ?> — <a href="/">Accueil</a> — <a href="/lois/">Lois françaises</a> — <a href="/proteger-documents/">Protéger vos docs</a></p>
    </div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
<script>
// ── Slogans rotatifs ──────────────────────────────────────────────
const slogans = <?= json_encode($slogans) ?>;
let current = 0;
let autoTimer = null;
function showSlogan(index) {
  current = (index + slogans.length) % slogans.length;
  const text = document.getElementById('slogan-text');
  const author = document.getElementById('slogan-author');
  const icon = document.getElementById('slogan-icon');
  if (!text) return;
  text.style.opacity = '0';
  text.style.transform = 'translateY(10px)';
  setTimeout(() => {
    text.textContent = slogans[current].texte;
    author.textContent = '— ' + slogans[current].auteur;
    icon.textContent = slogans[current].icon;
    text.style.opacity = '1';
    text.style.transform = 'translateY(0)';
  }, 300);
  document.querySelectorAll('.slogan-dot').forEach((d, i) => {
    d.classList.toggle('active', i === current);
  });
}
function startAuto() {
  stopAuto();
  autoTimer = setInterval(() => showSlogan(current + 1), 5000);
}
function stopAuto() {
  if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
}
document.getElementById('next-slogan')?.addEventListener('click', () => { stopAuto(); showSlogan(current + 1); startAuto(); });
document.getElementById('prev-slogan')?.addEventListener('click', () => { stopAuto(); showSlogan(current - 1); startAuto(); });
document.querySelectorAll('.slogan-dot').forEach((d, i) => {
  d.addEventListener('click', () => { stopAuto(); showSlogan(i); startAuto(); });
});
startAuto();
// Animation chiffres au scroll
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.querySelectorAll('.chiffre-num').forEach(el => {
        el.style.animation = 'fadeInUp .6s ease both';
      });
    }
  });
}, { threshold: 0.3 });
document.querySelectorAll('.chiffres-grid')?.forEach(el => observer.observe(el));
</script>
</body>
</html>

