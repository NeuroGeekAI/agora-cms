<?php
/**
 * AgoraCMS — Fonctions utilitaires globales
 */
defined('AGORA') or die('Accès direct interdit.');
// ── SÉCURITÉ ─────────────────────────────────────────────────────────────
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_verify(): bool {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
function ag_slug(string $str): string {
    $str = mb_strtolower($str, 'UTF-8');
    $map = ['à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u',
            'ç'=>'c','œ'=>'oe','æ'=>'ae','ñ'=>'n'];
    $str = strtr($str, $map);
    $str = preg_replace('/[^a-z0-9\s\-]/', '', $str);
    $str = preg_replace('/[\s\-]+/', '-', $str);
    return trim($str, '-');
}
function ag_excerpt(string $html, int $len = 160): string {
    $text = strip_tags($html);
    if (mb_strlen($text) <= $len) return $text;
    return mb_substr($text, 0, $len) . '…';
}
function sv_format_date(string $date, string $format = 'd/m/Y'): string {
    return date($format, strtotime($date));
}
function sv_time_ago(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60)     return 'à l\'instant';
    if ($diff < 3600)   return floor($diff / 60) . ' min';
    if ($diff < 86400)  return floor($diff / 3600) . 'h';
    if ($diff < 604800) return floor($diff / 86400) . ' jour' . (floor($diff/86400)>1?'s':'');
    return sv_format_date($date);
}
function sv_reading_time(string $content): int {
    $words = str_word_count(strip_tags($content));
    return max(1, (int)ceil($words / 200));
}
function sv_format_number(int $n): string {
    if ($n >= 1000000) return round($n/1000000, 1) . 'M';
    if ($n >= 1000)    return round($n/1000, 1) . 'k';
    return (string)$n;
}
// ── ARTICLES ─────────────────────────────────────────────────────────────
function get_articles(int $page = 1, string $cat = '', string $search = '', string $statut = 'publie'): array {
    $offset = ($page - 1) * ARTICLES_PER_PAGE;
    $where  = "a.statut = ?";
    $params = [$statut];
    if ($cat)    { $where .= " AND a.categorie = ?"; $params[] = $cat; }
    if ($search) { $where .= " AND (a.titre LIKE ? OR a.contenu LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    return Database::fetchAll(
        "SELECT a.*, u.nom AS auteur_nom, u.avatar AS auteur_avatar
         FROM ag_articles a
         LEFT JOIN ag_auteurs u ON u.id = a.auteur_id
         WHERE $where ORDER BY a.une DESC, a.created_at DESC
         LIMIT " . ARTICLES_PER_PAGE . " OFFSET $offset",
        $params
    );
}
function count_articles(string $cat = '', string $search = '', string $statut = 'publie'): int {
    $where  = "statut = ?";
    $params = [$statut];
    if ($cat)    { $where .= " AND categorie = ?"; $params[] = $cat; }
    if ($search) { $where .= " AND (titre LIKE ? OR contenu LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    return Database::count("SELECT COUNT(*) FROM ag_articles WHERE $where", $params);
}
function get_article_by_slug(string $slug): ?array {
    return Database::fetch(
        "SELECT a.*, u.nom AS auteur_nom, u.bio AS auteur_bio, u.avatar AS auteur_avatar
         FROM ag_articles a
         LEFT JOIN ag_auteurs u ON u.id = a.auteur_id
         WHERE a.slug = ? AND a.statut = 'publie'",
        [$slug]
    );
}
function get_featured_articles(int $limit = 4): array {
    return Database::fetchAll(
        "SELECT a.*, u.nom AS auteur_nom FROM ag_articles a
         LEFT JOIN ag_auteurs u ON u.id = a.auteur_id
         WHERE a.statut = 'publie' AND a.une = 1
         ORDER BY a.created_at DESC LIMIT ?",
        [$limit]
    );
}
function get_related_articles(string $cat, int $exclude_id, int $limit = 3): array {
    return Database::fetchAll(
        "SELECT * FROM ag_articles WHERE statut='publie' AND categorie=? AND id != ?
         ORDER BY created_at DESC LIMIT ?",
        [$cat, $exclude_id, $limit]
    );
}
function increment_article_views(int $id): void {
    if (!isset($_SESSION['sv_views'])) $_SESSION['sv_views'] = [];
    $now = time();
    $last = $_SESSION['sv_views'][$id] ?? 0;
    if ($now - $last >= 1800) {
        Database::query("UPDATE ag_articles SET vues = vues + 1 WHERE id = ?", [$id]);
        $_SESSION['sv_views'][$id] = $now;
    }
}
// ── CATÉGORIES ────────────────────────────────────────────────────────────
function get_category(string $slug): ?array {
    $cats = CATEGORIES;
    return isset($cats[$slug]) ? array_merge(['slug' => $slug], $cats[$slug]) : null;
}
function get_articles_count_by_cat(): array {
    $rows = Database::fetchAll(
        "SELECT categorie, COUNT(*) as n FROM ag_articles WHERE statut='publie' GROUP BY categorie"
    );
    $result = [];
    foreach ($rows as $r) $result[$r['categorie']] = (int)$r['n'];
    return $result;
}
// ── UPLOAD IMAGE ─────────────────────────────────────────────────────────
function sv_upload_image(array $file, string $subfolder = 'articles') {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMG)) return false;
    if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) return false;
    $mime_allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (!in_array($finfo->file($file['tmp_name']), $mime_allowed)) return false;
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dir  = UPLOAD_PATH . $subfolder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) return false;
    return $subfolder . '/' . $name;
}
// ── ADMIN AUTH ────────────────────────────────────────────────────────────
function admin_logged(): bool {
    return !empty($_SESSION['sv_admin']);
}
function admin_require(): void {
    if (!admin_logged()) {
        header('Location: /' . ADMIN_PREFIX . '/');
        exit;
    }
}
// ── SETTINGS (accessible depuis les pages publiques) ──────────────────────
if (!function_exists('sv_get')) {
    function sv_get(string $key, string $default = ''): string {
        $row = Database::fetch("SELECT valeur FROM ag_settings WHERE cle = ?", [$key]);
        return $row ? (string)$row['valeur'] : $default;
    }
}
if (!function_exists('sv_set')) {
    function sv_set(string $key, string $val): void {
        $exists = Database::fetch("SELECT cle FROM ag_settings WHERE cle = ?", [$key]);
        if ($exists) {
            Database::query("UPDATE ag_settings SET valeur=?, updated_at=? WHERE cle=?",
                [$val, date('Y-m-d H:i:s'), $key]);
        } else {
            Database::query("INSERT INTO ag_settings (cle, valeur, updated_at) VALUES (?,?,?)",
                [$key, $val, date('Y-m-d H:i:s')]);
        }
    }
}
// ── MAINTENANCE MODE ──────────────────────────────────────────────────────
function check_maintenance(): void {
    if (admin_logged()) return;
    if (sv_get('maintenance', '0') === '1') {
        require_once __DIR__ . '/../pages/maintenance.php';
        exit;
    }
}
// ── SEO ───────────────────────────────────────────────────────────────────
function sv_meta_title(string $title = ''): string {
    return h($title ? "$title — " . SITE_NAME : SITE_NAME . ' — ' . SITE_TAGLINE);
}
function ag_canonical(string $path = ''): string {
    return SITE_URL . '/' . ltrim($path, '/');
}
// ── PAGINATION ────────────────────────────────────────────────────────────
function sv_pagination(int $current, int $total_pages, string $base_url): string {
    if ($total_pages <= 1) return '';
    $html = '<nav class="pagination">';
    if ($current > 1) $html .= '<a href="' . h($base_url) . '?p=' . ($current-1) . '">← Précédent</a>';
    for ($i = max(1, $current-2); $i <= min($total_pages, $current+2); $i++) {
        $html .= $i === $current
            ? "<span class='current'>$i</span>"
            : '<a href="' . h($base_url) . '?p=' . $i . '">' . $i . '</a>';
    }
    if ($current < $total_pages) $html .= '<a href="' . h($base_url) . '?p=' . ($current+1) . '">Suivant →</a>';
    $html .= '</nav>';
    return $html;
}
// ── ACCESSIBILITÉ HANDICAP (score) ────────────────────────────────────────
function sv_accessibility_score(string $content, string $title, ?string $image_alt): array {
    $score = 100;
    $tips  = [];
    $words = str_word_count(strip_tags($content));
    if ($words < 300)  { $score -= 15; $tips[] = 'Contenu court (< 300 mots)'; }
    if (strlen($title) > 60) { $score -= 10; $tips[] = 'Titre trop long pour lecteurs d\'écran'; }
    if (!$image_alt)   { $score -= 20; $tips[] = 'Image sans description alt (indispensable pour malvoyants)'; }
    if (!preg_match('/<h[2-4]/', $content)) { $score -= 15; $tips[] = 'Pas de sous-titres (h2/h3) — difficile pour navigation'; }
    if (preg_match('/https?:\/\/[^\s<>]+/', strip_tags($content))) { $score -= 5; $tips[] = 'URL brute détectée — utiliser texte descriptif'; }
    return ['score' => max(0, $score), 'tips' => $tips];
}

