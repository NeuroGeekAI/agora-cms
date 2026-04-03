<?php
/**
 * AgoraCMS — Admin : Créer / Éditer un article
 */
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
admin_require();
$id      = (int)($_GET['id'] ?? 0);
$article = $id ? Database::fetch("SELECT * FROM ag_articles WHERE id = ?", [$id]) : null;
$errors  = [];
$success = '';
// ── TRAITEMENT FORMULAIRE ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) die('CSRF invalide');
    $titre       = trim($_POST['titre'] ?? '');
    $extrait     = trim($_POST['extrait'] ?? '');
    $contenu     = $_POST['contenu'] ?? '';
    $categorie   = $_POST['categorie'] ?? 'souverainete';
    $tags        = trim($_POST['tags'] ?? '');
    $statut      = $_POST['statut'] ?? 'brouillon';
    $une         = !empty($_POST['une']) ? 1 : 0;
    $meta_title  = trim($_POST['meta_title'] ?? '');
    $meta_desc   = trim($_POST['meta_desc'] ?? '');
    $image_alt   = trim($_POST['image_alt'] ?? '');
    $auteur_id   = (int)($_SESSION['sv_admin']['id'] ?? 1);
    if (!$titre) $errors[] = 'Le titre est obligatoire.';
    if (!$contenu) $errors[] = 'Le contenu est obligatoire.';
    if (!in_array($categorie, array_keys(CATEGORIES))) $errors[] = 'Catégorie invalide.';
    if (!in_array($statut, ['publie', 'brouillon', 'archive'])) $statut = 'brouillon';
    // Upload image
    $image = $article['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $uploaded = sv_upload_image($_FILES['image'], 'articles');
        if ($uploaded) $image = $uploaded;
        else $errors[] = "Image invalide (jpg/png/webp, max 10 Mo).";
    }
    // Slug
    $slug_base = ag_slug($titre);
    if ($id) {
        $slug = $article['slug'];
        if (ag_slug($titre) !== ag_slug($article['titre'])) {
            $slug = $slug_base;
            $i = 1;
            while (Database::count("SELECT COUNT(*) FROM ag_articles WHERE slug=? AND id!=?", [$slug, $id])) {
                $slug = $slug_base . '-' . $i++;
            }
        }
    } else {
        $slug = $slug_base; $i = 1;
        while (Database::count("SELECT COUNT(*) FROM ag_articles WHERE slug=?", [$slug])) {
            $slug = $slug_base . '-' . $i++;
        }
    }
    // Auteur : récupérer l'auteur_id depuis l'admin user
    $admin_user = Database::fetch("SELECT auteur_id FROM ag_admin_users WHERE id=?", [$_SESSION['sv_admin']['id']]);
    $auteur_id = $admin_user['auteur_id'] ?? 1;
    // Score accessibilité
    $a11y = sv_accessibility_score($contenu, $titre, $image_alt ?: null);
    if (empty($errors)) {
        if ($id) {
            Database::query(
                "UPDATE ag_articles SET titre=?,slug=?,extrait=?,contenu=?,image=?,image_alt=?,categorie=?,tags=?,
                 auteur_id=?,statut=?,une=?,meta_title=?,meta_desc=?,access_score=? WHERE id=?",
                [$titre,$slug,$extrait,$contenu,$image,$image_alt,$categorie,$tags,
                 $auteur_id,$statut,$une,$meta_title,$meta_desc,$a11y['score'],$id]
            );
            $success = "✅ Article mis à jour avec succès.";
            $article = Database::fetch("SELECT * FROM ag_articles WHERE id=?", [$id]);
        } else {
            Database::query(
                "INSERT INTO ag_articles (titre,slug,extrait,contenu,image,image_alt,categorie,tags,
                 auteur_id,statut,une,meta_title,meta_desc,access_score) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$titre,$slug,$extrait,$contenu,$image,$image_alt,$categorie,$tags,
                 $auteur_id,$statut,$une,$meta_title,$meta_desc,$a11y['score']]
            );
            $id = (int)Database::lastInsertId();
            $success = "✅ Article créé avec succès !";
            $article = Database::fetch("SELECT * FROM ag_articles WHERE id=?", [$id]);
        }
    }
}
$a = $article ?: [];
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex">
<title><?= $id ? 'Éditer' : 'Nouvel article' ?> — <?= h(SITE_NAME) ?> Admin</title>
<style>
:root{--bleu:#002395;--rouge:#ED2939;--dark:#0d1117;--dark2:#161b22;--dark3:#1f2937;--text:#e5e7eb;--muted:#9ca3af;--border:#2d3748}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--dark);color:var(--text);font-family:system-ui,sans-serif;display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--dark2);border-right:1px solid var(--border);padding:0;flex-shrink:0;position:fixed;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
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
.editor-layout{display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start}
.card{background:var(--dark2);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:20px}
.card h2{font-size:0.9rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.field{margin-bottom:16px}
label{display:block;font-size:0.83rem;color:var(--muted);margin-bottom:6px;font-weight:600}
input[type=text],input[type=email],select,textarea{width:100%;background:var(--dark);border:1px solid var(--border);color:var(--text);padding:11px 14px;border-radius:10px;font-size:0.92rem;outline:none;transition:.2s;font-family:inherit}
input:focus,select:focus,textarea:focus{border-color:var(--bleu);box-shadow:0 0 0 3px rgba(0,35,149,.15)}
textarea{resize:vertical;min-height:120px}
.editor-area{min-height:500px;font-size:0.95rem;line-height:1.7}
.char-count{font-size:0.75rem;color:var(--muted);text-align:right;margin-top:4px}
.toggle-row{display:flex;align-items:center;gap:10px;padding:12px 0}
input[type=checkbox]{width:18px;height:18px;cursor:pointer}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:10px;font-size:0.9rem;font-weight:700;cursor:pointer;border:none;text-decoration:none;transition:.2s}
.btn-primary{background:var(--bleu);color:white}.btn-primary:hover{background:#0033cc}
.btn-secondary{background:var(--dark3);border:1px solid var(--border);color:var(--text)}
.msg-success{background:rgba(5,150,105,.1);border:1px solid rgba(5,150,105,.3);color:#34d399;padding:14px;border-radius:10px;margin-bottom:20px}
.msg-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:14px;border-radius:10px;margin-bottom:20px}
.img-preview{max-width:100%;border-radius:10px;margin-top:10px;display:none}
.a11y-tips{font-size:0.8rem;color:var(--muted);margin-top:8px}
.a11y-score-box{padding:14px;border-radius:10px;text-align:center;margin-bottom:14px}
.score-good{background:rgba(5,150,105,.1);border:1px solid rgba(5,150,105,.3);color:#34d399}
.score-medium{background:rgba(217,119,6,.1);border:1px solid rgba(217,119,6,.3);color:#fbbf24}
.score-low{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.toolbar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;padding:10px;background:var(--dark3);border-radius:10px 10px 0 0;border:1px solid var(--border);border-bottom:none}
.tbtn{background:none;border:1px solid var(--border);color:var(--muted);padding:5px 10px;border-radius:6px;font-size:0.8rem;cursor:pointer;transition:.2s}
.tbtn:hover{background:rgba(255,255,255,.08);color:white}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="tribar"><span></span><span></span><span></span></div>
  <div class="sidebar-top"><div class="sb-logo">🏛️ <?= h(SITE_NAME) ?></div></div>
  <nav class="sb-nav">
    <div class="sb-section">Contenu</div>
    <a href="/admin/" class="sb-link">📊 Dashboard</a>
    <a href="/admin/articles/" class="sb-link">📰 Articles</a>
    <a href="/admin/edit/" class="sb-link active">✏️ Nouvel article</a>
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
    <h1><?= $id ? '✏️ Éditer l\'article' : '✏️ Nouvel article' ?></h1>
    <div style="display:flex;gap:10px">
      <?php if ($id && ($a['statut'] ?? '') === 'publie'): ?>
      <a href="/article/<?= h($a['slug']) ?>/" class="btn btn-secondary" target="_blank">👁 Voir l'article</a>
      <?php endif; ?>
      <a href="/admin/articles/" class="btn btn-secondary">← Retour</a>
    </div>
  </div>
  <div class="content">
    <?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="msg-error"><?php foreach ($errors as $e) echo h($e) . '<br>'; ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <div class="editor-layout">
        <!-- Colonne gauche : contenu -->
        <div>
          <div class="card">
            <h2>📝 Contenu principal</h2>
            <div class="field">
              <label>Titre de l'article *</label>
              <input type="text" name="titre" value="<?= h($a['titre'] ?? '') ?>" required placeholder="Titre accrocheur..." id="titre-input" maxlength="255">
            </div>
            <div class="field">
              <label>Extrait / Chapeau <span style="font-weight:400">(affiché en preview)</span></label>
              <textarea name="extrait" rows="3" placeholder="Résumé en 1-2 phrases..."><?= h($a['extrait'] ?? '') ?></textarea>
            </div>
            <div class="field">
              <label>Contenu HTML *</label>
              <div class="toolbar">
                <button type="button" class="tbtn" onclick="wrap('h2')">H2</button>
                <button type="button" class="tbtn" onclick="wrap('h3')">H3</button>
                <button type="button" class="tbtn" onclick="wrap('strong')"><b>B</b></button>
                <button type="button" class="tbtn" onclick="wrap('em')"><i>I</i></button>
                <button type="button" class="tbtn" onclick="wrapBlock('ul','li')">Liste</button>
                <button type="button" class="tbtn" onclick="wrapBlock('ol','li')">Num.</button>
                <button type="button" class="tbtn" onclick="wrap('blockquote')">❝ Citation</button>
                <button type="button" class="tbtn" onclick="insertLink()">🔗 Lien</button>
              </div>
              <textarea name="contenu" id="contenu" class="editor-area" required placeholder="<p>Votre contenu en HTML...</p>"><?= h($a['contenu'] ?? '') ?></textarea>
            </div>
          </div>
          <!-- SEO -->
          <div class="card">
            <h2>🔍 SEO</h2>
            <div class="field">
              <label>Meta titre <span style="font-weight:400">(max 70 car.)</span></label>
              <input type="text" name="meta_title" value="<?= h($a['meta_title'] ?? '') ?>" maxlength="70" id="meta-title" placeholder="Titre SEO personnalisé (laisser vide = titre article)">
              <div class="char-count" id="mt-count">0 / 70</div>
            </div>
            <div class="field">
              <label>Meta description <span style="font-weight:400">(max 160 car.)</span></label>
              <textarea name="meta_desc" id="meta-desc" rows="3" maxlength="160" placeholder="Description SEO..."><?= h($a['meta_desc'] ?? '') ?></textarea>
              <div class="char-count" id="md-count">0 / 160</div>
            </div>
          </div>
        </div>
        <!-- Colonne droite : options -->
        <div>
          <!-- Score accessibilité -->
          <?php if ($id): $a11y = sv_accessibility_score($a['contenu'] ?? '', $a['titre'] ?? '', $a['image_alt'] ?? null); ?>
          <div class="card">
            <h2>♿ Score Accessibilité</h2>
            <div class="a11y-score-box <?= $a11y['score'] >= 80 ? 'score-good' : ($a11y['score'] >= 50 ? 'score-medium' : 'score-low') ?>">
              <div style="font-size:2rem;font-weight:900"><?= $a11y['score'] ?>%</div>
              <div><?= $a11y['score'] >= 80 ? '✅ Excellent' : ($a11y['score'] >= 50 ? '⚠️ À améliorer' : '❌ Insuffisant') ?></div>
            </div>
            <?php if (!empty($a11y['tips'])): ?>
            <div class="a11y-tips">
              <strong>Conseils :</strong>
              <?php foreach ($a11y['tips'] as $tip): ?><div>• <?= h($tip) ?></div><?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <!-- Publication -->
          <div class="card">
            <h2>🚀 Publication</h2>
            <div class="field">
              <label>Statut</label>
              <select name="statut">
                <option value="brouillon" <?= ($a['statut'] ?? '') === 'brouillon' ? 'selected' : '' ?>>📝 Brouillon</option>
                <option value="publie"    <?= ($a['statut'] ?? '') === 'publie'    ? 'selected' : '' ?>>✅ Publié</option>
                <option value="archive"   <?= ($a['statut'] ?? '') === 'archive'   ? 'selected' : '' ?>>📦 Archivé</option>
              </select>
            </div>
            <div class="toggle-row">
              <input type="checkbox" name="une" id="une" <?= !empty($a['une']) ? 'checked' : '' ?>>
              <label for="une" style="margin:0;cursor:pointer">⭐ Mettre à la une</label>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:14px">
              <button type="submit" name="statut" value="publie" class="btn btn-primary">✅ Publier</button>
              <button type="submit" class="btn btn-secondary">💾 Enregistrer</button>
            </div>
          </div>
          <!-- Catégorie & Tags -->
          <div class="card">
            <h2>📂 Catégorie & Tags</h2>
            <div class="field">
              <label>Catégorie *</label>
              <select name="categorie">
                <?php foreach (CATEGORIES as $slug => $c): ?>
                <option value="<?= $slug ?>" <?= ($a['categorie'] ?? '') === $slug ? 'selected' : '' ?>>
                  <?= $c['emoji'] ?> <?= h($c['nom']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>Tags <span style="font-weight:400">(séparés par virgule)</span></label>
              <input type="text" name="tags" value="<?= h($a['tags'] ?? '') ?>" placeholder="france, souveraineté, europe...">
            </div>
          </div>
          <!-- Image -->
          <div class="card">
            <h2>🖼️ Image principale</h2>
            <?php if (!empty($a['image'])): ?>
            <img src="<?= h(UPLOAD_URL . $a['image']) ?>" alt="" style="width:100%;border-radius:8px;margin-bottom:12px">
            <?php endif; ?>
            <div class="field">
              <label>Fichier image (jpg, png, webp — max 10 Mo)</label>
              <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" id="img-input">
              <img id="img-preview" class="img-preview">
            </div>
            <div class="field">
              <label>Description alt (accessibilité ♿)</label>
              <input type="text" name="image_alt" value="<?= h($a['image_alt'] ?? '') ?>" placeholder="Description de l'image pour les malvoyants">
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
// Compteurs SEO
function updateCount(inputId, countId, max) {
  const el = document.getElementById(inputId);
  const ct = document.getElementById(countId);
  if (!el || !ct) return;
  ct.textContent = el.value.length + ' / ' + max;
  ct.style.color = el.value.length > max * .9 ? '#fbbf24' : '#9ca3af';
  el.addEventListener('input', () => { ct.textContent = el.value.length + ' / ' + max; ct.style.color = el.value.length > max*.9?'#fbbf24':'#9ca3af'; });
}
updateCount('meta-title', 'mt-count', 70);
updateCount('meta-desc', 'md-count', 160);
// Prévisualisation image
document.getElementById('img-input')?.addEventListener('change', function() {
  const preview = document.getElementById('img-preview');
  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(this.files[0]);
  }
});
// Toolbar HTML basique
function getEditor() { return document.getElementById('contenu'); }
function wrap(tag) {
  const e = getEditor(); if (!e) return;
  const start = e.selectionStart, end = e.selectionEnd;
  const sel = e.value.substring(start, end) || 'Votre texte';
  const replacement = `<${tag}>${sel}</${tag}>`;
  e.value = e.value.substring(0, start) + replacement + e.value.substring(end);
  e.focus();
}
function wrapBlock(listTag, itemTag) {
  const e = getEditor(); if (!e) return;
  const start = e.selectionStart, end = e.selectionEnd;
  const items = (e.value.substring(start, end) || 'Élément 1\nÉlément 2').split('\n');
  const replacement = `<${listTag}>\n${items.map(i => `  <${itemTag}>${i.trim()}</${itemTag}>`).join('\n')}\n</${listTag}>`;
  e.value = e.value.substring(0, start) + replacement + e.value.substring(end);
}
function insertLink() {
  const url = prompt('URL du lien :');
  if (!url) return;
  const e = getEditor(); if (!e) return;
  const start = e.selectionStart, end = e.selectionEnd;
  const text = e.value.substring(start, end) || 'Texte du lien';
  e.value = e.value.substring(0, start) + `<a href="${url}">${text}</a>` + e.value.substring(end);
}
</script>
</body>
</html>

