<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * AgoraCMS — Page d'accueil
 * ═══════════════════════════════════════════════════════════════
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
check_maintenance();
$page    = max(1, (int)($_GET['p'] ?? 1));
$cat     = preg_replace('/[^a-z0-9\-]/', '', $_GET['cat'] ?? '');
$search_raw      = strip_tags(trim($_GET['q'] ?? ''));
$search          = htmlspecialchars($search_raw, ENT_QUOTES, 'UTF-8');
$articles        = get_articles($page, $cat, $search_raw);
$total           = count_articles($cat, $search_raw);
$total_pages     = max(1, (int)ceil($total / ARTICLES_PER_PAGE));
$featured        = get_featured_articles(3);
$counts_by_cat   = get_articles_count_by_cat();
$current_cat     = $cat ? get_category($cat) : null;
$page_title      = $current_cat ? $current_cat['nom'] : ($search ? "Recherche : $search" : '');
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sv_meta_title($page_title) ?></title>
<meta name="description" content="<?= h(SITE_TAGLINE) ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= ag_canonical() ?>">
<meta property="og:title" content="<?= sv_meta_title($page_title) ?>">
<meta property="og:description" content="<?= h(SITE_TAGLINE) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= ag_canonical() ?>">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="icon" href="/assets/img/favicon.ico">
</head>
<body>
<!-- ── HEADER ─────────────────────────────────────────────────── -->
<header class="site-header">
  <div class="header-top">
    <div class="tricolor-bar"><span></span><span></span><span></span></div>
  </div>
  <div class="container header-inner">
    <a href="/" class="site-logo">
      <span class="logo-icon">🏛️</span>
      <div>
        <div class="logo-name"><?= h(SITE_NAME) ?></div>
        <div class="logo-tagline"><?= h(SITE_TAGLINE) ?></div>
      </div>
    </a>
    <nav class="main-nav" id="main-nav">
      <a href="/" class="nav-link <?= !$cat && !$search ? 'active' : '' ?>">Accueil</a>
      <a href="/manifeste/" class="nav-link">🔥 Manifeste</a>
      <a href="/lois/" class="nav-link">⚖️ Lois</a>
      <a href="/debats/" class="nav-link">💬 Débats</a>
      <?php foreach (array_slice(CATEGORIES, 0, 4, true) as $slug => $c): ?>
      <a href="/categorie/<?= $slug ?>/" class="nav-link <?= $cat === $slug ? 'active' : '' ?>">
        <?= $c['emoji'] ?> <?= h(explode(' ', $c['nom'])[0]) ?>
      </a>
      <?php endforeach; ?>
      <a href="/handicap/" class="nav-link nav-handicap">♿ Handicap</a>
      <a href="/proteger-documents/" class="nav-link" style="color:#fbbf24">🔒 Docs</a>
    </nav>
    <div class="header-actions">
      <form action="/" method="GET" class="search-form" id="search-form">
        <input type="text" name="q" value="<?= h($search) ?>" placeholder="Rechercher..." aria-label="Rechercher">
        <button type="submit" aria-label="Lancer la recherche">🔍</button>
      </form>
      <button class="accessibility-btn" id="accessibility-btn" aria-label="Options d'accessibilité">♿</button>
      <button class="hamburger" id="hamburger" aria-label="Menu">☰</button>
    </div>
  </div>
</header>
<!-- ── PANEL ACCESSIBILITÉ ────────────────────────────────────── -->
<div class="accessibility-panel" id="accessibility-panel" role="dialog" aria-label="Options d'accessibilité" hidden>
  <div class="a11y-inner">
    <h3>♿ Options d'accessibilité</h3>
    <div class="a11y-options">
      <button onclick="sv.fontSize(1)" aria-label="Augmenter la taille du texte">A+ Texte grand</button>
      <button onclick="sv.fontSize(-1)" aria-label="Réduire la taille du texte">A- Texte petit</button>
      <button onclick="sv.toggleContrast()" aria-label="Activer le mode contraste élevé">🌗 Contraste élevé</button>
      <button onclick="sv.toggleDyslexia()" aria-label="Police dyslexie">📖 Police dyslexie</button>
      <button onclick="sv.resetA11y()" aria-label="Réinitialiser">↺ Réinitialiser</button>
    </div>
    <button class="a11y-close" onclick="sv.closeA11y()" aria-label="Fermer">✕ Fermer</button>
  </div>
