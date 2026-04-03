<?php
/**
 * AgoraCMS — Paramètres Admin
 * Général · SEO · Apparence · Sécurité
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
admin_require();
$tab     = $_GET['tab'] ?? 'general';
$message = '';
$msg_ok  = true;
// ── TRAITEMENT POST ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) die('CSRF invalide');
    $action = $_POST['action'] ?? '';
    // ── GENERAL ──
    if ($action === 'general') {
        $name = trim($_POST['site_name'] ?? '');
        if ($name) {
            sv_set('site_name',    $name);
            sv_set('site_tagline', trim($_POST['site_tagline'] ?? ''));
            sv_set('maintenance',  isset($_POST['maintenance']) ? '1' : '0');
            sv_set('articles_per_page', max(4, min(48, (int)($_POST['articles_per_page'] ?? 12))));
            // Favicon upload
            if (!empty($_FILES['favicon']['name'])) {
                $ffile = $_FILES['favicon'];
                $ext   = strtolower(pathinfo($ffile['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['ico','png','svg','jpg','jpeg']) && $ffile['size'] < 512000) {
                    $dest = ROOT_PATH . '/assets/img/favicon.' . $ext;
                    if (move_uploaded_file($ffile['tmp_name'], $dest)) {
                        sv_set('favicon_ext', $ext);
                        $message = '✅ Favicon uploadé + paramètres sauvegardés !';
                    } else {
                        $message = '⚠️ Paramètres sauvegardés mais erreur upload favicon.';
                    }
                } else {
                    $message = '❌ Favicon invalide (max 500ko, formats: ico/png/svg/jpg).';
                    $msg_ok  = false;
                }
            } else {
                $message = '✅ Paramètres généraux sauvegardés !';
            }
        } else {
            $message = '❌ Le nom du site est obligatoire.';
            $msg_ok  = false;
        }
    }
    // ── SEO ──
    if ($action === 'seo') {
        sv_set('seo_title_suffix',  trim($_POST['seo_title_suffix']  ?? ''));
        sv_set('seo_meta_desc',     trim($_POST['seo_meta_desc']     ?? ''));
        sv_set('seo_h1_home',       trim($_POST['seo_h1_home']       ?? ''));
        sv_set('seo_keywords',      trim($_POST['seo_keywords']      ?? ''));
        sv_set('seo_robots',        trim($_POST['seo_robots']        ?? 'index,follow'));
        sv_set('seo_ga_id',         trim($_POST['seo_ga_id']         ?? ''));
        sv_set('seo_gtm_id',        trim($_POST['seo_gtm_id']        ?? ''));
        sv_set('seo_canonical',     trim($_POST['seo_canonical']     ?? ''));
        $message = '✅ Paramètres SEO sauvegardés !';
    }
    // ── SITEMAP ──
    if ($action === 'sitemap') {
        $articles = Database::fetchAll(
            "SELECT slug, updated_at, created_at FROM ag_articles WHERE statut='publie' ORDER BY created_at DESC"
        );
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $pages_statiques = ['', 'manifeste', 'lois', 'debats', 'handicap', 'rejoindre', 'contact'];
        foreach ($pages_statiques as $pg) {
            $url = rtrim(SITE_URL, '/') . ($pg ? '/' . $pg : '');
            $xml .= "  <url><loc>" . htmlspecialchars($url) . "</loc><changefreq>weekly</changefreq><priority>" . ($pg === '' ? '1.0' : '0.8') . "</priority></url>\n";
        }
        foreach ($articles as $a) {
            $url  = rtrim(SITE_URL, '/') . '/article/' . $a['slug'];
            $date = substr($a['updated_at'] ?? $a['created_at'], 0, 10);
            $xml .= "  <url><loc>" . htmlspecialchars($url) . "</loc><lastmod>" . $date . "</lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>\n";
        }
        $xml .= '</urlset>';
        file_put_contents(ROOT_PATH . '/sitemap.xml', $xml);
        sv_set('sitemap_generated', date('Y-m-d H:i:s'));
        $message = '✅ Sitemap XML généré (' . count($articles) . ' articles + ' . count($pages_statiques) . ' pages) !';
    }
    // ── APPARENCE ──
    if ($action === 'apparence') {
        sv_set('theme_bleu',    preg_replace('/[^#a-fA-F0-9]/', '', $_POST['theme_bleu']    ?? '#002395'));
        sv_set('theme_rouge',   preg_replace('/[^#a-fA-F0-9]/', '', $_POST['theme_rouge']   ?? '#ED2939'));
        sv_set('theme_accent',  preg_replace('/[^#a-fA-F0-9]/', '', $_POST['theme_accent']  ?? '#7c3aed'));
        sv_set('theme_dark',    preg_replace('/[^#a-fA-F0-9]/', '', $_POST['theme_dark']    ?? '#0d1117'));
        sv_set('theme_dark2',   preg_replace('/[^#a-fA-F0-9]/', '', $_POST['theme_dark2']   ?? '#161b22'));
        sv_set('theme_custom_css', trim($_POST['theme_custom_css'] ?? ''));
        $message = '✅ Thème sauvegardé ! Rechargez le site pour voir les changements.';
    }
    // ── MOT DE PASSE ──
    if ($action === 'password') {
        $cur  = $_POST['current_pass'] ?? '';
        $new1 = $_POST['new_pass']     ?? '';
        $new2 = $_POST['new_pass2']    ?? '';
        $uid  = $_SESSION['sv_admin']['id'];
        $user = Database::fetch("SELECT password_hash FROM ag_admin_users WHERE id=?", [$uid]);
        if (!$user || !password_verify($cur, $user['password_hash'])) {
            $message = '❌ Mot de passe actuel incorrect.'; $msg_ok = false;
        } elseif (strlen($new1) < 10) {
            $message = '❌ Minimum 10 caractères.'; $msg_ok = false;
        } elseif ($new1 !== $new2) {
            $message = '❌ Les mots de passe ne correspondent pas.'; $msg_ok = false;
        } else {
            $hash = password_hash($new1, PASSWORD_BCRYPT, ['cost' => 12]);
            Database::query("UPDATE ag_admin_users SET password_hash=? WHERE id=?", [$hash, $uid]);
            $message = '✅ Mot de passe changé avec succès !';
        }
    }
}
// ── VALEURS ACTUELLES ─────────────────────────────────────────────────────
$v = [
    'site_name'         => sv_get('site_name',         SITE_NAME),
    'site_tagline'      => sv_get('site_tagline',       SITE_TAGLINE),
    'maintenance'       => sv_get('maintenance',        '0'),
    'articles_per_page' => sv_get('articles_per_page',  '12'),
    'favicon_ext'       => sv_get('favicon_ext',        'svg'),
    'seo_title_suffix'  => sv_get('seo_title_suffix',   ' — ' . SITE_NAME),
    'seo_meta_desc'     => sv_get('seo_meta_desc',      SITE_TAGLINE),
    'seo_h1_home'       => sv_get('seo_h1_home',        'Actualités citoyens'),
    'seo_keywords'      => sv_get('seo_keywords',       'souveraineté, France, patriotes'),
    'seo_robots'        => sv_get('seo_robots',         'index,follow'),
    'seo_ga_id'         => sv_get('seo_ga_id',          ''),
    'seo_gtm_id'        => sv_get('seo_gtm_id',         ''),
    'seo_canonical'     => sv_get('seo_canonical',      SITE_URL),
    'sitemap_generated' => sv_get('sitemap_generated',  ''),
    'theme_bleu'        => sv_get('theme_bleu',         '#002395'),
    'theme_rouge'       => sv_get('theme_rouge',        '#ED2939'),
    'theme_accent'      => sv_get('theme_accent',       '#7c3aed'),
    'theme_dark'        => sv_get('theme_dark',         '#0d1117'),
    'theme_dark2'       => sv_get('theme_dark2',        '#161b22'),
    'theme_custom_css'  => sv_get('theme_custom_css',   ''),
];
// ── SÉCURITÉ : checks en temps réel ──────────────────────────────────────
$is_https    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (($_SERVER['SERVER_PORT'] ?? 80) == 443);
$is_demo     = (PHP_SAPI === 'cli-server');
$php_version = PHP_VERSION;
$php_ok      = version_compare($php_version, '8.0', '>=');
$php_74      = version_compare($php_version, '7.4', '>=');
$extensions  = ['pdo_mysql','pdo_sqlite','mbstring','json','openssl','gd','curl'];
$ext_status  = [];
foreach ($extensions as $e) $ext_status[$e] = extension_loaded($e);
$upload_max  = ini_get('upload_max_filesize');
$mem_limit   = ini_get('memory_limit');
$display_err = ini_get('display_errors');
$expose_php  = ini_get('expose_php');
$security_score = 100;
if (!$is_https)       $security_score -= 30;
if (!$php_ok)         $security_score -= 10;
if ($display_err)     $security_score -= 15;
if ($expose_php)      $security_score -= 10;
if (!$ext_status['openssl']) $security_score -= 10;
if (!$ext_status['curl'])    $security_score -= 5;
$score_color = $security_score >= 80 ? '#34d399' : ($security_score >= 50 ? '#fbbf24' : '#fca5a5');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex">
<title>Paramètres — <?= h(SITE_NAME) ?> Admin</title>
<style>
:root{--bleu:<?= h($v['theme_bleu']) ?>;--rouge:<?= h($v['theme_rouge']) ?>;--accent:<?= h($v['theme_accent']) ?>;--dark:<?= h($v['theme_dark']) ?>;--dark2:<?= h($v['theme_dark2']) ?>;--dark3:#1f2937;--text:#e5e7eb;--muted:#9ca3af;--border:#2d3748}
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
.sb-bottom{padding:12px 8px;border-top:1px solid var(--border)}
.tribar{height:4px;display:flex}
.tribar span:nth-child(1){flex:1;background:var(--bleu)}.tribar span:nth-child(2){flex:1;background:white}.tribar span:nth-child(3){flex:1;background:var(--rouge)}
.main{flex:1;margin-left:240px;display:flex;flex-direction:column}
.topbar{background:var(--dark2);border-bottom:1px solid var(--border);padding:16px 32px;display:flex;align-items:center;justify-content:space-between}
.topbar h1{font-size:1.3rem;font-weight:800}
.content{padding:32px;flex:1}
/* TABS */
.tabs{display:flex;gap:4px;margin-bottom:28px;background:var(--dark2);padding:6px;border-radius:14px;border:1px solid var(--border)}
.tab-btn{flex:1;padding:10px 16px;border:none;border-radius:10px;font-size:0.88rem;font-weight:600;cursor:pointer;transition:.2s;background:transparent;color:var(--muted)}
.tab-btn.active{background:var(--bleu);color:white;box-shadow:0 4px 12px rgba(0,35,149,.4)}
.tab-btn:hover:not(.active){background:rgba(255,255,255,.06);color:var(--text)}
.tab-panel{display:none}.tab-panel.active{display:block}
/* CARDS */
.card{background:var(--dark2);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:24px}
.card-title{font-size:1rem;font-weight:700;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{margin-bottom:16px}
.form-group.full{grid-column:1/-1}
label{display:block;font-size:0.82rem;color:var(--muted);margin-bottom:6px;font-weight:600}
input[type=text],input[type=url],input[type=password],input[type=email],select,textarea{width:100%;background:var(--dark);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-size:0.9rem;outline:none;transition:border-color .2s;font-family:inherit}
input:focus,select:focus,textarea:focus{border-color:var(--bleu)}
textarea{resize:vertical;min-height:80px;line-height:1.6}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;font-size:0.9rem;font-weight:600;cursor:pointer;border:none;transition:all .2s}
.btn-primary{background:var(--bleu);color:white}.btn-primary:hover{background:#0033cc}
.btn-success{background:#059669;color:white}.btn-success:hover{background:#047857}
.btn-danger{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.toggle{position:relative;display:inline-block;width:46px;height:26px}
.toggle input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;inset:0;background:var(--dark3);border-radius:999px;transition:.3s;cursor:pointer}
.toggle-slider:before{content:'';position:absolute;width:20px;height:20px;left:3px;top:3px;background:var(--muted);border-radius:50%;transition:.3s}
.toggle input:checked + .toggle-slider{background:var(--bleu)}
.toggle input:checked + .toggle-slider:before{transform:translateX(20px);background:white}
.toggle-wrap{display:flex;align-items:center;gap:12px;padding:6px 0}
/* MESSAGE */
.msg{padding:14px 18px;border-radius:10px;margin-bottom:24px;font-size:0.9rem;font-weight:500}
.msg.ok{background:rgba(5,150,105,.12);border:1px solid rgba(5,150,105,.3);color:#34d399}
.msg.err{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
/* SEO */
.seo-preview{background:white;border-radius:10px;padding:16px 20px;margin-top:12px}
.seo-title{color:#1a0dab;font-size:1rem;font-weight:400;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.seo-url{color:#006621;font-size:0.8rem;margin-bottom:4px}
.seo-desc{color:#545454;font-size:0.85rem;line-height:1.5}
.seo-label{font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px}
/* APPARENCE */
.color-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
.color-item label{font-size:0.8rem;color:var(--muted);display:block;margin-bottom:6px}
.color-item input[type=color]{width:100%;height:48px;border-radius:8px;border:2px solid var(--border);cursor:pointer;padding:2px}
.theme-preview{border:2px solid var(--border);border-radius:12px;overflow:hidden;margin-top:20px}
.prev-bar{height:4px;display:flex}
.prev-nav{padding:10px 16px;display:flex;align-items:center;gap:12px;font-size:0.8rem}
.prev-logo{font-weight:800;font-size:0.95rem}
.prev-link{padding:4px 8px;border-radius:4px;font-size:0.78rem}
.prev-content{padding:20px;font-size:0.85rem}
.prev-btn{display:inline-block;padding:8px 16px;border-radius:6px;font-size:0.8rem;font-weight:600;color:white;margin-top:10px}
/* SECURITE */
.sec-score{text-align:center;padding:28px;border-radius:16px;background:var(--dark3);margin-bottom:24px}
.score-num{font-size:4rem;font-weight:900;line-height:1}
.score-bar-wrap{background:var(--dark);border-radius:999px;height:10px;margin:12px auto;max-width:300px;overflow:hidden}
.score-bar{height:100%;border-radius:999px;transition:width 1s}
.checks-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
.check-item{background:var(--dark3);border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;font-size:0.88rem}
.check-icon{font-size:1.3rem;flex-shrink:0}
.check-label{font-weight:600;color:var(--text)}
.check-detail{font-size:0.78rem;color:var(--muted);margin-top:2px}
.reco-list{list-style:none}
.reco-list li{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;gap:14px;align-items:flex-start;font-size:0.88rem}
.reco-list li:last-child{border-bottom:none}
.reco-priority{font-size:0.72rem;font-weight:700;padding:3px 8px;border-radius:999px;white-space:nowrap}
.prio-high{background:rgba(239,68,68,.15);color:#fca5a5}
.prio-med{background:rgba(217,119,6,.15);color:#fbbf24}
.prio-low{background:rgba(5,150,105,.15);color:#34d399}
.htaccess-box{background:var(--dark);border:1px solid var(--border);border-radius:8px;padding:16px;font-family:monospace;font-size:0.8rem;color:#a5f3fc;line-height:1.8;overflow-x:auto;margin-top:10px}
/* FAVICON PREVIEW */
.favicon-preview{display:flex;align-items:center;gap:16px;padding:14px;background:var(--dark3);border-radius:10px;margin-top:8px}
.favicon-preview img{width:32px;height:32px;object-fit:contain}
/* UPLOAD */
.upload-zone{border:2px dashed var(--border);border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:.2s}
.upload-zone:hover{border-color:var(--bleu);background:rgba(0,35,149,.05)}
@media(max-width:900px){.form-grid{grid-template-columns:1fr}.checks-grid{grid-template-columns:1fr}.color-grid{grid-template-columns:repeat(3,1fr)}.main{margin-left:0}.sidebar{display:none}}
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
    <a href="/admin/" class="sb-link">📊 Dashboard</a>
    <a href="/admin/articles/" class="sb-link">📰 Articles</a>
    <a href="/admin/edit/" class="sb-link">✏️ Nouvel article</a>
    <div class="sb-section">Système</div>
    <a href="/admin/settings/" class="sb-link active">⚙️ Paramètres</a>
    <a href="/" class="sb-link" target="_blank">🌐 Voir le site</a>
  </nav>
  <div class="sb-bottom">
    <a href="/admin/?logout=1" class="sb-link" style="color:#fca5a5">🚪 Déconnexion</a>
  </div>
</aside>
<div class="main">
  <div class="topbar">
    <h1>⚙️ Paramètres du site</h1>
    <a href="/admin/" style="color:var(--muted);font-size:0.9rem;text-decoration:none">← Dashboard</a>
  </div>
  <div class="content">
    <?php if ($message): ?>
    <div class="msg <?= $msg_ok ? 'ok' : 'err' ?>"><?= $message ?></div>
    <?php endif; ?>
    <div class="tabs" role="tablist">
      <button class="tab-btn <?= $tab==='general'    ? 'active' : '' ?>" onclick="switchTab('general')">⚙️ Général</button>
      <button class="tab-btn <?= $tab==='seo'        ? 'active' : '' ?>" onclick="switchTab('seo')">🔍 SEO</button>
      <button class="tab-btn <?= $tab==='apparence'  ? 'active' : '' ?>" onclick="switchTab('apparence')">🎨 Apparence</button>
      <button class="tab-btn <?= $tab==='securite'   ? 'active' : '' ?>" onclick="switchTab('securite')">🛡️ Sécurité</button>
    </div>
    <!-- ══ TAB GÉNÉRAL ══════════════════════════════════════════════════ -->
    <div id="tab-general" class="tab-panel <?= $tab==='general' ? 'active' : '' ?>">
      <div class="card">
        <div class="card-title">⚙️ Informations générales</div>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action"     value="general">
          <div class="form-grid">
            <div class="form-group">
              <label>Nom du site *</label>
              <input type="text" name="site_name" value="<?= h($v['site_name']) ?>" required>
            </div>
            <div class="form-group">
              <label>Slogan / Tagline</label>
              <input type="text" name="site_tagline" value="<?= h($v['site_tagline']) ?>">
            </div>
            <div class="form-group">
              <label>Articles par page</label>
              <select name="articles_per_page">
                <?php foreach ([4,6,8,10,12,16,20,24] as $n): ?>
                <option value="<?= $n ?>" <?= $v['articles_per_page'] == $n ? 'selected' : '' ?>><?= $n ?> articles</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Mode maintenance</label>
              <div class="toggle-wrap">
                <label class="toggle">
                  <input type="checkbox" name="maintenance" <?= $v['maintenance']==='1' ? 'checked' : '' ?>>
                  <span class="toggle-slider"></span>
                </label>
                <span style="color:var(--muted);font-size:0.88rem">
                  <?= $v['maintenance']==='1' ? '⚠️ Site inaccessible au public' : 'Site accessible' ?>
                </span>
              </div>
            </div>
            <div class="form-group full">
              <label>Favicon (ico / png / svg — max 500 Ko)</label>
              <div class="upload-zone" onclick="document.getElementById('favicon_input').click()">
                <div style="font-size:2rem;margin-bottom:8px">🖼️</div>
                <div style="font-size:0.9rem;color:var(--muted)">Cliquez pour choisir un fichier</div>
                <div style="font-size:0.78rem;color:var(--muted);margin-top:4px">Formats : .ico .png .svg .jpg — Taille recommandée : 32×32 ou 64×64 px</div>
                <input type="file" id="favicon_input" name="favicon" accept=".ico,.png,.svg,.jpg,.jpeg" style="display:none" onchange="previewFavicon(this)">
              </div>
              <div class="favicon-preview" id="favicon-preview">
                <img src="/assets/img/favicon.<?= h($v['favicon_ext']) ?>?v=<?= time() ?>" alt="Favicon actuel" onerror="this.style.display='none'">
                <div>
                  <div style="font-weight:600;font-size:0.9rem">Favicon actuel</div>
                  <div style="font-size:0.78rem;color:var(--muted)">favicon.<?= h($v['favicon_ext']) ?></div>
                </div>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">💾 Sauvegarder</button>
        </form>
      </div>
      <!-- Mot de passe -->
      <div class="card">
        <div class="card-title">🔒 Changer le mot de passe admin</div>
        <form method="POST" autocomplete="off">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action"     value="password">
          <div class="form-grid">
            <div class="form-group">
              <label>Mot de passe actuel</label>
              <input type="password" name="current_pass" autocomplete="current-password" required>
            </div>
            <div class="form-group" style="grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <div class="form-group">
                <label>Nouveau mot de passe (min. 10 car.)</label>
                <input type="password" name="new_pass" minlength="10" autocomplete="new-password" required>
              </div>
              <div class="form-group">
                <label>Confirmer</label>
                <input type="password" name="new_pass2" autocomplete="new-password" required>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-danger">🔑 Changer le mot de passe</button>
        </form>
      </div>
    </div>
    <!-- ══ TAB SEO ══════════════════════════════════════════════════════ -->
    <div id="tab-seo" class="tab-panel <?= $tab==='seo' ? 'active' : '' ?>">
      <div class="card">
        <div class="card-title">🔍 Balises & méta</div>
        <form method="POST" id="seo-form">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action"     value="seo">
          <div class="form-grid">
            <div class="form-group">
              <label>Suffixe titre (ex : " — AgoraCMS")</label>
              <input type="text" name="seo_title_suffix" id="s_title_suffix"
                     value="<?= h($v['seo_title_suffix']) ?>" oninput="updateSeoPreview()">
            </div>
            <div class="form-group">
              <label>URL canonique du site</label>
              <input type="url" name="seo_canonical" value="<?= h($v['seo_canonical']) ?>">
            </div>
            <div class="form-group full">
              <label>Balise H1 page d'accueil</label>
              <input type="text" name="seo_h1_home" id="s_h1"
                     value="<?= h($v['seo_h1_home']) ?>" oninput="updateSeoPreview()">
              <small style="color:var(--muted);font-size:0.78rem">⚡ Utilisé comme titre principal visible sur la page d'accueil</small>
            </div>
            <div class="form-group full">
              <label>Méta description par défaut</label>
              <textarea name="seo_meta_desc" id="s_meta_desc" rows="2"
                        oninput="updateSeoPreview()"><?= h($v['seo_meta_desc']) ?></textarea>
              <small id="desc-count" style="color:var(--muted);font-size:0.78rem"></small>
            </div>
            <div class="form-group full">
              <label>Mots-clés (séparés par des virgules)</label>
              <input type="text" name="seo_keywords" value="<?= h($v['seo_keywords']) ?>"
                     placeholder="souveraineté, France, patriotes, liberté">
            </div>
            <div class="form-group">
              <label>Meta robots</label>
              <select name="seo_robots">
                <option value="index,follow"     <?= $v['seo_robots']==='index,follow'     ? 'selected':'' ?>>index, follow ✅ (recommandé)</option>
                <option value="noindex,nofollow" <?= $v['seo_robots']==='noindex,nofollow' ? 'selected':'' ?>>noindex, nofollow (maintenance)</option>
                <option value="index,nofollow"   <?= $v['seo_robots']==='index,nofollow'   ? 'selected':'' ?>>index, nofollow</option>
              </select>
            </div>
            <div class="form-group">
              <label>Google Analytics ID (G-XXXXXXXX)</label>
              <input type="text" name="seo_ga_id" value="<?= h($v['seo_ga_id']) ?>" placeholder="G-XXXXXXXXXX">
            </div>
            <div class="form-group">
              <label>Google Tag Manager ID (GTM-XXXXX)</label>
              <input type="text" name="seo_gtm_id" value="<?= h($v['seo_gtm_id']) ?>" placeholder="GTM-XXXXXXX">
            </div>
          </div>
          <!-- Aperçu Google -->
          <div style="margin-bottom:20px">
            <div class="seo-label">Aperçu résultat Google</div>
            <div class="seo-preview">
              <div class="seo-title" id="prev-title"><?= h($v['seo_h1_home']) . $v['seo_title_suffix'] ?></div>
              <div class="seo-url"><?= h(rtrim($v['seo_canonical'], '/')) ?>/</div>
              <div class="seo-desc" id="prev-desc"><?= h($v['seo_meta_desc']) ?></div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">💾 Sauvegarder SEO</button>
        </form>
      </div>
      <!-- Sitemap -->
      <div class="card">
        <div class="card-title">🗺️ Sitemap XML</div>
        <div style="margin-bottom:20px;color:var(--muted);font-size:0.9rem;line-height:1.7">
          Génère automatiquement un fichier <code style="background:var(--dark3);padding:2px 6px;border-radius:4px;color:#a5f3fc">sitemap.xml</code>
          avec toutes les pages statiques et les articles publiés.<br>
          <?php if ($v['sitemap_generated']): ?>
          <span style="color:#34d399">✅ Dernier sitemap généré le <?= h($v['sitemap_generated']) ?></span>
          <?php else: ?>
          <span style="color:#fbbf24">⚠️ Aucun sitemap généré pour le moment</span>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
          <form method="POST" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action"     value="sitemap">
            <button type="submit" class="btn btn-success">🗺️ Générer sitemap.xml maintenant</button>
          </form>
          <?php if (file_exists(ROOT_PATH . '/sitemap.xml')): ?>
          <a href="/sitemap.xml" target="_blank" class="btn" style="background:var(--dark3);color:var(--text);border:1px solid var(--border)">👁️ Voir sitemap.xml</a>
          <?php endif; ?>
        </div>
        <div style="margin-top:16px;padding:12px;background:var(--dark3);border-radius:8px;font-size:0.82rem;color:var(--muted)">
          💡 Soumettez votre sitemap à <strong style="color:var(--text)">Google Search Console</strong> :
          <code style="color:#a5f3fc">https://search.google.com/search-console</code><br>
          Et à <strong style="color:var(--text)">Bing Webmaster Tools</strong> :
          <code style="color:#a5f3fc">https://www.bing.com/webmasters</code>
        </div>
      </div>
    </div>
    <!-- ══ TAB APPARENCE ════════════════════════════════════════════════ -->
    <div id="tab-apparence" class="tab-panel <?= $tab==='apparence' ? 'active' : '' ?>">
      <div class="card">
        <div class="card-title">🎨 Couleurs du thème</div>
        <form method="POST" id="theme-form">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action"     value="apparence">
          <div class="color-grid">
            <div class="color-item">
              <label>Bleu tricolore</label>
              <input type="color" name="theme_bleu"   id="t_bleu"   value="<?= h($v['theme_bleu']) ?>"   oninput="liveTheme()">
            </div>
            <div class="color-item">
              <label>Rouge tricolore</label>
              <input type="color" name="theme_rouge"  id="t_rouge"  value="<?= h($v['theme_rouge']) ?>"  oninput="liveTheme()">
            </div>
            <div class="color-item">
              <label>Accent / Handicap</label>
              <input type="color" name="theme_accent" id="t_accent" value="<?= h($v['theme_accent']) ?>" oninput="liveTheme()">
            </div>
            <div class="color-item">
              <label>Fond principal</label>
              <input type="color" name="theme_dark"   id="t_dark"   value="<?= h($v['theme_dark']) ?>"   oninput="liveTheme()">
            </div>
            <div class="color-item">
              <label>Fond secondaire</label>
              <input type="color" name="theme_dark2"  id="t_dark2"  value="<?= h($v['theme_dark2']) ?>"  oninput="liveTheme()">
            </div>
          </div>
          <!-- Préréglages rapides -->
          <div style="margin:20px 0 14px">
            <div class="seo-label" style="margin-bottom:10px">Préréglages rapides</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
              <button type="button" class="btn" style="background:#002395;color:white;font-size:0.82rem"
                onclick="applyPreset('#002395','#ED2939','#7c3aed','#0d1117','#161b22')">🇫🇷 Tricolore</button>
              <button type="button" class="btn" style="background:#1a3a1a;color:white;font-size:0.82rem"
                onclick="applyPreset('#1a6b1a','#2d8a2d','#059669','#0a0f0a','#111811')">🌿 Vert nature</button>
              <button type="button" class="btn" style="background:#1a1a3a;color:white;font-size:0.82rem"
                onclick="applyPreset('#3b28cc','#7c3aed','#a78bfa','#0a0a1a','#12122a')">💜 Violet souverain</button>
              <button type="button" class="btn" style="background:#2a1a0a;color:white;font-size:0.82rem"
                onclick="applyPreset('#c45c00','#e07b00','#f59e0b','#0f0a05','#1a120a')">🔥 Or républicain</button>
            </div>
          </div>
          <!-- Aperçu live -->
          <div class="seo-label" style="margin-bottom:8px">Aperçu en temps réel</div>
          <div class="theme-preview" id="theme-preview">
            <div class="prev-bar" id="prev-tribar">
              <span style="flex:1" id="pb1"></span>
              <span style="flex:1;background:white"></span>
              <span style="flex:1" id="pb2"></span>
            </div>
            <div class="prev-nav" id="prev-nav">
              <span class="prev-logo" id="prev-logo-name">🏛️ AgoraCMS</span>
              <span class="prev-link" id="prev-lnk1">Accueil</span>
              <span class="prev-link" id="prev-lnk2">Manifeste</span>
              <span class="prev-link" id="prev-lnk3">Lois</span>
            </div>
            <div class="prev-content" id="prev-content">
              <div style="font-size:1.1rem;font-weight:800;margin-bottom:8px;color:white">La voix souveraine de la France libre</div>
              <div style="color:#9ca3af;font-size:0.85rem;margin-bottom:12px">Actualités — Débats — Pétitions citoyennes</div>
              <span class="prev-btn" id="prev-btn-blue">Lire l'article →</span>
              <span class="prev-btn" id="prev-btn-acc" style="margin-left:8px">♿ Accessibilité</span>
            </div>
          </div>
          <!-- CSS personnalisé -->
          <div class="form-group" style="margin-top:20px">
            <label>CSS personnalisé (avancé) <span style="color:var(--muted);font-weight:400">— injecté après le thème</span></label>
            <textarea name="theme_custom_css" rows="5" style="font-family:monospace;font-size:0.82rem"><?= h($v['theme_custom_css']) ?></textarea>
          </div>
          <div style="display:flex;gap:12px;align-items:center">
            <button type="submit" class="btn btn-primary">💾 Sauvegarder le thème</button>
            <button type="button" class="btn" style="background:var(--dark3);border:1px solid var(--border)"
              onclick="applyPreset('#002395','#ED2939','#7c3aed','#0d1117','#161b22')">↩️ Thème par défaut</button>
          </div>
        </form>
      </div>
    </div>
    <!-- ══ TAB SÉCURITÉ ══════════════════════════════════════════════════ -->
    <div id="tab-securite" class="tab-panel <?= $tab==='securite' ? 'active' : '' ?>">
      <!-- Score -->
      <div class="card">
        <div class="sec-score">
          <div class="score-num" style="color:<?= $score_color ?>"><?= $security_score ?>/100</div>
          <div class="score-bar-wrap">
            <div class="score-bar" style="width:<?= $security_score ?>%;background:<?= $score_color ?>"></div>
          </div>
          <div style="color:var(--muted);font-size:0.9rem">
            Score de sécurité <?= $is_demo ? '(mode démo local)' : '' ?>
          </div>
        </div>
        <div class="checks-grid">
          <?php
          $checks = [
            ['icon' => $is_https ? '✅' : '❌', 'label' => 'HTTPS / SSL', 'detail' => $is_https ? 'Connexion chiffrée TLS' : 'CRITIQUE — Activez SSL sur O2switch'],
            ['icon' => $php_ok ? '✅' : '⚠️', 'label' => 'PHP ' . $php_version, 'detail' => $php_ok ? 'PHP 8+ recommandé' : 'PHP 8.2+ recommandé pour la prod'],
            ['icon' => $display_err ? '❌' : '✅', 'label' => 'Erreurs cachées', 'detail' => $display_err ? 'display_errors=On — risque en prod !' : 'display_errors=Off — OK'],
            ['icon' => $expose_php ? '⚠️' : '✅', 'label' => 'Version PHP cachée', 'detail' => $expose_php ? 'expose_php=On — à désactiver' : 'Version PHP masquée — OK'],
            ['icon' => $ext_status['openssl'] ? '✅' : '❌', 'label' => 'Extension OpenSSL', 'detail' => $ext_status['openssl'] ? 'Disponible' : 'Manquante — requis pour HTTPS'],
            ['icon' => $ext_status['pdo_mysql'] ? '✅' : ($is_demo ? '⚠️' : '❌'), 'label' => 'PDO MySQL', 'detail' => $ext_status['pdo_mysql'] ? 'Disponible' : ($is_demo ? 'Normal en mode démo SQLite' : 'Requis en production')],
            ['icon' => $ext_status['gd'] ? '✅' : '⚠️', 'label' => 'Extension GD (images)', 'detail' => $ext_status['gd'] ? 'Disponible' : 'Conseillée pour redimensionner les images'],
            ['icon' => $ext_status['curl'] ? '✅' : '⚠️', 'label' => 'Extension cURL', 'detail' => $ext_status['curl'] ? 'Disponible' : 'Conseillée pour les APIs externes'],
          ];
          foreach ($checks as $c): ?>
          <div class="check-item">
            <span class="check-icon"><?= $c['icon'] ?></span>
            <div>
              <div class="check-label"><?= h($c['label']) ?></div>
              <div class="check-detail"><?= h($c['detail']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:24px;padding:14px;background:var(--dark3);border-radius:10px;font-size:0.85rem">
          <div>⚡ PHP Memory : <strong><?= h($mem_limit) ?></strong></div>
          <div>📤 Upload max : <strong><?= h($upload_max) ?></strong></div>
          <div>🖥️ Serveur : <strong><?= $is_demo ? 'PHP built-in (démo)' : h($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?></strong></div>
        </div>
      </div>
      <!-- Recommandations O2switch 2026 -->
      <div class="card">
        <div class="card-title">🔒 Recommandations O2switch 2026</div>
        <ul class="reco-list">
          <li>
            <span class="reco-priority prio-high">CRITIQUE</span>
            <div>
              <strong>Activer SSL Let's Encrypt</strong><br>
              <span style="color:var(--muted)">Dans cPanel O2switch → SSL/TLS → Let's Encrypt → Activer pour votre domaine. TLS 1.3 est supporté.</span>
            </div>
          </li>
          <li>
            <span class="reco-priority prio-high">CRITIQUE</span>
            <div>
              <strong>Configurer HSTS (HTTP Strict Transport Security)</strong><br>
              <span style="color:var(--muted)">Force le HTTPS même si l'utilisateur tape http://. Ajouter dans .htaccess :</span>
              <div class="htaccess-box">Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"</div>
            </div>
          </li>
          <li>
            <span class="reco-priority prio-high">CRITIQUE</span>
            <div>
              <strong>En-têtes de sécurité .htaccess</strong><br>
              <div class="htaccess-box"># Clickjacking protection<br>Header always set X-Frame-Options "SAMEORIGIN"<br># XSS protection<br>Header always set X-XSS-Protection "1; mode=block"<br># MIME sniffing<br>Header always set X-Content-Type-Options "nosniff"<br># Referrer<br>Header always set Referrer-Policy "strict-origin-when-cross-origin"<br># Permissions<br>Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"</div>
            </div>
          </li>
          <li>
            <span class="reco-priority prio-med">IMPORTANT</span>
            <div>
              <strong>Content Security Policy (CSP)</strong><br>
              <span style="color:var(--muted)">Protège contre les injections XSS. Ajuster selon vos CDN utilisés :</span>
              <div class="htaccess-box">Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://www.googletagmanager.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'"</div>
            </div>
          </li>
          <li>
            <span class="reco-priority prio-med">IMPORTANT</span>
            <div>
              <strong>Masquer la version PHP</strong><br>
              <span style="color:var(--muted)">Dans php.ini O2switch (via cPanel → Select PHP version → Options) :</span>
              <div class="htaccess-box">expose_php = Off<br>display_errors = Off<br>log_errors = On<br>error_log = /home/user/logs/php_errors.log</div>
            </div>
          </li>
          <li>
            <span class="reco-priority prio-med">IMPORTANT</span>
            <div>
              <strong>Protéger les fichiers sensibles</strong><br>
              <div class="htaccess-box"># Bloquer accès .env, config, logs<br>&lt;FilesMatch "\.(env|log|sql|sh|bat|ps1)$"&gt;<br>    Require all denied<br>&lt;/FilesMatch&gt;<br># Bloquer navigation dossiers<br>Options -Indexes</div>
            </div>
          </li>
          <li>
            <span class="reco-priority prio-med">IMPORTANT</span>
            <div>
              <strong>ModSecurity O2switch</strong><br>
              <span style="color:var(--muted)">O2switch a ModSecurity activable via cPanel → Security → ModSecurity. Active le WAF (Web Application Firewall) qui bloque automatiquement les attaques SQL injection, XSS, etc.</span>
            </div>
          </li>
          <li>
            <span class="reco-priority prio-low">CONSEILLÉ</span>
            <div>
              <strong>Fail2Ban & protection brute-force</strong><br>
              <span style="color:var(--muted)">O2switch bloque automatiquement après trop de tentatives. Implémentez un rate-limit sur /admin/ avec un délai entre les tentatives de connexion.</span>
            </div>
          </li>
          <li>
            <span class="reco-priority prio-low">CONSEILLÉ</span>
            <div>
              <strong>Sauvegardes automatiques O2switch</strong><br>
              <span style="color:var(--muted)">cPanel → Backup Wizard → Schedule backup. O2switch conserve les snapshots JetBackup — vérifiez que les sauvegardes quotidiennes sont actives.</span>
            </div>
          </li>
          <li>
            <span class="reco-priority prio-low">CONSEILLÉ</span>
            <div>
              <strong>Certificat ECDSA vs RSA (2026)</strong><br>
              <span style="color:var(--muted)">En 2026, préférez ECDSA P-256 pour votre certificat SSL — plus rapide et aussi sécurisé que RSA 2048. Disponible via Let's Encrypt sur O2switch.</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<script>
// ── TABS ──────────────────────────────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    event.target.classList.add('active');
    history.replaceState(null, '', '?tab=' + name);
}
// ── SEO PREVIEW ───────────────────────────────────────────────────────────
function updateSeoPreview() {
    var h1  = document.getElementById('s_h1');
    var suf = document.getElementById('s_title_suffix');
    var dsc = document.getElementById('s_meta_desc');
    if (h1)  document.getElementById('prev-title').textContent = (h1.value || 'Titre') + (suf ? suf.value : '');
    if (dsc) {
        document.getElementById('prev-desc').textContent = dsc.value;
        var n = dsc.value.length;
        var el = document.getElementById('desc-count');
        el.textContent = n + '/160 caractères';
        el.style.color = n > 160 ? '#fca5a5' : (n > 120 ? '#fbbf24' : '#34d399');
    }
}
updateSeoPreview();
// ── FAVICON PREVIEW ───────────────────────────────────────────────────────
function previewFavicon(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var prev = document.querySelector('#favicon-preview img');
            if (prev) { prev.src = e.target.result; prev.style.display = ''; }
            var lbl = document.querySelector('#favicon-preview div div:last-child');
            if (lbl) lbl.textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
// ── LIVE THEME ────────────────────────────────────────────────────────────
function liveTheme() {
    var bleu   = document.getElementById('t_bleu').value;
    var rouge  = document.getElementById('t_rouge').value;
    var accent = document.getElementById('t_accent').value;
    var dark   = document.getElementById('t_dark').value;
    var dark2  = document.getElementById('t_dark2').value;
    var prev   = document.getElementById('theme-preview');
    if (!prev) return;
    prev.style.background = dark2;
    document.getElementById('pb1').style.background  = bleu;
    document.getElementById('pb2').style.background  = rouge;
    document.getElementById('prev-nav').style.background   = dark2;
    document.getElementById('prev-nav').style.borderBottom = '1px solid rgba(255,255,255,.1)';
    document.getElementById('prev-logo-name').style.color  = 'white';
    document.getElementById('prev-lnk1').style.background  = bleu;
    document.getElementById('prev-lnk1').style.color       = 'white';
    document.getElementById('prev-lnk2').style.color       = '#9ca3af';
    document.getElementById('prev-lnk3').style.color       = '#9ca3af';
    document.getElementById('prev-content').style.background = dark;
    document.getElementById('prev-btn-blue').style.background = bleu;
    document.getElementById('prev-btn-acc').style.background  = accent;
    document.getElementById('prev-btn-acc').style.color       = 'white';
}
liveTheme();
function applyPreset(bleu, rouge, accent, dark, dark2) {
    document.getElementById('t_bleu').value   = bleu;
    document.getElementById('t_rouge').value  = rouge;
    document.getElementById('t_accent').value = accent;
    document.getElementById('t_dark').value   = dark;
    document.getElementById('t_dark2').value  = dark2;
    liveTheme();
}
</script>
</body>
</html>

