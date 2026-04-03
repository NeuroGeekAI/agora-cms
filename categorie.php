<?php
/**
 * AgoraCMS — Page catégorie
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
$cat_slug = preg_replace('/[^a-z0-9\-]/', '', $_GET['cat'] ?? '');
$cat_info = get_category($cat_slug);
if (!$cat_info) { header('Location: /'); exit; }
$page       = max(1, (int)($_GET['p'] ?? 1));
$articles   = get_articles($page, $cat_slug);
$total      = count_articles($cat_slug);
$total_pages = max(1, (int)ceil($total / ARTICLES_PER_PAGE));
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($cat_info['emoji'] . ' ' . $cat_info['nom']) ?> — <?= h(SITE_NAME) ?></title>
<meta name="description" content="Articles sur <?= h($cat_info['nom']) ?> — <?= h(SITE_NAME) ?>">
<link rel="canonical" href="<?= ag_canonical('categorie/' . $cat_slug . '/') ?>">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="header-top"><div class="tricolor-bar"><span></span><span></span><span></span></div></div>
  <div class="container header-inner">
    <a href="/" class="site-logo"><span class="logo-icon">🏛️</span><div><div class="logo-name"><?= h(SITE_NAME) ?></div></div></a>
    <nav class="main-nav">
      <a href="/" class="nav-link">Accueil</a>
      <?php foreach (CATEGORIES as $slug => $c): ?>
      <a href="/categorie/<?= $slug ?>/" class="nav-link <?= $cat_slug === $slug ? 'active' : '' ?>"><?= $c['emoji'] ?> <?= h(explode(' ', $c['nom'])[0]) ?></a>
      <?php endforeach; ?>
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
<div style="padding:60px 0 40px;text-align:center;background:<?= h($cat_info['couleur']) ?>18;border-bottom:1px solid var(--border)">
  <div style="font-size:3rem;margin-bottom:12px"><?= $cat_info['emoji'] ?></div>
  <h1 style="font-size:2rem;font-weight:900"><?= h($cat_info['nom']) ?></h1>
  <p style="color:var(--text2);margin-top:6px"><?= $total ?> article<?= $total > 1 ? 's' : '' ?></p>
</div>
<main class="main-content" id="main">
  <div class="container">
    <div class="content-layout">
      <section>
        <?php if (empty($articles)): ?>
        <div class="empty-state"><div class="empty-icon">📭</div><h3>Aucun article</h3><p>Aucun article dans cette catégorie pour l'instant.</p><a href="/" class="btn-primary">← Accueil</a></div>
        <?php else: ?>
        <div class="articles-grid">
          <?php foreach ($articles as $art): $ci = CATEGORIES[$art['categorie']] ?? null; ?>
          <article class="article-card">
            <a href="/article/<?= h($art['slug']) ?>/" tabindex="-1">
              <?php if ($art['image']): ?>
              <img src="<?= h(UPLOAD_URL . $art['image']) ?>" alt="<?= h($art['image_alt'] ?? $art['titre']) ?>" loading="lazy" class="card-img">
              <?php else: ?>
              <div class="card-img-placeholder" style="background:<?= h($cat_info['couleur']) ?>20"><span><?= $cat_info['emoji'] ?></span></div>
              <?php endif; ?>
            </a>
            <div class="card-body">
              <h3 class="card-title"><a href="/article/<?= h($art['slug']) ?>/"><?= h($art['titre']) ?></a></h3>
              <p class="card-excerpt"><?= h(ag_excerpt($art['extrait'] ?? $art['contenu'], 120)) ?></p>
              <div class="card-meta">
                <span>🗓️ <?= sv_time_ago($art['created_at']) ?></span>
                <span>⏱ <?= sv_reading_time($art['contenu']) ?> min</span>
                <span>👁 <?= sv_format_number((int)$art['vues']) ?></span>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <?= sv_pagination($page, $total_pages, '/categorie/' . $cat_slug . '/') ?>
        <?php endif; ?>
      </section>
      <aside class="sidebar">
        <div class="sidebar-widget">
          <h3 class="widget-title">📂 Autres catégories</h3>
          <ul class="cat-list">
            <?php foreach (CATEGORIES as $slug => $c): ?>
            <li><a href="/categorie/<?= $slug ?>/" class="cat-item <?= $cat_slug === $slug ? 'active' : '' ?>">
              <span class="cat-emoji"><?= $c['emoji'] ?></span>
              <span class="cat-name"><?= h($c['nom']) ?></span>
            </a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="sidebar-widget widget-cta">
          <h3>🤝 Rejoindre</h3>
          <a href="/rejoindre/" class="btn-primary btn-full">Adhérer →</a>
        </div>
      </aside>
    </div>
  </div>
</main>
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom"><p>© <?= date('Y') ?> <?= h(SITE_NAME) ?></p></div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>