</div>
<!-- ── BANDE SLOGANS ROTATIVE ─────────────────────────────────── -->
<?php if (!$cat && !$search && $page === 1): ?>
<div id="slogan-ticker" style="background:linear-gradient(90deg,var(--bleu),#1a2a8f,var(--rouge));padding:10px 0;overflow:hidden;position:relative">
  <div id="ticker-track" style="display:flex;align-items:center;white-space:nowrap;animation:ticker 40s linear infinite;gap:80px;padding:0 40px">
    <?php $ticker_slogans = [
      "🛡️ Équiper l'armée française de Linux — indépendance numérique totale",
      "⚖️ Vos lois en clair — accédez à vos droits sans avocat",
      "🎓 Soutenez les étudiants qui construisent l'OS souverain français",
      "🔒 Protégez vos documents — filigrane 100% local, rien sur les serveurs",
      "💬 Débattez librement — arguments structurés pour et contre",
      "♿ 12 millions de Français handicapés = une force économique dormante",
      "💻 97% des serveurs mondiaux tournent sous Linux — pas les admins françaises",
      "🇫🇷 La France arrête de subir. Elle décide.",
    ]; ?>
    <?php foreach (array_merge($ticker_slogans, $ticker_slogans) as $ts): ?>
    <span style="color:rgba(255,255,255,.9);font-size:0.88rem;font-weight:600;flex-shrink:0"><?= h($ts) ?></span>
    <span style="color:rgba(255,255,255,.4);flex-shrink:0">•</span>
    <?php endforeach; ?>
  </div>
</div>
<style>
@keyframes ticker{from{transform:translateX(0)}to{transform:translateX(-50%)}}
#slogan-ticker:hover #ticker-track{animation-play-state:paused}
</style>
<?php endif; ?>
<!-- ── HERO (seulement accueil sans filtre) ───────────────────── -->
<?php if (!$cat && !$search && $page === 1 && !empty($featured)): ?>
<section class="hero" aria-label="Articles à la une">
  <div class="container">
    <div class="hero-grid">
      <?php $main = $featured[0]; ?>
      <article class="hero-main">
        <?php if ($main['image']): ?>
        <img src="<?= h(UPLOAD_URL . $main['image']) ?>" alt="<?= h($main['image_alt'] ?? $main['titre']) ?>" class="hero-img" loading="eager">
        <?php endif; ?>
        <div class="hero-content">
          <span class="cat-badge" style="background:<?= h(CATEGORIES[$main['categorie']]['couleur'] ?? '#002395') ?>">
            <?= CATEGORIES[$main['categorie']]['emoji'] ?? '' ?> <?= h(CATEGORIES[$main['categorie']]['nom'] ?? $main['categorie']) ?>
          </span>
          <h1 class="hero-title"><a href="/article/<?= h($main['slug']) ?>/"><?= h($main['titre']) ?></a></h1>
          <p class="hero-excerpt"><?= h(ag_excerpt($main['extrait'] ?? $main['contenu'], 180)) ?></p>
          <div class="hero-meta">
            <span>✍️ <?= h($main['auteur_nom'] ?? 'Rédaction') ?></span>
            <span>🗓️ <?= sv_format_date($main['created_at']) ?></span>
            <span>👁 <?= sv_format_number((int)$main['vues']) ?></span>
            <span>⏱ <?= sv_reading_time($main['contenu']) ?> min</span>
          </div>
          <a href="/article/<?= h($main['slug']) ?>/" class="btn-lire">Lire l'article →</a>
        </div>
      </article>
      <?php if (count($featured) > 1): ?>
      <div class="hero-side">
        <?php foreach (array_slice($featured, 1, 2) as $art): ?>
        <article class="hero-side-item">
          <?php if ($art['image']): ?>
          <img src="<?= h(UPLOAD_URL . $art['image']) ?>" alt="<?= h($art['image_alt'] ?? $art['titre']) ?>" loading="lazy">
          <?php endif; ?>
          <div>
            <span class="cat-badge-sm" style="color:<?= h(CATEGORIES[$art['categorie']]['couleur'] ?? '#002395') ?>">
              <?= CATEGORIES[$art['categorie']]['emoji'] ?? '' ?> <?= h(explode(' ', CATEGORIES[$art['categorie']]['nom'] ?? '')[0]) ?>
            </span>
            <h2><a href="/article/<?= h($art['slug']) ?>/"><?= h(mb_substr($art['titre'], 0, 80)) ?></a></h2>
            <p class="side-meta"><?= sv_format_date($art['created_at']) ?> · <?= sv_reading_time($art['contenu']) ?> min</p>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<!-- ── BANNIÈRES THÉMATIQUES ──────────────────────────────────── -->
