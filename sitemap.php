<?php
/**
 * AgoraCMS — Sitemap XML dynamique
 */
define('AGORA', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
header('Content-Type: application/xml; charset=utf-8');
$articles = Database::fetchAll(
    "SELECT slug, updated_at FROM ag_articles WHERE statut='publie' ORDER BY updated_at DESC"
);
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc><?= ag_canonical() ?></loc><changefreq>daily</changefreq><priority>1.0</priority></url>
  <url><loc><?= ag_canonical('handicap/') ?></loc><changefreq>weekly</changefreq><priority>0.9</priority></url>
  <url><loc><?= ag_canonical('programme/') ?></loc><changefreq>monthly</changefreq><priority>0.8</priority></url>
  <url><loc><?= ag_canonical('rejoindre/') ?></loc><changefreq>monthly</changefreq><priority>0.7</priority></url>
  <url><loc><?= ag_canonical('contact/') ?></loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
  <?php foreach (array_keys(CATEGORIES) as $slug): ?>
  <url><loc><?= ag_canonical('categorie/' . $slug . '/') ?></loc><changefreq>daily</changefreq><priority>0.8</priority></url>
  <?php endforeach; ?>
  <?php foreach ($articles as $a): ?>
  <url>
    <loc><?= ag_canonical('article/' . h($a['slug']) . '/') ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($a['updated_at'])) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
  <?php endforeach; ?>
</urlset>

