<?php
/**
 * AgoraCMS — Admin : Liste des articles
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
admin_require();
$message = '';
// Actions
if (!empty($_GET['action']) && csrf_verify()) {
    $aid = (int)($_GET['id'] ?? 0);
    if ($_GET['action'] === 'delete' && $aid) {
        $art = Database::fetch("SELECT image FROM ag_articles WHERE id=?", [$aid]);
        if ($art && $art['image']) { @unlink(UPLOAD_PATH . $art['image']); }
        Database::query("DELETE FROM ag_articles WHERE id=?", [$aid]);
        $message = "✅ Article supprimé.";
    } elseif ($_GET['action'] === 'toggle' && $aid) {
        $art = Database::fetch("SELECT statut FROM ag_articles WHERE id=?", [$aid]);
        if ($art) {
            $new = $art['statut'] === 'publie' ? 'brouillon' : 'publie';
            Database::query("UPDATE ag_articles SET statut=? WHERE id=?", [$new, $aid]);
            $message = "✅ Statut changé.";
        }
    }
}
$page    = max(1, (int)($_GET['p'] ?? 1));
$cat     = preg_replace('/[^a-z0-9\-]/', '', $_GET['cat'] ?? '');
$statut  = in_array($_GET['statut'] ?? '', ['publie','brouillon','archive','']) ? ($_GET['statut'] ?? '') : '';
$search  = htmlspecialchars(strip_tags($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8');
$per_page = 20;
$where  = "1=1";
$params = [];
if ($cat)    { $where .= " AND a.categorie=?"; $params[] = $cat; }
if ($statut) { $where .= " AND a.statut=?"; $params[] = $statut; }
if ($search) { $where .= " AND (a.titre LIKE ? OR a.tags LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$total      = Database::count("SELECT COUNT(*) FROM ag_articles a WHERE $where", $params);
$total_pages = max(1, (int)ceil($total / $per_page));
$offset     = ($page - 1) * $per_page;
$articles   = Database::fetchAll(
    "SELECT a.*, u.nom AS auteur_nom FROM ag_articles a
     LEFT JOIN ag_auteurs u ON u.id=a.auteur_id
     WHERE $where ORDER BY a.created_at DESC LIMIT $per_page OFFSET $offset",
    $params
);
$csrf = csrf_token();
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex"><title>Articles — <?= h(SITE_NAME) ?> Admin</title>
<style>
:root{--bleu:#002395;--rouge:#ED2939;--dark:#0d1117;--dark2:#161b22;--dark3:#1f2937;--text:#e5e7eb;--muted:#9ca3af;--border:#2d3748}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--dark);color:var(--text);font-family:system-ui,sans-serif;display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--dark2);border-right:1px solid var(--border);flex-shrink:0;position:fixed;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
.sidebar-top{padding:20px 16px;border-bottom:1px solid var(--border)}
.sb-logo{font-size:1.1rem;font-weight:900;color:white}
.sb-nav{padding:12px 8px;flex:1}
.sb-section{font-size:0.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;padding:12px 8px 6px;font-weight:700}
.sb-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:var(--muted);font-size:0.88rem;text-decoration:none;margin-bottom:2px;transition:.2s}
.sb-link:hover,.sb-link.active{background:rgba(255,255,255,.07);color:white}
.sb-bottom{padding:12px 8px;border-top:1px solid var(--border)}
.tribar{height:4px;display:flex}
.tribar span:nth-child(1){flex:1;background:var(--bleu)}.tribar span:nth-child(2){flex:1;background:white}.tribar span:nth-child(3){flex:1;background:var(--rouge)}
.main{flex:1;margin-left:240px}
.topbar{background:var(--dark2);border-bottom:1px solid var(--border);padding:16px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
.topbar h1{font-size:1.2rem;font-weight:800}
.content{padding:32px}
.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;align-items:center}
.filters input,.filters select{background:var(--dark2);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:10px;font-size:0.88rem;outline:none}
.filters input:focus,.filters select:focus{border-color:var(--bleu)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:.2s}
.btn-primary{background:var(--bleu);color:white}.btn-primary:hover{background:#0033cc}
.btn-sm{padding:5px 10px;font-size:0.78rem}
.btn-danger{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.btn-secondary{background:var(--dark3);border:1px solid var(--border);color:var(--text)}
.table-wrap{overflow-x:auto;border-radius:16px;border:1px solid var(--border)}
table{width:100%;border-collapse:collapse;background:var(--dark2)}
th{padding:12px 16px;text-align:left;color:var(--muted);font-size:0.78rem;text-transform:uppercase;background:rgba(255,255,255,.02);border-bottom:1px solid var(--border)}
td{padding:12px 16px;border-bottom:1px solid var(--border);font-size:0.88rem;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(255,255,255,.02)}
.badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:0.72rem;font-weight:700;cursor:pointer}
.badge-publie{background:rgba(5,150,105,.15);color:#34d399}
.badge-brouillon{background:rgba(217,119,6,.15);color:#fbbf24}
.badge-archive{background:rgba(107,114,128,.15);color:#9ca3af}
.thumb{width:60px;height:40px;object-fit:cover;border-radius:6px}
.actions-cell{display:flex;gap:6px}
.msg{background:rgba(5,150,105,.1);border:1px solid rgba(5,150,105,.3);color:#34d399;padding:12px;border-radius:10px;margin-bottom:20px}
.pagination{display:flex;gap:8px;margin-top:24px;flex-wrap:wrap}
.pagination a,.pagination span{padding:8px 14px;border-radius:8px;font-size:0.85rem;border:1px solid var(--border);color:var(--muted);text-decoration:none;transition:.2s}
.pagination a:hover{border-color:var(--bleu);color:#60a5fa}
.pagination .current{background:var(--bleu);color:white;border-color:var(--bleu)}
.empty{text-align:center;padding:60px;color:var(--muted)}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="tribar"><span></span><span></span><span></span></div>
  <div class="sidebar-top"><div class="sb-logo">🏛️ <?= h(SITE_NAME) ?></div></div>
  <nav class="sb-nav">
    <div class="sb-section">Contenu</div>
    <a href="/admin/" class="sb-link">📊 Dashboard</a>
    <a href="/admin/articles/" class="sb-link active">📰 Articles</a>
    <a href="/admin/edit/" class="sb-link">✏️ Nouvel article</a>
    <a href="/admin/medias/" class="sb-link">🖼️ Médias</a>
    <div class="sb-section">Communauté</div>
    <a href="/admin/petitions/" class="sb-link">✊ Pétitions</a>
    <a href="/admin/messages/" class="sb-link">📬 Messages</a>
    <div class="sb-section">Système</div>
    <a href="/admin/settings/" class="sb-link">⚙️ Paramètres</a>
    <a href="/" class="sb-link" target="_blank">🌐 Voir le site</a>
  </nav>
  <div class="sb-bottom"><a href="/admin/?logout=1" class="sb-link" style="color:#fca5a5">🚪 Déconnexion</a></div>
</aside>
<div class="main">
  <div class="topbar">
    <h1>📰 Articles <span style="color:var(--muted);font-size:0.9rem"><?= $total ?> au total</span></h1>
    <a href="/admin/edit/" class="btn btn-primary">✏️ Nouvel article</a>
  </div>
  <div class="content">
    <?php if ($message): ?><div class="msg"><?= $message ?></div><?php endif; ?>
    <form method="GET" action="/admin/articles/" class="filters">
      <input type="text" name="q" value="<?= h($search) ?>" placeholder="🔍 Rechercher...">
      <select name="cat">
        <option value="">Toutes catégories</option>
        <?php foreach (CATEGORIES as $slug => $c): ?>
        <option value="<?= $slug ?>" <?= $cat === $slug ? 'selected' : '' ?>><?= $c['emoji'] ?> <?= h($c['nom']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="statut">
        <option value="">Tous statuts</option>
        <option value="publie" <?= $statut === 'publie' ? 'selected' : '' ?>>✅ Publiés</option>
        <option value="brouillon" <?= $statut === 'brouillon' ? 'selected' : '' ?>>📝 Brouillons</option>
        <option value="archive" <?= $statut === 'archive' ? 'selected' : '' ?>>📦 Archivés</option>
      </select>
      <button type="submit" class="btn btn-primary">Filtrer</button>
      <a href="/admin/articles/" class="btn btn-secondary">↺ Reset</a>
    </form>
    <?php if (empty($articles)): ?>
    <div class="empty">
      <div style="font-size:3rem;margin-bottom:16px">📭</div>
      <p>Aucun article trouvé.</p>
      <a href="/admin/edit/" class="btn btn-primary" style="margin-top:16px">✏️ Créer le premier article</a>
    </div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Image</th><th>Titre / Slug</th><th>Catégorie</th>
            <th>Auteur</th><th>Vues</th><th>♿</th><th>Statut</th><th>Date</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($articles as $a):
            $cat_i = CATEGORIES[$a['categorie']] ?? null; ?>
          <tr>
            <td><?php if ($a['image']): ?>
              <img src="<?= h(UPLOAD_URL . $a['image']) ?>" alt="" class="thumb">
            <?php else: ?>
              <div style="width:60px;height:40px;background:<?= h($cat_i['couleur'] ?? '#002395') ?>20;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1.2rem"><?= $cat_i['emoji'] ?? '📰' ?></div>
            <?php endif; ?></td>
            <td>
              <strong><?= h(mb_substr($a['titre'], 0, 60)) ?></strong>
              <br><small style="color:var(--muted)">/article/<?= h($a['slug']) ?>/</small>
            </td>
            <td><?= $cat_i ? $cat_i['emoji'] . ' ' . h(explode(' ', $cat_i['nom'])[0]) : h($a['categorie']) ?></td>
            <td><?= h($a['auteur_nom'] ?? '—') ?></td>
            <td>👁 <?= number_format((int)$a['vues']) ?></td>
            <td>
              <span title="Score accessibilité" style="font-size:0.8rem;color:<?= (int)$a['access_score'] >= 80 ? '#34d399' : ((int)$a['access_score'] >= 50 ? '#fbbf24' : '#fca5a5') ?>">
                ♿ <?= $a['access_score'] ?>%
              </span>
            </td>
            <td>
              <a href="/admin/articles/?action=toggle&id=<?= $a['id'] ?>&csrf_token=<?= $csrf ?>" class="badge badge-<?= $a['statut'] ?>" title="Cliquer pour changer">
                <?= $a['statut'] === 'publie' ? '✅ Publié' : ($a['statut'] === 'brouillon' ? '📝 Brouillon' : '📦 Archivé') ?>
              </a>
              <?= $a['une'] ? '<span style="font-size:0.8rem;margin-left:4px">⭐</span>' : '' ?>
            </td>
            <td><small><?= sv_format_date($a['created_at']) ?></small></td>
            <td>
              <div class="actions-cell">
                <a href="/admin/edit/?id=<?= $a['id'] ?>" class="btn btn-sm" style="background:rgba(0,35,149,.2);border:1px solid rgba(0,35,149,.4);color:#93c5fd" title="Éditer">✏️</a>
                <?php if ($a['statut'] === 'publie'): ?>
                <a href="/article/<?= h($a['slug']) ?>/" class="btn btn-sm btn-secondary" target="_blank" title="Voir">👁</a>
                <?php endif; ?>
                <a href="/admin/articles/?action=delete&id=<?= $a['id'] ?>&csrf_token=<?= $csrf ?>"
                   class="btn btn-sm btn-danger" title="Supprimer"
                   onclick="return confirm('Supprimer « <?= addslashes(h(mb_substr($a['titre'],0,40))) ?> » ? Irréversible.')">🗑️</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?><a href="?p=<?= $page-1 ?>&cat=<?= urlencode($cat) ?>&statut=<?= urlencode($statut) ?>&q=<?= urlencode($search) ?>">← Précédent</a><?php endif; ?>
      <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
      <?php if ($i === $page): ?><span class="current"><?= $i ?></span>
      <?php else: ?><a href="?p=<?= $i ?>&cat=<?= urlencode($cat) ?>&statut=<?= urlencode($statut) ?>&q=<?= urlencode($search) ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $total_pages): ?><a href="?p=<?= $page+1 ?>&cat=<?= urlencode($cat) ?>&statut=<?= urlencode($statut) ?>&q=<?= urlencode($search) ?>">Suivant →</a><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>

