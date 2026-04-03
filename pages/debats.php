<?php
/**
 * AgoraCMS — Débats Citoyens Structurés
 * Pour / Contre avec vote anonyme sécurisé
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
check_maintenance();
// Gestion vote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote'])) {
    if (!csrf_verify()) die('CSRF invalide');
    $debat_id = (int)($_POST['debat_id'] ?? 0);
    $position = in_array($_POST['vote'], ['pour','contre']) ? $_POST['vote'] : '';
    if ($debat_id && $position) {
        $ip_hash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . SECRET_KEY . date('Y-m-d'));
        $exists  = Database::count("SELECT COUNT(*) FROM ag_debat_votes WHERE debat_id=? AND ip_hash=?", [$debat_id, $ip_hash]);
        if (!$exists) {
            Database::query("INSERT INTO ag_debat_votes (debat_id, position, ip_hash) VALUES (?,?,?)", [$debat_id, $position, $ip_hash]);
            Database::query("UPDATE ag_debats SET votes_$position = votes_$position + 1 WHERE id=?", [$debat_id]);
        }
    }
    header('Location: /debats/#debat-' . $debat_id);
    exit;
}
$debats = Database::fetchAll(
    "SELECT * FROM ag_debats WHERE statut='ouvert' ORDER BY featured DESC, created_at DESC"
);
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Débats Citoyens — <?= h(SITE_NAME) ?></title>
<meta name="description" content="Participez aux débats de votre communauté. Votez, argumentez, défendez vos positions honnêtement.">
<link rel="canonical" href="<?= ag_canonical('debats/') ?>">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.debats-hero{background:linear-gradient(135deg,#1a0005 0%,#0d1117 50%,#00051a 100%);padding:70px 0;border-bottom:1px solid var(--border);text-align:center}
.debat-card{background:var(--dark2);border:1px solid var(--border);border-radius:20px;padding:32px;margin-bottom:28px;transition:.25s}
.debat-card.featured{border-color:rgba(237,41,57,.4);background:linear-gradient(180deg,rgba(237,41,57,.04) 0%,var(--dark2) 60%)}
.debat-card:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.4)}
.debat-tag{display:inline-block;font-size:0.72rem;font-weight:700;padding:4px 12px;border-radius:999px;margin-bottom:14px;letter-spacing:1px}
.tag-featured{background:rgba(237,41,57,.15);color:#fca5a5;border:1px solid rgba(237,41,57,.3)}
.tag-ouvert{background:rgba(5,150,105,.15);color:#34d399;border:1px solid rgba(5,150,105,.3)}
.debat-title{font-size:1.3rem;font-weight:900;color:white;margin-bottom:12px;line-height:1.35}
.debat-desc{color:var(--text2);font-size:0.92rem;line-height:1.7;margin-bottom:24px}
.debat-args{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
.arg-side{border-radius:14px;padding:20px}
.arg-pour{background:rgba(5,150,105,.06);border:1px solid rgba(5,150,105,.25)}
.arg-contre{background:rgba(237,41,57,.06);border:1px solid rgba(237,41,57,.25)}
.arg-title{font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.arg-pour .arg-title{color:#34d399}
.arg-contre .arg-title{color:#fca5a5}
.arg-list{list-style:none;padding:0;font-size:0.87rem;color:var(--text2);line-height:1.6}
.arg-list li{padding:4px 0;display:flex;gap:8px}
.arg-pour .arg-list li::before{content:'✓';color:#34d399;font-weight:700;flex-shrink:0}
.arg-contre .arg-list li::before{content:'✗';color:#fca5a5;font-weight:700;flex-shrink:0}
.vote-section{display:flex;gap:16px;align-items:center;flex-wrap:wrap}
.vote-bar-wrap{flex:1;min-width:200px}
.vote-bar-row{display:flex;height:12px;border-radius:999px;overflow:hidden}
.vbar-pour{background:linear-gradient(90deg,#34d399,#059669);transition:width .8s}
.vbar-contre{background:linear-gradient(90deg,#ED2939,#991b1b);transition:width .8s}
.vote-stats{display:flex;justify-content:space-between;margin-top:6px;font-size:0.82rem}
.vstat-pour{color:#34d399;font-weight:700}
.vstat-contre{color:#fca5a5;font-weight:700}
.btn-vote{padding:12px 24px;border:none;border-radius:12px;font-size:0.9rem;font-weight:700;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:8px}
.btn-pour{background:rgba(5,150,105,.15);border:2px solid rgba(5,150,105,.4);color:#34d399}
.btn-pour:hover{background:rgba(5,150,105,.3);transform:translateY(-2px)}
.btn-contre{background:rgba(237,41,57,.12);border:2px solid rgba(237,41,57,.35);color:#fca5a5}
.btn-contre:hover{background:rgba(237,41,57,.25);transform:translateY(-2px)}
.vote-note{font-size:0.78rem;color:var(--text2);margin-top:6px}
@media(max-width:768px){.debat-args{grid-template-columns:1fr}.vote-section{flex-direction:column;align-items:stretch}}
</style>
</head>
<body>
<header class="site-header">
  <div class="header-top"><div class="tricolor-bar"><span></span><span></span><span></span></div></div>
  <div class="container header-inner">
    <a href="/" class="site-logo"><span class="logo-icon">🏛️</span><div><div class="logo-name"><?= h(SITE_NAME) ?></div></div></a>
    <nav class="main-nav" id="main-nav">
      <a href="/" class="nav-link">Accueil</a>
      <a href="/manifeste/" class="nav-link">🔥 Manifeste</a>
      <a href="/lois/" class="nav-link">⚖️ Lois</a>
      <a href="/debats/" class="nav-link active">💬 Débats</a>
      <a href="/proteger-documents/" class="nav-link">🔒 Docs</a>
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
<section class="debats-hero">
  <div class="container">
    <div style="display:inline-block;background:rgba(237,41,57,.12);border:1px solid rgba(237,41,57,.3);color:#fca5a5;font-size:0.78rem;font-weight:700;padding:6px 18px;border-radius:999px;letter-spacing:2px;margin-bottom:20px">💬 DÉBATS — ARGUMENTS STRUCTURÉS</div>
    <h1 style="font-size:3rem;font-weight:900;color:white;margin-bottom:16px"><?= h(SITE_NAME) ?> débat.<br><span style="color:#fca5a5">Librement, honnêtement.</span></h1>
    <p style="color:var(--text2);font-size:1.05rem;max-width:700px;margin:0 auto;line-height:1.8">Pas de clash, pas d'insultes — des arguments <strong style="color:white">pour</strong> et <strong style="color:white">contre</strong>, structurés et présentés honnêtement. Votez en conscience. <span style="color:#fbbf24">1 vote anonyme par débat par jour.</span></p>
  </div>
</section>
<!-- DÉBATS -->
<main class="main-content" id="main">
  <div class="container" style="max-width:900px">
    <?php if (empty($debats)): ?>
    <div class="empty-state" style="margin-top:40px">
      <div class="empty-icon">💬</div>
      <h3>Aucun débat ouvert pour l'instant</h3>
      <p>Les débats arrivent bientôt. Proposez un sujet via le formulaire contact.</p>
      <a href="/contact/" class="btn-primary" style="margin-top:16px">Proposer un débat →</a>
    </div>
    <?php else: ?>
    <?php foreach ($debats as $d):
      $total_votes = $d['votes_pour'] + $d['votes_contre'];
      $pct_pour    = $total_votes > 0 ? round($d['votes_pour'] / $total_votes * 100) : 50;
      $pct_contre  = 100 - $pct_pour;
      $args_pour   = $d['pour']    ? array_filter(array_map('trim', explode("\n", $d['pour'])))   : [];
      $args_contre = $d['contre']  ? array_filter(array_map('trim', explode("\n", $d['contre']))) : [];
    ?>
    <div class="debat-card <?= $d['featured'] ? 'featured' : '' ?>" id="debat-<?= $d['id'] ?>">
      <div>
        <?php if ($d['featured']): ?><span class="debat-tag tag-featured">⭐ DÉBAT PHARE</span><?php endif; ?>
        <span class="debat-tag tag-ouvert">🟢 Ouvert — <?= number_format($total_votes) ?> vote<?= $total_votes > 1 ? 's' : '' ?></span>
      </div>
      <h2 class="debat-title"><?= h($d['titre']) ?></h2>
      <p class="debat-desc"><?= h($d['description']) ?></p>
      <!-- Arguments -->
      <?php if (!empty($args_pour) || !empty($args_contre)): ?>
      <div class="debat-args">
        <?php if (!empty($args_pour)): ?>
        <div class="arg-side arg-pour">
          <div class="arg-title">✅ Arguments POUR</div>
          <ul class="arg-list">
            <?php foreach (array_slice($args_pour, 0, 5) as $arg): ?>
            <li><?= h(preg_replace('/^\d+\.\s*/', '', $arg)) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        <?php if (!empty($args_contre)): ?>
        <div class="arg-side arg-contre">
          <div class="arg-title">❌ Arguments CONTRE</div>
          <ul class="arg-list">
            <?php foreach (array_slice($args_contre, 0, 5) as $arg): ?>
            <li><?= h(preg_replace('/^\d+\.\s*/', '', $arg)) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <!-- Vote + Barre -->
      <div class="vote-section">
        <div class="vote-bar-wrap">
          <div class="vote-bar-row">
            <div class="vbar-pour" style="width:<?= $pct_pour ?>%"></div>
            <div class="vbar-contre" style="width:<?= $pct_contre ?>%"></div>
          </div>
          <div class="vote-stats">
            <span class="vstat-pour">✅ <?= $pct_pour ?>% POUR (<?= number_format($d['votes_pour']) ?>)</span>
            <span class="vstat-contre">❌ <?= $pct_contre ?>% CONTRE (<?= number_format($d['votes_contre']) ?>)</span>
          </div>
        </div>
        <form method="POST" style="display:flex;gap:10px;flex-shrink:0">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="debat_id" value="<?= $d['id'] ?>">
          <button type="submit" name="vote" value="pour" class="btn-vote btn-pour">👍 POUR</button>
          <button type="submit" name="vote" value="contre" class="btn-vote btn-contre">👎 CONTRE</button>
        </form>
      </div>
      <p class="vote-note">🔒 Vote anonyme — 1 vote par jour par débat — IP hashée, non stockée en clair</p>
    </div>
    <?php endforeach; ?>
    <!-- Proposer un débat -->
    <div style="text-align:center;padding:40px;background:var(--dark2);border:1px dashed var(--border);border-radius:20px;margin-top:20px">
      <h3 style="font-size:1.3rem;font-weight:800;margin-bottom:10px">💡 Proposer un débat citoyen</h3>
      <p style="color:var(--text2);margin-bottom:20px">Vous avez une question importante à soumettre au débat public ? Envoyez-la nous. Nous la structurerons avec des arguments <strong style="color:white">honnêtes pour les deux positions.</strong></p>
      <a href="/contact/" class="btn-primary">Proposer un sujet de débat →</a>
    </div>
    <?php endif; ?>
  </div>
</main>
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= h(SITE_NAME) ?> — Débats citoyens modérés — <a href="/mentions-legales/">Mentions légales</a></p>
    </div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>