<section style="padding:0" aria-label="Sections thématiques">
  <!-- Bannière Lois -->
  <div style="background:linear-gradient(135deg,#001a4d 0%,#002395 100%);padding:28px 0;border-bottom:1px solid rgba(255,255,255,.08)">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap">
      <div>
        <span style="display:inline-block;background:rgba(255,255,255,.15);color:rgba(255,255,255,.9);font-size:0.72rem;font-weight:700;padding:3px 12px;border-radius:999px;letter-spacing:2px;margin-bottom:8px">⚖️ VOS DROITS</span>
        <h2 style="font-size:1.2rem;font-weight:800;color:white;margin-bottom:4px">Lois françaises en clair — Directement depuis data.gouv.fr</h2>
        <p style="color:rgba(255,255,255,.75);font-size:0.9rem">Arrêtons d'infantiliser les citoyens. Vos lois sont publiques — rendons-les lisibles sans avocat.</p>
      </div>
      <a href="/lois/" style="display:inline-flex;align-items:center;padding:12px 24px;background:white;color:#002395;border-radius:12px;font-weight:700;white-space:nowrap;text-decoration:none;flex-shrink:0;transition:.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">⚖️ Consulter les lois →</a>
    </div>
  </div>
  <!-- Bannière Protection docs -->
  <div style="background:linear-gradient(135deg,#0a2818 0%,#064e35 100%);padding:28px 0;border-bottom:1px solid rgba(255,255,255,.08)">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap">
      <div>
        <span style="display:inline-block;background:rgba(52,211,153,.15);color:#34d399;font-size:0.72rem;font-weight:700;padding:3px 12px;border-radius:999px;letter-spacing:2px;margin-bottom:8px">🔒 100% LOCAL — ZÉRO SERVEUR</span>
        <h2 style="font-size:1.2rem;font-weight:800;color:white;margin-bottom:4px">Protégez vos documents avec un filigrane — rien n'est envoyé</h2>
        <p style="color:rgba(255,255,255,.75);font-size:0.9rem">Outil gratuit, souverain, 100% dans votre navigateur. Vos fichiers ne quittent jamais votre appareil.</p>
      </div>
      <a href="/proteger-documents/" style="display:inline-flex;align-items:center;padding:12px 24px;background:#34d399;color:#064e35;border-radius:12px;font-weight:700;white-space:nowrap;text-decoration:none;flex-shrink:0;transition:.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">🔒 Protéger mes docs →</a>
    </div>
  </div>
  <!-- Bannière Handicap -->
  <div style="background:linear-gradient(135deg,#4c1d95 0%,#6d28d9 50%,#7c3aed 100%);padding:28px 0">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap">
      <div>
        <span style="display:inline-block;background:rgba(255,255,255,.2);color:white;font-size:0.72rem;font-weight:700;padding:3px 12px;border-radius:999px;letter-spacing:2px;margin-bottom:8px">PRIORITÉ NATIONALE</span>
        <h2 style="font-size:1.2rem;font-weight:800;color:white;margin-bottom:4px">♿ Handicap & Innovation — 12 millions de Français, des milliards d'opportunités</h2>
        <p style="color:rgba(255,255,255,.8);font-size:0.9rem">La France ignore sa plus grande révolution silencieuse. Technologie, lois, opportunités économiques.</p>
      </div>
      <a href="/handicap/" style="display:inline-flex;align-items:center;padding:12px 24px;background:white;color:#6d28d9;border-radius:12px;font-weight:700;white-space:nowrap;text-decoration:none;flex-shrink:0;transition:.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">♿ Explorer la section →</a>
    </div>
  </div>
