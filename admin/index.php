<?php
/**
 * AgoraCMS — Dashboard Admin
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
// ── LOGIN ─────────────────────────────────────────────────────────────
if (!admin_logged()) {
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) die('CSRF invalide');
        $u = trim($_POST['username'] ?? '');
        $p = $_POST['password'] ?? '';
        $user = Database::fetch("SELECT * FROM ag_admin_users WHERE username = ?", [$u]);
        if ($user && password_verify($p, $user['password_hash'])) {
            $_SESSION['sv_admin'] = ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']];
            Database::query("UPDATE ag_admin_users SET last_login=? WHERE id=?", [date('Y-m-d H:i:s'), $user['id']]);
            header('Location: /admin/'); exit;
        }
        $err = "Identifiants incorrects.";
    }
    ?><!DOCTYPE html>
    <html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex"><title>Admin — <?= h(SITE_NAME) ?></title>
    <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#0d1117;color:#e5e7eb;font-family:system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center}
    .tribar{position:fixed;top:0;left:0;right:0;height:4px;display:flex}
    .tribar span:nth-child(1){flex:1;background:#002395}.tribar span:nth-child(2){flex:1;background:#fff}.tribar span:nth-child(3){flex:1;background:#ED2939}
    .box{background:#161b22;border:1px solid #2d3748;border-radius:20px;padding:48px 40px;width:100%;max-width:420px;text-align:center}
    .box-logo{font-size:2.5rem;margin-bottom:8px}
    .box-title{font-size:1.5rem;font-weight:900;color:white;margin-bottom:4px}
    .box-sub{color:#9ca3af;font-size:0.9rem;margin-bottom:32px}
    .field{margin-bottom:16px;text-align:left}
    label{display:block;font-size:0.85rem;color:#9ca3af;margin-bottom:6px;font-weight:600}
    input{width:100%;background:#0d1117;border:1px solid #2d3748;color:#e5e7eb;padding:13px 16px;border-radius:10px;font-size:0.95rem;outline:none;transition:.2s}
    input:focus{border-color:#002395;box-shadow:0 0 0 3px rgba(0,35,149,.2)}
    .btn{width:100%;padding:14px;background:#002395;color:white;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:.2s;margin-top:8px}
    .btn:hover{background:#0033cc}
    .err{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:12px;border-radius:8px;margin-bottom:16px;font-size:0.88rem}
    .back{margin-top:24px;color:#6b7280;font-size:0.85rem}
    .back a{color:#6b7280;text-decoration:none}
    </style></head>
    <body><div class="tribar"><span></span><span></span><span></span></div>
    <div class="box">
      <div class="box-logo">🏛️</div>
      <div class="box-title"><?= h(SITE_NAME) ?></div>
      <div class="box-sub">Administration — Accès restreint</div>
      <?php if ($err): ?><div class="err">⚠️ <?= h($err) ?></div><?php endif; ?>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="field"><label>Identifiant</label><input type="text" name="username" required autocomplete="username" autofocus></div>
        <div class="field"><label>Mot de passe</label><input type="password" name="password" required autocomplete="current-password"></div>
        <button type="submit" class="btn">Connexion →</button>
      </form>
      <p class="back"><a href="/">← Retour au site</a></p>
    </div></body></html>
    <?php exit;
}
// ── STATS DASHBOARD ───────────────────────────────────────────────────
$stats = [
    'articles'   => Database::count("SELECT COUNT(*) FROM ag_articles WHERE statut='publie'"),
    'brouillons' => Database::count("SELECT COUNT(*) FROM ag_articles WHERE statut='brouillon'"),
    'vues'       => Database::count("SELECT COALESCE(SUM(vues),0) FROM ag_articles"),
    'messages'   => Database::count("SELECT COUNT(*) FROM ag_messages WHERE lu=0"),
    'petitions'  => Database::count("SELECT COUNT(*) FROM ag_petitions WHERE statut='active'"),
    'signatures' => Database::count("SELECT COALESCE(SUM(signatures),0) FROM ag_petitions"),
];
$recent_articles = Database::fetchAll(
    "SELECT a.*, u.nom AS auteur_nom FROM ag_articles a
     LEFT JOIN ag_auteurs u ON u.id = a.auteur_id
     ORDER BY a.created_at DESC LIMIT 8"
);
$cat_stats = Database::fetchAll(
    "SELECT categorie, COUNT(*) as n, SUM(vues) as vues FROM ag_articles
     WHERE statut='publie' GROUP BY categorie ORDER BY n DESC"
);
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex"><title>Dashboard — <?= h(SITE_NAME) ?> Admin</title>
<style>
:root{--bleu:#002395;--rouge:#ED2939;--dark:#0d1117;--dark2:#161b22;--dark3:#1f2937;--text:#e5e7eb;--muted:#9ca3af;--border:#2d3748;--green:#059669;--orange:#d97706}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--dark);color:var(--text);font-family:system-ui,sans-serif;display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--dark2);border-right:1px solid var(--border);padding:0;flex-shrink:0;position:fixed;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
.sidebar-top{padding:20px 16px;border-bottom:1px solid var(--border)}
.sb-logo{font-size:1.1rem;font-weight:900;color:white;display:flex;align-items:center;gap:8px}
.sb-user{font-size:0.78rem;color:var(--muted);margin-top:4px}
.sb-nav{padding:12px 8px;flex:1}
.sb-section{font-size:0.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;padding:12px 8px 6px;font-weight:700}
.sb-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:var(--muted);font-size:0.88rem;text-decoration:none;margin-bottom:2px;transition:.2s}
.sb-link:hover,.sb-link.active{background:rgba(255,255,255,.07);color:white}
.sb-link .badge{margin-left:auto;background:var(--rouge);color:white;font-size:0.7rem;padding:2px 7px;border-radius:999px}
.sb-bottom{padding:12px 8px;border-top:1px solid var(--border)}
.tribar{height:4px;display:flex}
.tribar span:nth-child(1){flex:1;background:var(--bleu)}.tribar span:nth-child(2){flex:1;background:white}.tribar span:nth-child(3){flex:1;background:var(--rouge)}
.main{flex:1;margin-left:240px;display:flex;flex-direction:column}
.topbar{background:var(--dark2);border-bottom:1px solid var(--border);padding:16px 32px;display:flex;align-items:center;justify-content:space-between}
.topbar h1{font-size:1.3rem;font-weight:800}
.topbar-actions{display:flex;gap:10px}
.content{padding:32px;flex:1}
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:32px}
.stat-card{background:var(--dark2);border:1px solid var(--border);border-radius:16px;padding:24px;transition:.2s}
.stat-card:hover{border-color:var(--bleu)}
.stat-icon{font-size:2rem;margin-bottom:10px}
.stat-val{font-size:2.2rem;font-weight:900;color:white}
.stat-label{color:var(--muted);font-size:0.85rem;margin-top:4px}
.stat-card.alert{border-color:rgba(237,41,57,.3);background:rgba(237,41,57,.05)}
.stat-card.alert .stat-val{color:#fca5a5}
.grid2{display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:32px}
.card{background:var(--dark2);border:1px solid var(--border);border-radius:16px;padding:24px}
.card-title{font-size:0.9rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border)}
table{width:100%;border-collapse:collapse}
th{padding:10px 14px;text-align:left;color:var(--muted);font-size:0.78rem;text-transform:uppercase;background:rgba(255,255,255,.02);border-bottom:1px solid var(--border)}
td{padding:10px 14px;border-bottom:1px solid var(--border);font-size:0.88rem;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(255,255,255,.02)}
.badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:0.73rem;font-weight:700}
.badge-publie{background:rgba(5,150,105,.15);color:#34d399}
.badge-brouillon{background:rgba(217,119,6,.15);color:#fbbf24}
.badge-archive{background:rgba(107,114,128,.15);color:#9ca3af}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:.2s}
.btn-primary{background:var(--bleu);color:white}.btn-primary:hover{background:#0033cc}
.btn-sm{padding:5px 10px;font-size:0.78rem}
.btn-danger{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.cat-bar{display:flex;flex-direction:column;gap:10px}
.cat-bar-item label{display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:4px;color:var(--muted)}
.bar{height:8px;background:var(--dark3);border-radius:999px;overflow:hidden}
.bar-fill{height:100%;background:linear-gradient(90deg,var(--bleu),#0055ff);border-radius:999px;transition:width 1s}
.welcome-box{background:linear-gradient(135deg,#002395,#ED2939);border-radius:16px;padding:28px 32px;margin-bottom:32px;display:flex;align-items:center;justify-content:space-between}
.welcome-box h2{font-size:1.3rem;font-weight:800;color:white;margin-bottom:4px}
.welcome-box p{color:rgba(255,255,255,.8);font-size:0.9rem}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="tribar"><span></span><span></span><span></span></div>
  <div class="sidebar-top">
    <div class="sb-logo">🏛️ <?= h(SITE_NAME) ?></div>
    <div class="sb-user">👤 <?= h($_SESSION['sv_admin']['username']) ?> — <?= h($_SESSION['sv_admin']['role']) ?></div>
  </div>
  <nav class="sb-nav">
    <div class="sb-section">Contenu</div>
    <a href="/admin/" class="sb-link active">📊 Dashboard</a>
    <a href="/admin/articles/" class="sb-link">📰 Articles</a>
    <a href="/admin/edit/" class="sb-link">✏️ Nouvel article</a>
    <a href="/admin/medias/" class="sb-link">🖼️ Médias</a>
    <div class="sb-section">Communauté</div>
    <a href="/admin/petitions/" class="sb-link">✊ Pétitions</a>
    <a href="/admin/messages/" class="sb-link">📬 Messages
      <?php if ($stats['messages'] > 0): ?><span class="badge"><?= $stats['messages'] ?></span><?php endif; ?>
    </a>
    <div class="sb-section">Système</div>
    <a href="/admin/settings/" class="sb-link">⚙️ Paramètres</a>
    <a href="/" class="sb-link" target="_blank">🌐 Voir le site</a>
  </nav>
  <div class="sb-bottom">
    <a href="/admin/?logout=1" class="sb-link" style="color:#fca5a5">🚪 Déconnexion</a>
  </div>
</aside>
<div class="main">
  <div class="topbar">
    <h1>📊 Dashboard</h1>
    <div class="topbar-actions">
      <a href="/admin/edit/" class="btn btn-primary">✏️ Nouvel article</a>
    </div>
  </div>
  <div class="content">
    <?php if (isset($_GET['logout'])): session_destroy(); header('Location: /admin/'); exit; endif; ?>
    <!-- Bienvenue -->
    <div class="welcome-box">
      <div>
        <h2>🇫🇷 Bienvenue, <?= h($_SESSION['sv_admin']['username']) ?> !</h2>
        <p><?= h(SITE_NAME) ?> — <?= h(SITE_TAGLINE) ?></p>
      </div>
      <a href="/admin/edit/" class="btn btn-primary" style="background:white;color:var(--bleu)">✏️ Écrire un article</a>
    </div>
    <!-- Statistiques -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📰</div>
        <div class="stat-val"><?= number_format($stats['articles']) ?></div>
        <div class="stat-label">Articles publiés</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">👁</div>
        <div class="stat-val"><?= number_format($stats['vues']) ?></div>
        <div class="stat-label">Vues totales</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✊</div>
        <div class="stat-val"><?= number_format($stats['signatures']) ?></div>
        <div class="stat-label">Signatures pétitions</div>
      </div>
      <div class="stat-card <?= $stats['brouillons'] > 0 ? '' : '' ?>">
        <div class="stat-icon">📝</div>
        <div class="stat-val"><?= $stats['brouillons'] ?></div>
        <div class="stat-label">Brouillons en attente</div>
      </div>
      <div class="stat-card <?= $stats['messages'] > 0 ? 'alert' : '' ?>">
        <div class="stat-icon">📬</div>
        <div class="stat-val"><?= $stats['messages'] ?></div>
        <div class="stat-label">Messages non lus</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✊</div>
        <div class="stat-val"><?= $stats['petitions'] ?></div>
        <div class="stat-label">Pétitions actives</div>
      </div>
    </div>
    <!-- Articles récents + Stats catégories -->
    <div class="grid2">
      <div class="card">
        <div class="card-title">📰 Articles récents</div>
        <table>
          <thead><tr><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Vues</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($recent_articles as $a):
              $cat_info = CATEGORIES[$a['categorie']] ?? null; ?>
            <tr>
              <td><strong><?= h(mb_substr($a['titre'], 0, 55)) ?></strong><br><small style="color:var(--muted)"><?= sv_time_ago($a['created_at']) ?></small></td>
              <td><?= $cat_info ? $cat_info['emoji'] . ' ' . h(explode(' ', $cat_info['nom'])[0]) : h($a['categorie']) ?></td>
              <td><span class="badge badge-<?= $a['statut'] ?>"><?= $a['statut'] === 'publie' ? '✅ Publié' : '📝 Brouillon' ?></span></td>
              <td>👁 <?= number_format((int)$a['vues']) ?></td>
              <td style="display:flex;gap:6px">
                <a href="/admin/edit/?id=<?= $a['id'] ?>" class="btn btn-sm" style="background:rgba(0,35,149,.2);border:1px solid rgba(0,35,149,.4);color:#93c5fd">✏️</a>
                <?php if ($a['statut'] === 'publie'): ?>
                <a href="/article/<?= h($a['slug']) ?>/" class="btn btn-sm" style="background:rgba(5,150,105,.15);border:1px solid rgba(5,150,105,.3);color:#34d399" target="_blank">👁</a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div style="text-align:center;margin-top:16px">
          <a href="/admin/articles/" class="btn btn-primary btn-sm">Voir tous les articles →</a>
        </div>
      </div>
      <!-- Stats catégories -->
      <div class="card">
        <div class="card-title">📂 Articles par catégorie</div>
        <?php
        $max_articles = max(1, array_reduce($cat_stats, fn($c, $r) => max($c, $r['n']), 0));
        foreach ($cat_stats as $cs):
          $cat_i = CATEGORIES[$cs['categorie']] ?? null;
          $pct = round($cs['n'] / $max_articles * 100);
        ?>
        <div class="cat-bar-item" style="margin-bottom:14px">
          <label>
            <span><?= $cat_i ? $cat_i['emoji'] . ' ' . h(explode(' ', $cat_i['nom'])[0]) : h($cs['categorie']) ?></span>
            <span><?= $cs['n'] ?> article<?= $cs['n'] > 1 ? 's' : '' ?></span>
          </label>
          <div class="bar"><div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $cat_i ? $cat_i['couleur'] : '#002395' ?>40;"></div></div>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:16px">
          <a href="/admin/edit/" class="btn btn-primary btn-sm btn-full">+ Nouvel article</a>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>

