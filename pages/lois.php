<?php
/**
 * AgoraCMS — Lois Françaises en Clair
 * API data.gouv.fr + Légifrance public + explication citoyenne
 * Les lois sont publiques. Rendons-les lisibles.
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
check_maintenance();
$search_raw = strip_tags(trim($_GET['q'] ?? ''));
$search     = htmlspecialchars($search_raw, ENT_QUOTES, 'UTF-8');
$domaine = preg_replace('/[^a-zA-Z]/', '', $_GET['domaine'] ?? '');
// Lois emblématiques expliquées simplement (source : Légifrance officiel)
$lois_importantes = [
    [
        'titre'  => 'Loi RGPD (Règlement Général sur la Protection des Données)',
        'numero' => '2018-493 du 20 juin 2018',
        'resume' => 'Vous avez le droit de savoir quelles données sont collectées sur vous, de les corriger, de les supprimer. Toute entreprise ou site web doit obtenir votre consentement explicite AVANT de collecter vos données.',
        'droits' => ["Droit d'accès à vos données", "Droit de rectification", "Droit à l'oubli (effacement)", "Droit à la portabilité", "Droit d'opposition"],
        'source' => 'https://www.legifrance.gouv.fr/jorf/id/JORFTEXT000037085952',
        'categorie' => 'Numérique & Vie privée',
        'icone'  => '🔒',
        'importance' => 'CRITIQUE',
    ],
    [
        'titre'  => 'Loi pour une République Numérique',
        'numero' => '2016-1321 du 7 octobre 2016',
        'resume' => 'Les données produites par les services publics doivent être accessibles à tous (Open Data). Les logiciels financés par l\'État doivent être publiés en open source. Votre droit à l\'accès à internet est reconnu.',
        'droits' => ["Accès à l'Open Data gouvernemental", "Code source des logiciels publics disponible", "Portabilité de vos données", "Mort numérique (données après décès)"],
        'source' => 'https://www.legifrance.gouv.fr/loda/id/JORFTEXT000033202746',
        'categorie' => 'Numérique & Souveraineté',
        'icone'  => '💻',
        'importance' => 'IMPORTANT',
    ],
    [
        'titre'  => 'Loi HADOPI / Arcom — Droits sur Internet',
        'numero' => '2022-1159 du 12 août 2022',
        'resume' => 'Réglemente la lutte contre le piratage en ligne. Depuis 2022, l\'HADOPI devient l\'ARCOM. Toujours controversée car elle surveille vos connexions internet. Vous avez le droit de contester.',
        'droits' => ["Droit de contestation des sanctions", "Présomption d'innocence", "Accès à votre dossier ARCOM"],
        'source' => 'https://www.legifrance.gouv.fr/loda/id/JORFTEXT000046114944',
        'categorie' => 'Numérique & Liberté',
        'icone'  => '📡',
        'importance' => 'À CONNAÎTRE',
    ],
    [
        'titre'  => 'Loi sur le Handicap 2005',
        'numero' => '2005-102 du 11 février 2005',
        'resume' => 'Toute personne en situation de handicap a droit à la compensation, à l\'accessibilité et à l\'égalité des chances. La prestation de compensation du handicap (PCH) est votre droit — pas une faveur. Les employeurs publics et privés (+20 salariés) ont l\'obligation d\'employer 6% de personnes handicapées.',
        'droits' => ["PCH — Prestation de Compensation du Handicap", "Accessibilité universelle obligatoire", "6% d'emploi obligatoire pour les grandes entreprises", "MDPH — Maison Départementale des Personnes Handicapées", "Reconnaissance Qualité Travailleur Handicapé (RQTH)"],
        'source' => 'https://www.legifrance.gouv.fr/loda/id/JORFTEXT000000809647',
        'categorie' => 'Handicap & Droits',
        'icone'  => '♿',
        'importance' => 'CRITIQUE',
    ],
    [
        'titre'  => 'Loi sur la liberté de la presse',
        'numero' => 'Loi du 29 juillet 1881',
        'resume' => 'La liberté de la presse est garantie en France depuis 1881. Toute censure préalable est interdite. Vous avez le droit de publier, d\'informer et de critiquer le gouvernement et les élus dans les limites de la loi (pas de diffamation sans preuve, pas d\'incitation à la haine).',
        'droits' => ["Liberté de publication", "Droit à l'information", "Droit de réponse", "Protection des sources journalistiques"],
        'source' => 'https://www.legifrance.gouv.fr/loda/id/JORFTEXT000000877119',
        'categorie' => 'Liberté d\'expression',
        'icone'  => '🗣️',
        'importance' => 'FONDAMENTAL',
    ],
    [
        'titre'  => 'Déclaration des Droits de l\'Homme et du Citoyen',
        'numero' => '26 août 1789 — Valeur constitutionnelle',
        'resume' => 'Le texte fondateur de vos droits en France. Nul ne peut être arrêté sans motif légal, tout citoyen est présumé innocent, la propriété est inviolable, la liberté d\'opinion est garantie. Ces droits s\'appliquent TOUJOURS — en ligne comme hors ligne.',
        'droits' => ["Présomption d'innocence", "Liberté d'opinion et d'expression", "Propriété inviolable et sacrée", "Égalité devant la loi", "Résistance à l'oppression (Article 2)"],
        'source' => 'https://www.legifrance.gouv.fr/contenu/menu/droit-francais/constitution/declaration-des-droits-de-l-homme-et-du-citoyen-de-1789',
        'categorie' => 'Droits fondamentaux',
        'icone'  => '🏛️',
        'importance' => 'FONDAMENTAL',
    ],
];
// Appel API data.gouv.fr pour datasets publics sur les lois
$api_results = [];
$api_error   = '';
$api_query   = $search_raw ?: 'loi française 2026';
try {
    $api_url = 'https://www.data.gouv.fr/api/1/datasets/?q=' . urlencode($api_query) . '&sort=-created&page_size=6';
    $ctx = stream_context_create(['http' => [
        'timeout'  => 6,
        'header'   => 'User-Agent: AgoraCMS/1.0 (contact: ' . SITE_EMAIL . ')',
        'method'   => 'GET',
    ]]);
    $response = @file_get_contents($api_url, false, $ctx);
    if ($response) {
        $data = json_decode($response, true);
        $api_results = $data['data'] ?? [];
    } else {
        $api_error = 'API data.gouv.fr temporairement indisponible.';
    }
} catch (Throwable $e) {
    $api_error = 'Impossible de contacter l\'API gouvernementale.';
}
// Filtre lois importantes
$lois_filtrees = $lois_importantes;
if ($search_raw) {
    $lois_filtrees = array_filter($lois_importantes, function($l) use ($search_raw) {
        return stripos($l['titre'], $search_raw) !== false ||
               stripos($l['resume'], $search_raw) !== false ||
               stripos($l['categorie'], $search_raw) !== false;
    });
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Les Lois Françaises en Clair — <?= h(SITE_NAME) ?></title>
<meta name="description" content="Accédez aux lois françaises en vigueur en 2026, expliquées simplement. Source officielle data.gouv.fr et Légifrance. Vos droits, en langage citoyen.">
<link rel="canonical" href="<?= ag_canonical('lois/') ?>">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.lois-hero{background:linear-gradient(135deg,#0d1117 0%,#0a1428 100%);padding:70px 0;border-bottom:1px solid var(--border)}
.lois-search{display:flex;gap:10px;max-width:700px;margin:0 auto;margin-top:28px}
.lois-search input{flex:1;background:rgba(255,255,255,.06);border:2px solid var(--border);color:var(--text);padding:16px 20px;border-radius:14px;font-size:1rem;outline:none;transition:.2s}
.lois-search input:focus{border-color:var(--bleu);box-shadow:0 0 0 4px rgba(0,35,149,.15)}
.lois-search button{background:var(--bleu);color:white;border:none;padding:16px 28px;border-radius:14px;font-size:1rem;font-weight:700;cursor:pointer;white-space:nowrap;transition:.2s}
.lois-search button:hover{background:#0033cc}
.importance-badge{display:inline-block;font-size:0.72rem;font-weight:700;padding:4px 12px;border-radius:999px;margin-bottom:12px;letter-spacing:1px}
.imp-FONDAMENTAL{background:rgba(237,41,57,.15);color:#fca5a5;border:1px solid rgba(237,41,57,.3)}
.imp-CRITIQUE{background:rgba(124,58,237,.15);color:#a78bfa;border:1px solid rgba(124,58,237,.3)}
.imp-IMPORTANT{background:rgba(0,35,149,.15);color:#93c5fd;border:1px solid rgba(0,35,149,.3)}
.imp-À.CONNAÎTRE{background:rgba(5,150,105,.15);color:#34d399;border:1px solid rgba(5,150,105,.3)}
.loi-card{background:var(--dark2);border:1px solid var(--border);border-radius:20px;padding:28px;margin-bottom:20px;transition:.25s;position:relative}
.loi-card:hover{border-color:rgba(0,35,149,.5);transform:translateX(4px)}
.loi-header{display:flex;gap:16px;align-items:flex-start;margin-bottom:14px}
.loi-icon{font-size:2.5rem;flex-shrink:0;width:56px;height:56px;background:rgba(255,255,255,.04);border-radius:14px;display:flex;align-items:center;justify-content:center}
.loi-title-block{}
.loi-title{font-size:1.15rem;font-weight:800;color:white;margin-bottom:4px;line-height:1.35}
.loi-numero{font-size:0.8rem;color:var(--text2);font-family:monospace}
.loi-resume{color:var(--text2);font-size:0.95rem;line-height:1.7;margin-bottom:16px;background:rgba(255,255,255,.02);padding:16px;border-radius:10px;border-left:3px solid var(--bleu)}
.loi-droits{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px}
.droit-tag{background:rgba(5,150,105,.1);border:1px solid rgba(5,150,105,.25);color:#34d399;font-size:0.78rem;padding:5px 12px;border-radius:8px}
.loi-actions{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
.loi-source{display:inline-flex;align-items:center;gap:6px;color:#60a5fa;font-size:0.85rem;font-weight:600;transition:.2s}
.loi-source:hover{color:#93c5fd}
.loi-cat{background:var(--dark3);border:1px solid var(--border);color:var(--text2);font-size:0.78rem;padding:4px 12px;border-radius:999px}
.api-section{background:var(--dark2);border-radius:20px;padding:28px;border:1px solid var(--border);margin-top:40px}
.api-dataset{background:var(--dark3);border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:12px;transition:.2s}
.api-dataset:hover{border-color:rgba(0,35,149,.4)}
.api-dataset h4{font-size:0.95rem;font-weight:700;margin-bottom:6px;color:white}
.api-dataset p{font-size:0.85rem;color:var(--text2);margin-bottom:10px}
.api-dataset a{color:#60a5fa;font-size:0.85rem}
.disclaimer{background:rgba(217,119,6,.08);border:1px solid rgba(217,119,6,.25);border-radius:12px;padding:16px;margin-bottom:28px}
.disclaimer p{color:#fbbf24;font-size:0.88rem}
.ressources-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-top:20px}
.ressource-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;padding:20px;text-align:center;transition:.2s}
.ressource-card:hover{border-color:var(--bleu);transform:translateY(-4px)}
.ressource-card .r-icon{font-size:2rem;margin-bottom:10px;display:block}
.ressource-card h4{font-size:0.92rem;font-weight:700;color:white;margin-bottom:6px}
.ressource-card p{font-size:0.8rem;color:var(--text2);margin-bottom:12px}
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
      <a href="/lois/" class="nav-link active">⚖️ Lois</a>
      <a href="/debats/" class="nav-link">💬 Débats</a>
      <a href="/etudiants/" class="nav-link">🎓 Étudiants</a>
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
<section class="lois-hero">
  <div class="container" style="text-align:center">
    <div style="display:inline-block;background:rgba(0,35,149,.15);border:1px solid rgba(0,35,149,.4);color:#93c5fd;font-size:0.78rem;font-weight:700;padding:6px 18px;border-radius:999px;letter-spacing:2px;margin-bottom:20px">⚖️ SOURCE OFFICIELLE DATA.GOUV.FR + LÉGIFRANCE</div>
    <h1 style="font-size:3rem;font-weight:900;color:white;margin-bottom:16px">Les Lois Françaises<br><span style="color:#93c5fd">en langage humain</span></h1>
    <p style="color:var(--text2);font-size:1.05rem;max-width:700px;margin:0 auto 8px">Vos lois, vos droits — expliqués simplement. Directement depuis les sources officielles du gouvernement français. <strong style="color:white">Aucune censure. Aucune simplification politique.</strong></p>
    <p style="color:#fbbf24;font-size:0.85rem;margin-bottom:28px">⚠️ Pour toute décision juridique, consultez un avocat. Cette page est informative et citoyenne.</p>
    <form action="/lois/" method="GET" class="lois-search" role="search" aria-label="Rechercher dans les lois françaises">
      <input type="text" name="q" value="<?= h($search) ?>" placeholder="Rechercher une loi, un droit, un sujet..." aria-label="Rechercher une loi" autofocus>
      <button type="submit">🔍 Chercher</button>
    </form>
    <div style="margin-top:16px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;font-size:0.83rem">
      <span style="color:var(--text2)">Suggestions rapides :</span>
      <?php foreach (['RGPD', 'Handicap', 'Liberté expression', 'Linux État', 'Open Data'] as $tag): ?>
      <a href="/lois/?q=<?= urlencode($tag) ?>" style="color:#60a5fa;background:rgba(0,35,149,.1);padding:4px 12px;border-radius:999px;text-decoration:none;border:1px solid rgba(0,35,149,.3)"><?= h($tag) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<main class="main-content" id="main">
  <div class="container">
    <!-- Avertissement RGPD citoyen -->
    <div class="disclaimer">
      <p>⚠️ <strong>Information légale importante :</strong> Cette page présente des résumés citoyens des lois françaises. Toujours consulter <a href="https://www.legifrance.gouv.fr" target="_blank" rel="noopener" style="color:#fbbf24">Légifrance.gouv.fr</a> pour le texte officiel et intégral. En cas de litige, seul le texte officiel fait foi.</p>
    </div>
    <!-- Lois importantes expliquées -->
    <h2 style="font-size:1.5rem;font-weight:900;margin-bottom:24px">
      <?= $search ? '🔍 Résultats pour : ' . h($search) : '📜 Lois fondamentales à connaître en 2026' ?>
      <span style="font-size:0.9rem;font-weight:400;color:var(--text2)">(<?= count($lois_filtrees) ?> loi<?= count($lois_filtrees) > 1 ? 's' : '' ?>)</span>
    </h2>
    <?php if (empty($lois_filtrees)): ?>
    <div class="empty-state">
      <div class="empty-icon">⚖️</div>
      <h3>Aucune loi trouvée pour "<?= h($search) ?>"</h3>
      <p>Essayez un terme différent ou consultez directement <a href="https://www.legifrance.gouv.fr" target="_blank" style="color:#60a5fa">Légifrance.gouv.fr</a></p>
    </div>
    <?php else: ?>
    <?php foreach ($lois_filtrees as $loi):
      $imp_class = 'imp-' . str_replace(' ', '.', $loi['importance']);
    ?>
    <div class="loi-card">
      <div class="loi-header">
        <div class="loi-icon"><?= $loi['icone'] ?></div>
        <div class="loi-title-block">
          <span class="importance-badge <?= $imp_class ?>"><?= $loi['importance'] ?></span>
          <h3 class="loi-title"><?= h($loi['titre']) ?></h3>
          <div class="loi-numero">📋 <?= h($loi['numero']) ?></div>
        </div>
      </div>
      <div class="loi-resume">
        <strong style="color:white;display:block;margin-bottom:8px">💡 Ce que ça veut dire pour vous :</strong>
        <?= h($loi['resume']) ?>
      </div>
      <div style="margin-bottom:12px">
        <strong style="font-size:0.83rem;color:var(--text2);text-transform:uppercase;letter-spacing:1px">✅ Vos droits :</strong>
        <div class="loi-droits" style="margin-top:8px">
          <?php foreach ($loi['droits'] as $droit): ?>
          <span class="droit-tag">✓ <?= h($droit) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="loi-actions">
        <a href="<?= h($loi['source']) ?>" target="_blank" rel="noopener noreferrer" class="loi-source">
          🏛️ Lire le texte officiel sur Légifrance →
        </a>
        <span class="loi-cat">📂 <?= h($loi['categorie']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    <!-- Données API data.gouv.fr en temps réel -->
    <div class="api-section">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px">
        <div>
          <h2 style="font-size:1.3rem;font-weight:900;margin-bottom:4px">🌐 Données officielles — data.gouv.fr</h2>
          <p style="color:var(--text2);font-size:0.88rem">Résultats en temps réel de l'API ouverte du gouvernement français pour : <strong style="color:white">"<?= h($api_query) ?>"</strong></p>
        </div>
        <a href="https://www.data.gouv.fr" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:6px;background:rgba(0,35,149,.15);border:1px solid rgba(0,35,149,.3);color:#93c5fd;padding:8px 16px;border-radius:8px;font-size:0.83rem;text-decoration:none">
          🇫🇷 Voir data.gouv.fr →
        </a>
      </div>
      <?php if ($api_error): ?>
      <div style="background:rgba(217,119,6,.08);border:1px solid rgba(217,119,6,.25);border-radius:12px;padding:16px;color:#fbbf24">
        ⚠️ <?= h($api_error) ?> — <a href="https://www.data.gouv.fr" target="_blank" style="color:#fbbf24">Accéder directement à data.gouv.fr</a>
      </div>
      <?php elseif (empty($api_results)): ?>
      <p style="color:var(--text2)">Aucun dataset trouvé pour cette recherche.</p>
      <?php else: ?>
      <?php foreach ($api_results as $ds): ?>
      <div class="api-dataset">
        <h4><?= h(mb_substr($ds['title'] ?? 'Dataset sans titre', 0, 100)) ?></h4>
        <?php if (!empty($ds['description'])): ?>
        <p><?= h(ag_excerpt($ds['description'], 160)) ?></p>
        <?php endif; ?>
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
          <?php if (!empty($ds['page'])): ?>
          <a href="<?= h($ds['page']) ?>" target="_blank" rel="noopener">📂 Voir le dataset officiel →</a>
          <?php endif; ?>
          <?php if (!empty($ds['organization']['name'])): ?>
          <span style="color:var(--text2);font-size:0.78rem">🏛️ <?= h($ds['organization']['name']) ?></span>
          <?php endif; ?>
          <?php if (!empty($ds['created_at'])): ?>
          <span style="color:var(--text2);font-size:0.78rem">📅 <?= sv_format_date($ds['created_at']) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <!-- Ressources officielles -->
    <div style="margin-top:40px">
      <h2 style="font-size:1.3rem;font-weight:900;margin-bottom:8px">🏛️ Sources officielles — Vos droits, sans intermédiaire</h2>
      <p style="color:var(--text2);margin-bottom:20px">Ces sites sont financés par vos impôts. Utilisez-les librement.</p>
      <div class="ressources-grid">
        <a href="https://www.legifrance.gouv.fr" target="_blank" rel="noopener" class="ressource-card" style="text-decoration:none">
          <span class="r-icon">⚖️</span>
          <h4>Légifrance</h4>
          <p>Toutes les lois françaises, décrets et jurisprudences depuis 1789</p>
          <span style="color:#60a5fa;font-size:0.83rem">→ legifrance.gouv.fr</span>
        </a>
        <a href="https://www.data.gouv.fr" target="_blank" rel="noopener" class="ressource-card" style="text-decoration:none">
          <span class="r-icon">📊</span>
          <h4>Data.gouv.fr</h4>
          <p>Open Data officiel du gouvernement — toutes les données publiques françaises</p>
          <span style="color:#60a5fa;font-size:0.83rem">→ data.gouv.fr</span>
        </a>
        <a href="https://www.service-public.fr" target="_blank" rel="noopener" class="ressource-card" style="text-decoration:none">
          <span class="r-icon">🏛️</span>
          <h4>Service-Public.fr</h4>
          <p>Vos droits et démarches administratives en langage simple</p>
          <span style="color:#60a5fa;font-size:0.83rem">→ service-public.fr</span>
        </a>
        <a href="https://www.conseil-constitutionnel.fr" target="_blank" rel="noopener" class="ressource-card" style="text-decoration:none">
          <span class="r-icon">🔵</span>
          <h4>Conseil Constitutionnel</h4>
          <p>Contrôle la conformité des lois à la Constitution</p>
          <span style="color:#60a5fa;font-size:0.83rem">→ conseil-constitutionnel.fr</span>
        </a>
        <a href="https://www.cnil.fr" target="_blank" rel="noopener" class="ressource-card" style="text-decoration:none">
          <span class="r-icon">🔒</span>
          <h4>CNIL</h4>
          <p>Protège vos données personnelles. Portez plainte si vos droits RGPD sont violés.</p>
          <span style="color:#60a5fa;font-size:0.83rem">→ cnil.fr</span>
        </a>
        <a href="https://www.defenseurdesdroits.fr" target="_blank" rel="noopener" class="ressource-card" style="text-decoration:none">
          <span class="r-icon">🛡️</span>
          <h4>Défenseur des droits</h4>
          <p>Si vos droits sont violés par un service public, saisissez-le gratuitement.</p>
          <span style="color:#60a5fa;font-size:0.83rem">→ defenseurdesdroits.fr</span>
        </a>
      </div>
    </div>
  </div>
</main>
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= h(SITE_NAME) ?> — Données issues de <a href="https://www.legifrance.gouv.fr" target="_blank" style="color:#60a5fa">Légifrance</a> et <a href="https://www.data.gouv.fr" target="_blank" style="color:#60a5fa">data.gouv.fr</a> (sources officielles)</p>
    </div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>