</section>
<?php endif; ?>
<!-- ── CONTENU PRINCIPAL ──────────────────────────────────────── -->
<main class="main-content" id="main">
  <div class="container">
    <div class="content-layout">
      <!-- Articles -->
      <section class="articles-section">
        <?php if ($current_cat || $search): ?>
        <div class="section-header">
          <h2><?php
            if ($current_cat) echo $current_cat['emoji'] . ' ' . h($current_cat['nom']);
            else echo '🔍 Résultats pour : ' . h($search);
          ?></h2>
          <span class="results-count"><?= $total ?> article<?= $total > 1 ? 's' : '' ?></span>
        </div>
        <?php endif; ?>
        <?php if (empty($articles)): ?>
        <div class="empty-state">
          <div class="empty-icon">📭</div>
          <h3>Aucun article trouvé</h3>
          <p><?= $search ? "Aucun résultat pour « $search »" : 'Aucun article dans cette catégorie pour le moment.' ?></p>
          <a href="/" class="btn-primary">← Retour à l'accueil</a>
        </div>
        <?php else: ?>
        <div class="articles-grid">
          <?php foreach ($articles as $art): $cat_info = CATEGORIES[$art['categorie']] ?? null; ?>
          <article class="article-card">
            <a href="/article/<?= h($art['slug']) ?>/" class="card-img-link" tabindex="-1" aria-hidden="true">
              <?php if ($art['image']): ?>
              <img src="<?= h(UPLOAD_URL . $art['image']) ?>" alt="<?= h($art['image_alt'] ?? $art['titre']) ?>" loading="lazy" class="card-img">
              <?php else: ?>
              <div class="card-img-placeholder" style="background:<?= h($cat_info['couleur'] ?? '#002395') ?>20">
                <span><?= $cat_info['emoji'] ?? '📰' ?></span>
              </div>
              <?php endif; ?>
            </a>
            <div class="card-body">
              <?php if ($cat_info): ?>
              <a href="/categorie/<?= h($art['categorie']) ?>/" class="cat-badge-sm" style="color:<?= h($cat_info['couleur']) ?>">
                <?= $cat_info['emoji'] ?> <?= h($cat_info['nom']) ?>
              </a>
              <?php endif; ?>
              <h3 class="card-title">
                <a href="/article/<?= h($art['slug']) ?>/"><?= h($art['titre']) ?></a>
              </h3>
              <p class="card-excerpt"><?= h(ag_excerpt($art['extrait'] ?? $art['contenu'], 120)) ?></p>
              <div class="card-meta">
                <span>✍️ <?= h($art['auteur_nom'] ?? 'Rédaction') ?></span>
                <span>🗓️ <?= sv_time_ago($art['created_at']) ?></span>
                <span>👁 <?= sv_format_number((int)$art['vues']) ?></span>
                <?php if ((int)$art['access_score'] >= 90): ?>
                <span class="a11y-badge" title="Article accessible aux personnes handicapées">♿ Accessible</span>
                <?php endif; ?>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <?= sv_pagination($page, $total_pages, $search ? '/?q=' . urlencode($search) : ($cat ? '/categorie/' . $cat . '/' : '/')) ?>
        <?php endif; ?>
      </section>
      <!-- Sidebar -->
      <aside class="sidebar" role="complementary">
        <!-- Catégories -->
        <div class="sidebar-widget">
          <h3 class="widget-title">📂 Catégories</h3>
          <ul class="cat-list">
            <?php foreach (CATEGORIES as $slug => $c): ?>
            <li>
              <a href="/categorie/<?= $slug ?>/" class="cat-item <?= $cat === $slug ? 'active' : '' ?>">
                <span class="cat-emoji"><?= $c['emoji'] ?></span>
                <span class="cat-name"><?= h($c['nom']) ?></span>
                <span class="cat-count"><?= $counts_by_cat[$slug] ?? 0 ?></span>
              </a>
            </li>
            <?php endforeach; ?>
            <li>
              <a href="/handicap/" class="cat-item cat-handicap">
                <span class="cat-emoji">♿</span>
                <span class="cat-name">Handicap & Innovation</span>
                <span class="cat-arrow">→</span>
              </a>
            </li>
          </ul>
        </div>
        <!-- Pétitions actives -->
        <?php $petitions = Database::fetchAll("SELECT * FROM ag_petitions WHERE statut='active' ORDER BY created_at DESC LIMIT 3"); ?>
        <?php if (!empty($petitions)): ?>
        <div class="sidebar-widget">
          <h3 class="widget-title">✊ Pétitions actives</h3>
          <?php foreach ($petitions as $p): $pct = min(100, round($p['signatures'] / max(1, $p['objectif']) * 100)); ?>
          <div class="petition-mini">
            <a href="/petition/<?= h($p['slug']) ?>/"><?= h(mb_substr($p['titre'], 0, 60)) ?></a>
            <div class="petition-bar">
              <div class="petition-fill" style="width:<?= $pct ?>%"></div>
            </div>
            <small><?= number_format($p['signatures']) ?> / <?= number_format($p['objectif']) ?> signatures (<?= $pct ?>%)</small>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <!-- Rejoindre -->
        <!-- Outils citoyens -->
        <div class="sidebar-widget">
          <h3 class="widget-title">🔧 Outils Citoyens</h3>
          <div style="display:flex;flex-direction:column;gap:8px">
            <a href="/lois/" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(0,35,149,.08);border:1px solid rgba(0,35,149,.2);border-radius:10px;color:var(--text);text-decoration:none;transition:.2s" onmouseover="this.style.borderColor='#002395'" onmouseout="this.style.borderColor='rgba(0,35,149,.2)'">
              <span>⚖️</span><div><div style="font-size:0.85rem;font-weight:600;color:white">Lois en clair</div><div style="font-size:0.75rem;color:var(--text2)">API data.gouv.fr officielle</div></div>
            </a>
            <a href="/proteger-documents/" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(5,150,105,.06);border:1px solid rgba(5,150,105,.2);border-radius:10px;color:var(--text);text-decoration:none;transition:.2s" onmouseover="this.style.borderColor='#059669'" onmouseout="this.style.borderColor='rgba(5,150,105,.2)'">
              <span>🔒</span><div><div style="font-size:0.85rem;font-weight:600;color:white">Filigrane sécurisé</div><div style="font-size:0.75rem;color:#34d399">100% local — zéro serveur</div></div>
            </a>
            <a href="/debats/" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(237,41,57,.06);border:1px solid rgba(237,41,57,.2);border-radius:10px;color:var(--text);text-decoration:none;transition:.2s" onmouseover="this.style.borderColor='#ED2939'" onmouseout="this.style.borderColor='rgba(237,41,57,.2)'">
              <span>💬</span><div><div style="font-size:0.85rem;font-weight:600;color:white">Débats citoyens</div><div style="font-size:0.75rem;color:var(--text2)">Voter, argumenter librement</div></div>
            </a>
            <a href="/manifeste/" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(124,58,237,.06);border:1px solid rgba(124,58,237,.2);border-radius:10px;color:var(--text);text-decoration:none;transition:.2s">
              <span>🔥</span><div><div style="font-size:0.85rem;font-weight:600;color:white">Manifeste & Projets</div><div style="font-size:0.75rem;color:var(--text2)">Vision Linux + étudiants</div></div>
            </a>
          </div>
        </div>
        <div class="sidebar-widget widget-cta">
          <h3>🤝 Rejoindre le mouvement</h3>
          <p>Adhérez et participez à la construction d'une France souveraine et inclusive.</p>
          <a href="/rejoindre/" class="btn-primary btn-full">Rejoindre maintenant</a>
        </div>
      </aside>
    </div>
  </div>
