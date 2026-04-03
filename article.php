<?php
/**
 * AgoraCMS — Page article
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
$slug = preg_replace('/[^a-z0-9\-]/', '', $_GET['slug'] ?? '');
if (!$slug) { header('Location: /'); exit; }
$article = get_article_by_slug($slug);
if (!$article) { http_response_code(404); include __DIR__ . '/404.php'; exit; }
increment_article_views((int)$article['id']);
$related  = get_related_articles($article['categorie'], (int)$article['id']);
$cat_info = get_category($article['categorie']);
$a11y     = sv_accessibility_score($article['contenu'], $article['titre'], $article['image_alt']);
$read_min = sv_reading_time($article['contenu']);
$meta_title = $article['meta_title'] ?: $article['titre'];
$meta_desc  = $article['meta_desc']  ?: ag_excerpt($article['extrait'] ?? $article['contenu'], 155);
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($meta_title) ?> — <?= h(SITE_NAME) ?></title>
<meta name="description" content="<?= h($meta_desc) ?>">
<meta name="author" content="<?= h($article['auteur_nom'] ?? 'Rédaction') ?>">
<link rel="canonical" href="<?= ag_canonical('article/' . h($slug) . '/') ?>">
<meta property="og:title" content="<?= h($meta_title) ?>">
<meta property="og:description" content="<?= h($meta_desc) ?>">
<meta property="og:type" content="article">
<meta property="og:url" content="<?= ag_canonical('article/' . h($slug) . '/') ?>">
<?php if ($article['image']): ?>
<meta property="og:image" content="<?= h(UPLOAD_URL . $article['image']) ?>">
<?php endif; ?>
<meta property="article:published_time" content="<?= $article['created_at'] ?>">
<meta property="article:section" content="<?= h($cat_info['nom'] ?? $article['categorie']) ?>">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "<?= addslashes(h($article['titre'])) ?>",
  "description": "<?= addslashes(h($meta_desc)) ?>",
  "datePublished": "<?= date('c', strtotime($article['created_at'])) ?>",
  "dateModified": "<?= date('c', strtotime($article['updated_at'])) ?>",
  "author": {"@type": "Person", "name": "<?= addslashes(h($article['auteur_nom'] ?? 'Rédaction')) ?>"},
  "publisher": {"@type": "Organization", "name": "<?= h(SITE_NAME) ?>"},
  "image": "<?= $article['image'] ? h(UPLOAD_URL . $article['image']) : '' ?>"
}
</script>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="header-top"><div class="tricolor-bar"><span></span><span></span><span></span></div></div>
  <div class="container header-inner">
    <a href="/" class="site-logo">
      <span class="logo-icon">🏛️</span>
      <div><div class="logo-name"><?= h(SITE_NAME) ?></div><div class="logo-tagline"><?= h(SITE_TAGLINE) ?></div></div>
    </a>
    <nav class="main-nav">
      <a href="/" class="nav-link">Accueil</a>
      <?php foreach (array_slice(CATEGORIES, 0, 6, true) as $slug_c => $c): ?>
      <a href="/categorie/<?= $slug_c ?>/" class="nav-link <?= $article['categorie'] === $slug_c ? 'active' : '' ?>"><?= $c['emoji'] ?> <?= h(explode(' ', $c['nom'])[0]) ?></a>
      <?php endforeach; ?>
      <a href="/handicap/" class="nav-link nav-handicap">♿ Handicap</a>
    </nav>
    <div class="header-actions">
      <button class="accessibility-btn" id="accessibility-btn" aria-label="Accessibilité">♿</button>
      <button class="hamburger" id="hamburger" aria-label="Menu">☰</button>
    </div>
  </div>
</header>
<div class="accessibility-panel" id="accessibility-panel" hidden>
  <div class="a11y-inner">
    <h3>♿ Accessibilité</h3>
    <div class="a11y-options">
      <button onclick="sv.fontSize(1)">A+ Grand</button>
      <button onclick="sv.fontSize(-1)">A- Petit</button>
      <button onclick="sv.toggleContrast()">🌗 Contraste</button>
      <button onclick="sv.toggleDyslexia()">📖 Dyslexie</button>
      <button onclick="sv.resetA11y()">↺ Reset</button>
    </div>
    <button class="a11y-close" onclick="sv.closeA11y()">✕</button>
  </div>
</div>
<!-- BREADCRUMB -->
<div class="breadcrumb" aria-label="Fil d'Ariane">
  <div class="container">
    <a href="/">Accueil</a> › 
    <?php if ($cat_info): ?>
    <a href="/categorie/<?= h($article['categorie']) ?>/"><?= $cat_info['emoji'] ?> <?= h($cat_info['nom']) ?></a> › 
    <?php endif; ?>
    <span><?= h(mb_substr($article['titre'], 0, 50)) ?>…</span>
  </div>
</div>
<main class="main-content" id="main">
  <div class="container">
    <div class="article-layout">
      <!-- Article principal -->
      <article class="article-full" itemscope itemtype="https://schema.org/NewsArticle">
        <!-- En-tête article -->
        <header class="article-header">
          <?php if ($cat_info): ?>
          <a href="/categorie/<?= h($article['categorie']) ?>/" class="cat-badge" style="background:<?= h($cat_info['couleur']) ?>">
            <?= $cat_info['emoji'] ?> <?= h($cat_info['nom']) ?>
          </a>
          <?php endif; ?>
          <h1 class="article-title" itemprop="headline"><?= h($article['titre']) ?></h1>
          <?php if ($article['extrait']): ?>
          <p class="article-lead"><?= h($article['extrait']) ?></p>
          <?php endif; ?>
          <div class="article-meta-bar">
            <div class="meta-author">
              <?php if ($article['auteur_avatar']): ?>
              <img src="<?= h(UPLOAD_URL . 'avatars/' . $article['auteur_avatar']) ?>" alt="" class="author-avatar" aria-hidden="true">
              <?php else: ?>
              <span class="author-initials"><?= mb_substr($article['auteur_nom'] ?? 'R', 0, 1) ?></span>
              <?php endif; ?>
              <div>
                <span class="author-name" itemprop="author"><?= h($article['auteur_nom'] ?? 'Rédaction') ?></span>
                <span class="article-date" itemprop="datePublished"><?= sv_format_date($article['created_at'], 'j F Y') ?></span>
              </div>
            </div>
            <div class="meta-stats">
              <span title="Temps de lecture">⏱ <?= $read_min ?> min de lecture</span>
              <span title="Vues">👁 <?= sv_format_number((int)$article['vues']) ?> vues</span>
              <!-- Score accessibilité -->
              <span class="a11y-score a11y-score-<?= $a11y['score'] >= 80 ? 'good' : ($a11y['score'] >= 50 ? 'medium' : 'low') ?>"
                    title="Score d'accessibilité pour les personnes handicapées">
                ♿ <?= $a11y['score'] ?>%
              </span>
            </div>
          </div>
        </header>
        <!-- Image principale -->
        <?php if ($article['image']): ?>
        <figure class="article-figure">
          <img src="<?= h(UPLOAD_URL . $article['image']) ?>"
               alt="<?= h($article['image_alt'] ?? $article['titre']) ?>"
               class="article-img" itemprop="image" loading="eager">
          <?php if ($article['image_alt']): ?>
          <figcaption><?= h($article['image_alt']) ?></figcaption>
          <?php endif; ?>
        </figure>
        <?php endif; ?>
        <!-- Contenu -->
        <div class="article-content" itemprop="articleBody">
          <?= $article['contenu'] ?>
        </div>
        <!-- Tags -->
        <?php if ($article['tags']): ?>
        <div class="article-tags">
          <?php foreach (explode(',', $article['tags']) as $tag): $tag = trim($tag); ?>
          <a href="/?q=<?= urlencode($tag) ?>" class="tag">#<?= h($tag) ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <!-- Partage social -->
        <div class="share-section">
          <p class="share-title">📢 Partager cet article</p>
          <div class="share-btns">
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(ag_canonical('article/'.$slug.'/')) ?>&text=<?= urlencode($article['titre']) ?>"
               target="_blank" rel="noopener" class="share-btn share-twitter">𝕏 Twitter/X</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(ag_canonical('article/'.$slug.'/')) ?>"
               target="_blank" rel="noopener" class="share-btn share-facebook">Facebook</a>
            <a href="https://t.me/share/url?url=<?= urlencode(ag_canonical('article/'.$slug.'/')) ?>&text=<?= urlencode($article['titre']) ?>"
               target="_blank" rel="noopener" class="share-btn share-telegram">Telegram</a>
            <button onclick="sv.copyLink()" class="share-btn share-copy">🔗 Copier le lien</button>
          </div>
        </div>
        <!-- Auteur bio -->
        <?php if ($article['auteur_bio']): ?>
        <div class="author-bio-box">
          <?php if ($article['auteur_avatar']): ?>
          <img src="<?= h(UPLOAD_URL . 'avatars/' . $article['auteur_avatar']) ?>" alt="" class="author-bio-avatar">
          <?php endif; ?>
          <div>
            <strong>✍️ <?= h($article['auteur_nom']) ?></strong>
            <p><?= h($article['auteur_bio']) ?></p>
          </div>
        </div>
        <?php endif; ?>
      </article>
      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="sidebar-widget">
          <h3 class="widget-title">📂 Catégories</h3>
          <ul class="cat-list">
            <?php foreach (CATEGORIES as $slug_c => $c): ?>
            <li><a href="/categorie/<?= $slug_c ?>/" class="cat-item <?= $article['categorie'] === $slug_c ? 'active' : '' ?>">
              <span><?= $c['emoji'] ?> <?= h($c['nom']) ?></span>
            </a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php if (!empty($related)): ?>
        <div class="sidebar-widget">
          <h3 class="widget-title">📰 Articles similaires</h3>
          <?php foreach ($related as $r): ?>
          <div class="related-item">
            <?php if ($r['image']): ?>
            <img src="<?= h(UPLOAD_URL . $r['image']) ?>" alt="<?= h($r['image_alt'] ?? $r['titre']) ?>" loading="lazy">
            <?php endif; ?>
            <div>
              <a href="/article/<?= h($r['slug']) ?>/"><?= h(mb_substr($r['titre'], 0, 70)) ?></a>
              <small><?= sv_format_date($r['created_at']) ?></small>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="sidebar-widget widget-cta">
          <h3>🤝 Rejoindre</h3>
          <p>Soutenez le mouvement citoyen.</p>
          <a href="/rejoindre/" class="btn-primary btn-full">Adhérer →</a>
        </div>
      </aside>
    </div>
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

