<?php
/**
 * AgoraCMS — Page Handicap & Innovation
 * Section unique en France : révolution inclusive + opportunités économiques
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
check_maintenance();
$articles_handicap = get_articles(1, 'handicap');
$count_handicap    = count_articles('handicap');
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>♿ Handicap & Innovation — <?= h(SITE_NAME) ?></title>
<meta name="description" content="La France compte 12 millions de personnes en situation de handicap. Découvrez les révolutions technologiques, législatives et économiques qui peuvent transformer leur vie et créer des milliards de valeur.">
<link rel="canonical" href="<?= ag_canonical('handicap/') ?>">
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
      <a href="/handicap/" class="nav-link active nav-handicap">♿ Handicap & Innovation</a>
      <a href="/manifeste/" class="nav-link">Programme</a>
      <a href="/rejoindre/" class="nav-link">Rejoindre</a>
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
      <button onclick="sv.fontSize(1)">A+ Grand</button>
      <button onclick="sv.fontSize(-1)">A- Petit</button>
      <button onclick="sv.toggleContrast()">🌗 Contraste élevé</button>
      <button onclick="sv.toggleDyslexia()">📖 Police dyslexie</button>
      <button onclick="sv.resetA11y()">↺ Réinitialiser</button>
    </div>
    <button class="a11y-close" onclick="sv.closeA11y()">✕</button>
  </div>
</div>
<!-- HERO HANDICAP -->
<section class="handicap-hero" aria-label="Section Handicap et Innovation">
  <div class="container">
    <div class="hh-content">
      <span class="hh-tag">PRIORITÉ NATIONALE — 12 MILLIONS DE FRANÇAIS</span>
      <h1>♿ Handicap & Innovation<br>La révolution silencieuse de la France</h1>
      <p class="hh-lead">La France ignore sa plus grande force économique et humaine. 12 millions de citoyens en situation de handicap, des technologies révolutionnaires disponibles, des milliards d'euros de marché inexploité. Il est temps d'agir.</p>
      <div class="hh-stats">
        <div class="hh-stat">
          <span class="hh-stat-num">12M</span>
          <span class="hh-stat-label">Français en situation de handicap</span>
        </div>
        <div class="hh-stat">
          <span class="hh-stat-num">80Mds€</span>
          <span class="hh-stat-label">Marché mondial des technologies d'assistance</span>
        </div>
        <div class="hh-stat">
          <span class="hh-stat-num">18%</span>
          <span class="hh-stat-label">De la population française concernée</span>
        </div>
        <div class="hh-stat">
          <span class="hh-stat-num">N°1</span>
          <span class="hh-stat-label">Priorité du programme citoyen</span>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- RÉVOLUTIONS TECHNOLOGIQUES -->
<section class="handi-revolutions">
  <div class="container">
    <h2 class="section-title">🚀 Les révolutions technologiques à exploiter maintenant</h2>
    <p class="section-sub">Ces innovations existent. La France doit les déployer massivement — pour ses citoyens ET pour son économie.</p>
    <div class="revolutions-grid">
      <div class="revolution-card">
        <div class="rev-icon">🧠</div>
        <h3>IA & Interfaces cognitives</h3>
        <p>L'intelligence artificielle peut traduire la pensée en texte pour les personnes atteintes de paralysie. Des entreprises comme Neuralink, Synchron (utilisables en France) ouvrent des possibilités révolutionnaires.</p>
        <div class="rev-market">Marché estimé : <strong>25 Mds€/an</strong></div>
        <div class="rev-france">🇫🇷 Potentiel France : Startups à créer, emplois à générer, leadership européen à prendre</div>
      </div>
      <div class="revolution-card">
        <div class="rev-icon">🦾</div>
        <h3>Exosquelettes & Membres bioniques</h3>
        <p>Les exosquelettes permettent aux personnes paraplégiques de marcher. La France a des champions cachés (Wandercraft, Proteor). Il faut les soutenir et déployer à grande échelle dans nos hôpitaux.</p>
        <div class="rev-market">Marché estimé : <strong>15 Mds€/an</strong></div>
        <div class="rev-france">🇫🇷 Champion français : Wandercraft — À financer massivement !</div>
      </div>
      <div class="revolution-card">
        <div class="rev-icon">👁️</div>
        <h3>Vision artificielle pour malvoyants</h3>
        <p>Les caméras IA peuvent décrire l'environnement en temps réel pour les personnes malvoyantes ou aveugles. Solutions : Be My Eyes, Microsoft Seeing AI, Luminance. Déploiement en France : quasi inexistant.</p>
        <div class="rev-market">Marché estimé : <strong>8 Mds€/an</strong></div>
        <div class="rev-france">🇫🇷 Urgence : Former les travailleurs sociaux à ces outils dès 2026</div>
      </div>
      <div class="revolution-card">
        <div class="rev-icon">🗣️</div>
        <h3>Communication augmentée (CAA)</h3>
        <p>Pour les personnes autistes, aphasiques ou atteintes de trisomie. Les applications de communication par pictogrammes et synthèse vocale révolutionnent l'autonomie. En France : sous-financées et méconnues.</p>
        <div class="rev-market">Marché estimé : <strong>5 Mds€/an</strong></div>
        <div class="rev-france">🇫🇷 Solution : Remboursement par la Sécurité Sociale = économies sur les soins !</div>
      </div>
      <div class="revolution-card">
        <div class="rev-icon">🏠</div>
        <h3>Habitat inclusif intelligent</h3>
        <p>La domotique adaptée permet aux personnes en situation de handicap de vivre chez elles en autonomie. Cela coûte moins cher que l'hébergement en ESAT. La France investit mal : priorité au collectif plutôt qu'à l'autonomie.</p>
        <div class="rev-market">Marché estimé : <strong>12 Mds€/an</strong></div>
        <div class="rev-france">🇫🇷 Proposition : Crédit d'impôt domotique adaptée étendu à 100%</div>
      </div>
      <div class="revolution-card">
        <div class="rev-icon">💼</div>
        <h3>Emploi & Télétravail adapté</h3>
        <p>Le télétravail a révélé que des milliers de personnes handicapées peuvent travailler avec les bonnes adaptations numériques. En France, le taux d'emploi des personnes handicapées stagne à 36% — contre 72% pour la population générale.</p>
        <div class="rev-market">Gain économique : <strong>+ 30 Mds€/an</strong> si taux d'emploi égalisé</div>
        <div class="rev-france">🇫🇷 Urgence : Réforme AGEFIPH + formation numérique accessible</div>
      </div>
    </div>
  </div>
</section>
<!-- PROPOSITIONS CONCRÈTES -->
<section class="handi-propositions">
  <div class="container">
    <h2 class="section-title">⚖️ Nos propositions concrètes — Applicables dès 2027</h2>
    <div class="propositions-list">
      <div class="proposition">
        <span class="prop-num">01</span>
        <div class="prop-content">
          <h3>Créer un Ministère de l'Innovation Inclusive</h3>
          <p>Un ministère dédié au handicap ET à l'innovation technologique pour les personnes en situation de handicap. Budget : 2 Mds€/an prélevés sur la sur-bureaucratisation actuelle.</p>
          <div class="prop-impact">💰 Impact économique estimé : +15 Mds€ PIB à 5 ans</div>
        </div>
      </div>
      <div class="proposition">
        <span class="prop-num">02</span>
        <div class="prop-content">
          <h3>Plan National Domotique & IA Accessible</h3>
          <p>Subvention à 80% pour l'adaptation numérique du domicile de toute personne en situation de handicap. Financé par la réduction des coûts d'hébergement institutionnel.</p>
          <div class="prop-impact">👥 Bénéficiaires directs : 500 000 personnes en 3 ans</div>
        </div>
      </div>
      <div class="proposition">
        <span class="prop-num">03</span>
        <div class="prop-content">
          <h3>Label "Numérique Accessible" obligatoire</h3>
          <p>Toute application et tout site web français DOIT respecter WCAG 2.2 AA minimum. Amendes pour non-conformité. Contrats publics réservés aux entreprises certifiées.</p>
          <div class="prop-impact">🎯 Résultat : 0 exclusion numérique d'ici 2030</div>
        </div>
      </div>
      <div class="proposition">
        <span class="prop-num">04</span>
        <div class="prop-content">
          <h3>Objectif 50% d'emploi pour les personnes handicapées</h3>
          <p>Réforme profonde : télétravail adapté comme droit, formations numériques accessibles gratuites, obligation de résultat pour les grandes entreprises (au lieu des simples quotas).</p>
          <div class="prop-impact">💪 + 500 000 emplois créés / économies Sécurité Sociale : 8 Mds€/an</div>
        </div>
      </div>
      <div class="proposition">
        <span class="prop-num">05</span>
        <div class="prop-content">
          <h3>Fonds Souverain Innovation Handicap (FSIH)</h3>
          <p>500 M€ pour financer des startups françaises dans les technologies d'assistance : exosquelettes, IA conversationnelle, interfaces cerveau-machine, CAA avancée. Objectif : devenir le leader européen.</p>
          <div class="prop-impact">🇫🇷 Objectif : 50 startups françaises leaders mondiaux d'ici 2030</div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- ARTICLES HANDICAP -->
<?php if (!empty($articles_handicap)): ?>
<section class="handi-articles">
  <div class="container">
    <h2 class="section-title">📰 Derniers articles — Handicap & Innovation</h2>
    <div class="articles-grid">
      <?php foreach ($articles_handicap as $art): ?>
      <article class="article-card">
        <?php if ($art['image']): ?>
        <img src="<?= h(UPLOAD_URL . $art['image']) ?>" alt="<?= h($art['image_alt'] ?? $art['titre']) ?>" loading="lazy" class="card-img">
        <?php else: ?>
        <div class="card-img-placeholder" style="background:#7c3aed20"><span>♿</span></div>
        <?php endif; ?>
        <div class="card-body">
          <h3 class="card-title"><a href="/article/<?= h($art['slug']) ?>/"><?= h($art['titre']) ?></a></h3>
          <p class="card-excerpt"><?= h(ag_excerpt($art['extrait'] ?? $art['contenu'], 120)) ?></p>
          <div class="card-meta">
            <span>🗓️ <?= sv_time_ago($art['created_at']) ?></span>
            <span>⏱ <?= sv_reading_time($art['contenu']) ?> min</span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<!-- CTA REJOINDRE -->
<section class="handicap-cta">
  <div class="container">
    <div class="hcta-inner">
      <h2>🤝 Ensemble, faisons de l'inclusion une force nationale</h2>
      <p>Rejoignez le mouvement qui met le handicap au cœur de la souveraineté française. Pas par pitié — par intelligence économique et humanisme.</p>
      <div class="hcta-btns">
        <a href="/rejoindre/" class="btn-primary">Rejoindre le mouvement</a>
        <a href="/contact/" class="btn-secondary">Témoigner / Contribuer</a>
      </div>
    </div>
  </div>
</section>
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= h(SITE_NAME) ?> — <a href="/mentions-legales/">Mentions légales</a> — <a href="/confidentialite/">Confidentialité</a></p>
      <p style="font-size:0.8rem;color:#6b7280;margin-top:4px">Ce site respecte les standards WCAG 2.1 pour l'accessibilité numérique</p>
    </div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>