</main>
<!-- ── FOOTER ─────────────────────────────────────────────────── -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">🏛️ <?= h(SITE_NAME) ?></div>
        <p><?= h(SITE_TAGLINE) ?></p>
        <div class="footer-flags">🇫🇷 Made in France — Hébergé en France</div>
      </div>
      <div class="footer-links">
        <h4>Navigation</h4>
        <a href="/">Accueil</a>
        <a href="/handicap/">♿ Handicap & Innovation</a>
        <a href="/manifeste/">Notre programme</a>
        <a href="/rejoindre/">Rejoindre</a>
        <a href="/contact/">Contact</a>
      </div>
      <div class="footer-links">
        <h4>Légal</h4>
        <a href="/mentions-legales/">Mentions légales</a>
        <a href="/confidentialite/">Confidentialité</a>
        <a href="/sitemap.xml">Sitemap XML</a>
      </div>
      <div class="footer-links">
        <h4>Accessibilité</h4>
        <p style="font-size:0.85rem;color:#9ca3af">Ce site respecte les standards WCAG 2.1 et s'engage pour l'inclusion numérique de toutes les personnes en situation de handicap.</p>
        <button onclick="sv.toggleContrast()" class="btn-a11y-footer">🌗 Mode contraste</button>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= h(SITE_NAME) ?> — <?= h(SITE_TAGLINE) ?> — RGPD ✅ — Liberté d'expression 🗣️</p>
      <p style="font-size:0.75rem;color:#6b7280;margin-top:4px">Propulsé par AgoraCMS — CMS 100% français, 100% indépendant</p>
    </div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>

