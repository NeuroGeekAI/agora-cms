<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * AgoraCMS — Installateur Wizard (style WordPress)
 * Accès : https://votre-domaine.fr/install/wizard.php
 * À SUPPRIMER après installation !
 * ═══════════════════════════════════════════════════════════════
 */
session_start();
define('WIZARD_VERSION', '1.0.0');
// ── Sécurité : si déjà installé, bloquer l'accès au wizard ───────────────
if (file_exists(dirname(__DIR__) . '/config/config.php') && !isset($_SESSION['wizard_done'])) {
    http_response_code(403);
    die('<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>AgoraCMS — Déjà installé</title>
    <style>body{background:#0d1117;color:#e5e7eb;font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
    .box{text-align:center;padding:40px;background:#161b22;border:1px solid #2d3748;border-radius:20px;max-width:480px}
    h1{color:#34d399;font-size:1.5rem;margin-bottom:12px}p{color:#9ca3af;margin-bottom:24px}
    a{background:#002395;color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700}</style></head>
    <body><div class="box"><h1>✅ AgoraCMS est déjà installé</h1>
    <p>Le wizard a déjà été exécuté. Pour des raisons de sécurité, supprimez le dossier <code>install/</code> via votre cPanel.</p>
    <a href="/">← Retour au site</a></div></body></html>');
}
$step = (int)($_GET['step'] ?? $_SESSION['wizard_step'] ?? 1);
$error = '';
$success = '';
// ── Fonctions utilitaires ─────────────────────────────────────────────────
function wiz_input(string $key, string $default = ''): string {
    return htmlspecialchars(trim($_POST[$key] ?? $_SESSION['wizard_data'][$key] ?? $default));
}
function wiz_test_db(string $host, string $name, string $user, string $pass): string|true {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}
function wiz_run_sql(PDO $pdo, string $sql): void {
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt) $pdo->exec($stmt);
    }
}
function wiz_generate_config(array $d): string {
    $key = bin2hex(random_bytes(32));
    $pass_hash = password_hash($d['admin_pass'], PASSWORD_BCRYPT, ['cost' => 12]);
    $_SESSION['wizard_admin_hash'] = $pass_hash;
    $_SESSION['wizard_secret_key'] = $key;
    $color1 = $d['color_primary'] ?? '#002395';
    $color2 = $d['color_accent']  ?? '#ED2939';
    $modules_php = '';
    $mods = ['handicap','lois','debats','petitions','proteger_docs','manifeste','rejoindre','souverainevoix'];
    foreach ($mods as $m) {
        $val = in_array($m, $d['modules'] ?? $mods) ? 'true' : 'false';
        $modules_php .= "    '$m' => $val,\n";
    }
    return "<?php\ndefined('AGORA') or die('Accès direct interdit.');\n"
        . "\$_demo_sqlite = dirname(__DIR__) . '/data/agora-demo.sqlite';\n"
        . "if (!defined('DB_TYPE') && PHP_SAPI === 'cli-server') {\n"
        . "    define('DB_TYPE','sqlite'); define('DB_SQLITE_PATH',\$_demo_sqlite);\n"
        . "    if (!defined('SITE_URL')) { \$port=\$_SERVER['SERVER_PORT']??8080; define('SITE_URL','http://localhost:'.\$port); }\n"
        . "}\n"
        . "define('SITE_NAME',        '" . addslashes($d['site_name']) . "');\n"
        . "define('SITE_TAGLINE',     '" . addslashes($d['site_tagline']) . "');\n"
        . "if (!defined('SITE_URL')) define('SITE_URL', '" . rtrim($d['site_url'],'/') . "');\n"
        . "define('SITE_EMAIL',       '" . addslashes($d['site_email']) . "');\n"
        . "define('SITE_LANG',        'fr');\n"
        . "define('SITE_VERSION',     '1.0.0');\n"
        . "define('SITE_COLOR_PRIMARY',  '" . $color1 . "');\n"
        . "define('SITE_COLOR_ACCENT',   '" . $color2 . "');\n"
        . "define('DB_HOST',    '" . addslashes($d['db_host']) . "');\n"
        . "define('DB_NAME',    '" . addslashes($d['db_name']) . "');\n"
        . "define('DB_USER',    '" . addslashes($d['db_user']) . "');\n"
        . "define('DB_PASS',    '" . addslashes($d['db_pass']) . "');\n"
        . "define('DB_CHARSET', 'utf8mb4');\n"
        . "define('DB_PREFIX',  'ag_');\n"
        . "define('ROOT_PATH',    __DIR__ . '/..');\n"
        . "define('UPLOAD_PATH',  ROOT_PATH . '/uploads/');\n"
        . "define('UPLOAD_URL',   SITE_URL . '/uploads/');\n"
        . "define('ASSET_URL',    SITE_URL . '/assets/');\n"
        . "define('SECRET_KEY',   '" . $key . "');\n"
        . "define('ADMIN_PREFIX', 'admin');\n"
        . "define('MAX_UPLOAD_MB', 10);\n"
        . "define('ALLOWED_IMG',  ['jpg','jpeg','png','webp','gif']);\n"
        . "define('THUMB_WIDTH',  800);\n"
        . "define('THUMB_HEIGHT', 450);\n"
        . "define('ARTICLES_PER_PAGE', 12);\n"
        . "define('CATEGORIES', [\n"
        . "    'programme'     => ['nom'=>'Programme & Vision',        'emoji'=>'🏛️','couleur'=>'" . $color1 . "'],\n"
        . "    'economie'      => ['nom'=>'Économie & Emploi',          'emoji'=>'📈','couleur'=>'#1a7a4a'],\n"
        . "    'societe'       => ['nom'=>'Société & Valeurs',          'emoji'=>'🤝','couleur'=>'#8b4513'],\n"
        . "    'handicap'      => ['nom'=>'Handicap & Inclusion',       'emoji'=>'♿','couleur'=>'#7c3aed'],\n"
        . "    'education'     => ['nom'=>'Éducation & Jeunesse',       'emoji'=>'📚','couleur'=>'#0891b2'],\n"
        . "    'defense'       => ['nom'=>'Défense & Sécurité',         'emoji'=>'🛡️','couleur'=>'#374151'],\n"
        . "    'numerique'     => ['nom'=>'Numérique & Innovation',     'emoji'=>'💻','couleur'=>'#059669'],\n"
        . "    'justice'       => ['nom'=>'Justice & Libertés',         'emoji'=>'⚖️','couleur'=>'#d97706'],\n"
        . "    'sante'         => ['nom'=>'Santé & Solidarité',         'emoji'=>'🏥','couleur'=>'#dc2626'],\n"
        . "    'international' => ['nom'=>'International & Diplomatie', 'emoji'=>'🌍','couleur'=>'#4f46e5'],\n"
        . "]);\n"
        . "define('MODULES', [\n" . $modules_php . "]);\n"
        . "\$_env = (defined('DB_TYPE') && DB_TYPE==='sqlite') ? 'development' : 'production';\n"
        . "define('ENVIRONMENT', \$_env);\n"
        . "if (ENVIRONMENT==='production') { error_reporting(0); ini_set('display_errors',0); }\n"
        . "else { error_reporting(E_ALL); ini_set('display_errors',1); }\n"
        . "date_default_timezone_set('Europe/Paris');\n";
}
// ── Traitement POST ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['wizard_data'] = array_merge($_SESSION['wizard_data'] ?? [], $_POST);
    if ($step === 2) {
        // Test connexion BDD
        $result = wiz_test_db($_POST['db_host']??'localhost', $_POST['db_name']??'', $_POST['db_user']??'', $_POST['db_pass']??'');
        if ($result !== true) { $error = "❌ Connexion BDD impossible : $result"; }
        else { $_SESSION['wizard_step'] = 3; header('Location: ?step=3'); exit; }
    } elseif ($step === 3) {
        if (empty($_POST['site_name'])) { $error = "Le nom du site est obligatoire."; }
        elseif (empty($_POST['site_url'])) { $error = "L'URL du site est obligatoire."; }
        else { $_SESSION['wizard_step'] = 4; header('Location: ?step=4'); exit; }
    } elseif ($step === 4) {
        $_SESSION['wizard_data']['modules'] = array_keys($_POST['modules'] ?? []);
        $_SESSION['wizard_step'] = 5; header('Location: ?step=5'); exit;
    } elseif ($step === 5) {
        if (empty($_POST['admin_user'])) { $error = "L'identifiant admin est obligatoire."; }
        elseif (strlen($_POST['admin_pass']??'') < 8) { $error = "Mot de passe trop court (8 caractères minimum)."; }
        elseif (($_POST['admin_pass']??'') !== ($_POST['admin_pass2']??'')) { $error = "Les mots de passe ne correspondent pas."; }
        else { $_SESSION['wizard_step'] = 6; header('Location: ?step=6'); exit; }
    } elseif ($step === 6) {
        // INSTALLATION FINALE
        $d = $_SESSION['wizard_data'] ?? [];
        try {
            // 1. Générer config.php
            $configContent = wiz_generate_config($d);
            $configPath = dirname(__DIR__) . '/config/config.php';
            file_put_contents($configPath, $configContent);
            // 2. Connexion BDD
            $pdo = new PDO(
                "mysql:host={$d['db_host']};dbname={$d['db_name']};charset=utf8mb4",
                $d['db_user'], $d['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            // 3. Importer install.sql
            $sql = file_get_contents(__DIR__ . '/install.sql');
            wiz_run_sql($pdo, $sql);
            // 4. Insérer admin
            $hash = $_SESSION['wizard_admin_hash'];
            $adminUser = htmlspecialchars_decode($d['admin_user']);
            $adminEmail = htmlspecialchars_decode($d['admin_email'] ?? $d['site_email']);
            $pdo->prepare("INSERT INTO ag_admin_users (username, email, password_hash, role) VALUES (?,?,?,'superadmin') ON DUPLICATE KEY UPDATE password_hash=?")
                ->execute([$adminUser, $adminEmail, $hash, $hash]);
            // 5. Insérer settings
            $settings = [
                ['site_name',    htmlspecialchars_decode($d['site_name'])],
                ['site_tagline', htmlspecialchars_decode($d['site_tagline'])],
                ['site_email',   htmlspecialchars_decode($d['site_email'])],
                ['maintenance',  '0'],
                ['color_primary', $d['color_primary'] ?? '#002395'],
                ['color_accent',  $d['color_accent']  ?? '#ED2939'],
            ];
            foreach ($settings as [$k, $v]) {
                $pdo->prepare("INSERT INTO ag_settings (cle, valeur) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur=?")
                    ->execute([$k, $v, $v]);
            }
            // 6. Créer dossier uploads
            $uploadsDir = dirname(__DIR__) . '/uploads';
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
            file_put_contents($uploadsDir . '/.htaccess', "Options -Indexes\nRequire all denied\n<FilesMatch \"\.(jpg|jpeg|png|gif|webp|svg)$\">\nRequire all granted\n</FilesMatch>");
            $_SESSION['wizard_done'] = true;
            $_SESSION['wizard_admin_user'] = $adminUser;
            $step = 7;
        } catch (Throwable $e) {
            $error = "❌ Erreur installation : " . htmlspecialchars($e->getMessage());
        }
    }
}
$stepTitles = ['','Bienvenue','Base de données','Identité','Modules','Administrateur','Vérification','Terminé !'];
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installation AgoraCMS — Étape <?= $step ?>/7</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0d1117;color:#e5e7eb;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wizard{background:#161b22;border:1px solid #2d3748;border-radius:20px;width:100%;max-width:680px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.wizard-header{background:linear-gradient(135deg,#002395,#0033cc);padding:28px 36px}
.wizard-logo{font-size:1.6rem;font-weight:900;color:white;margin-bottom:6px}
.wizard-subtitle{color:rgba(255,255,255,.75);font-size:0.9rem}
.steps-bar{display:flex;gap:0;background:#0d1117;padding:16px 24px;border-bottom:1px solid #2d3748;overflow-x:auto}
.step-dot{display:flex;align-items:center;gap:6px;font-size:0.78rem;color:#6b7280;white-space:nowrap;padding:0 8px}
.step-dot.active{color:#60a5fa;font-weight:700}
.step-dot.done{color:#34d399}
.step-num{width:22px;height:22px;border-radius:50%;border:2px solid #2d3748;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0}
.step-dot.active .step-num{border-color:#60a5fa;background:rgba(96,165,250,.15);color:#60a5fa}
.step-dot.done .step-num{border-color:#34d399;background:rgba(52,211,153,.15);color:#34d399}
.step-sep{color:#2d3748;margin:0 4px;font-size:0.8rem}
.wizard-body{padding:36px}
h2{font-size:1.3rem;font-weight:800;margin-bottom:8px;color:white}
.lead{color:#9ca3af;font-size:0.92rem;margin-bottom:28px;line-height:1.6}
.field{margin-bottom:18px}
label{display:block;font-size:0.83rem;color:#9ca3af;margin-bottom:7px;font-weight:600}
input[type=text],input[type=url],input[type=email],input[type=password]{width:100%;background:#0d1117;border:1px solid #2d3748;color:#e5e7eb;padding:12px 14px;border-radius:10px;font-size:0.92rem;outline:none;transition:.2s;font-family:inherit}
input:focus{border-color:#002395;box-shadow:0 0 0 3px rgba(0,35,149,.2)}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.field-hint{font-size:0.76rem;color:#6b7280;margin-top:5px}
.error-box{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:14px 16px;border-radius:10px;margin-bottom:20px;font-size:0.88rem}
.success-box{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);color:#34d399;padding:14px 16px;border-radius:10px;margin-bottom:20px;font-size:0.88rem}
.btn-next{width:100%;padding:14px;background:linear-gradient(135deg,#002395,#0033cc);color:white;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;transition:.2s;margin-top:8px}
.btn-next:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,35,149,.4)}
.test-btn{padding:10px 20px;background:#1f2937;border:1px solid #2d3748;color:#e5e7eb;border-radius:8px;font-size:0.88rem;cursor:pointer;transition:.2s;margin-bottom:16px}
.test-btn:hover{border-color:#60a5fa;color:#60a5fa}
.module-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}
.module-card{background:#0d1117;border:2px solid #2d3748;border-radius:12px;padding:14px;cursor:pointer;transition:.2s;position:relative}
.module-card:has(input:checked){border-color:#002395;background:rgba(0,35,149,.1)}
.module-card input{position:absolute;opacity:0;width:0;height:0}
.module-card .m-icon{font-size:1.4rem;display:block;margin-bottom:6px}
.module-card strong{display:block;font-size:0.88rem;color:white;margin-bottom:3px}
.module-card span{font-size:0.76rem;color:#6b7280}
.module-required{font-size:0.7rem;color:#34d399;font-weight:700}
.color-row{display:flex;gap:10px;align-items:center;margin-top:8px}
.cpick{width:44px;height:36px;border-radius:8px;border:2px solid #2d3748;cursor:pointer;background:none;padding:2px}
.color-preset{width:36px;height:36px;border-radius:8px;border:2px solid transparent;cursor:pointer;transition:.2s;flex-shrink:0}
.color-preset:hover{transform:scale(1.1)}
.done-hero{text-align:center;padding:20px 0}
.done-icon{font-size:4rem;display:block;margin-bottom:16px}
.done-creds{background:#0d1117;border:1px solid #2d3748;border-radius:12px;padding:20px;margin:20px 0;text-align:left}
.done-creds p{font-size:0.88rem;margin-bottom:8px;color:#9ca3af}
.done-creds strong{color:white}
.warn-box{background:rgba(217,119,6,.08);border:1px solid rgba(217,119,6,.3);color:#fbbf24;padding:14px;border-radius:10px;font-size:0.83rem;margin-top:16px}
@media(max-width:500px){.row2,.module-grid{grid-template-columns:1fr}.wizard-body{padding:24px}}
</style>
</head>
<body>
<div class="wizard">
  <div class="wizard-header">
    <div class="wizard-logo">🏛️ AgoraCMS</div>
    <div class="wizard-subtitle">Installation — Étape <?= $step ?>/7 — <?= $stepTitles[$step] ?? '' ?></div>
  </div>
  <!-- Barre de progression -->
  <div class="steps-bar">
    <?php for ($i = 1; $i <= 7; $i++):
      $cls = $i < $step ? 'done' : ($i === $step ? 'active' : '');
      $icon = $i < $step ? '✓' : $i;
    ?>
    <div class="step-dot <?= $cls ?>">
      <span class="step-num"><?= $icon ?></span>
      <?php $labels=['','Accueil','BDD','Identité','Modules','Admin','Vérif','✓']; echo $labels[$i]; ?>
    </div>
    <?php if ($i < 7): ?><span class="step-sep">›</span><?php endif; ?>
    <?php endfor; ?>
  </div>
  <div class="wizard-body">
    <?php if ($error): ?><div class="error-box">⚠️ <?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success-box"><?= $success ?></div><?php endif; ?>
    <!-- ═══ ÉTAPE 1 — BIENVENUE ═══ -->
    <?php if ($step === 1): ?>
    <h2>👋 Bienvenue dans AgoraCMS !</h2>
    <p class="lead">L'installation prend moins de <strong>2 minutes</strong>. Vous aurez besoin de vos identifiants MySQL (disponibles dans votre cPanel).</p>
    <div style="background:#0d1117;border:1px solid #2d3748;border-radius:12px;padding:20px;margin-bottom:24px">
      <p style="font-size:0.88rem;margin-bottom:10px;color:#9ca3af">✅ <strong style="color:white">Avant de commencer, vérifiez :</strong></p>
      <ul style="padding-left:20px;font-size:0.85rem;color:#9ca3af;line-height:2">
        <li>Une base de données MySQL créée dans votre cPanel</li>
        <li>Le login/mot de passe de cette base de données</li>
        <li>L'URL de votre site (ex: https://mon-site.fr)</li>
        <li>Un email pour le compte administrateur</li>
      </ul>
    </div>
    <form method="post" action="?step=1">
      <button type="submit" class="btn-next" onclick="this.form.action='?step=2'">🚀 Commencer l'installation →</button>
    </form>
    <!-- ═══ ÉTAPE 2 — BASE DE DONNÉES ═══ -->
    <?php elseif ($step === 2): ?>
    <h2>🗄️ Base de données</h2>
    <p class="lead">Entrez vos identifiants MySQL. Vous les trouvez dans votre cPanel → Bases de données MySQL.</p>
    <form method="post" action="?step=2">
      <div class="row2">
        <div class="field">
          <label>Hôte MySQL</label>
          <input type="text" name="db_host" value="<?= wiz_input('db_host','localhost') ?>" placeholder="localhost">
          <p class="field-hint">Généralement "localhost"</p>
        </div>
        <div class="field">
          <label>Nom de la base</label>
          <input type="text" name="db_name" value="<?= wiz_input('db_name') ?>" placeholder="user_mabase" required>
        </div>
      </div>
      <div class="row2">
        <div class="field">
          <label>Utilisateur MySQL</label>
          <input type="text" name="db_user" value="<?= wiz_input('db_user') ?>" placeholder="user_dbuser" required>
        </div>
        <div class="field">
          <label>Mot de passe MySQL</label>
          <input type="password" name="db_pass" value="<?= wiz_input('db_pass') ?>" placeholder="••••••••">
        </div>
      </div>
      <button type="submit" class="btn-next">Tester & Continuer →</button>
    </form>
    <!-- ═══ ÉTAPE 3 — IDENTITÉ DU PARTI ═══ -->
    <?php elseif ($step === 3): ?>
    <h2>🎨 Identité de votre mouvement</h2>
    <p class="lead">Personnalisez votre site. Tout pourra être modifié depuis l'admin après installation.</p>
    <form method="post" action="?step=3">
      <div class="field">
        <label>Nom du site / mouvement *</label>
        <input type="text" name="site_name" value="<?= wiz_input('site_name','Mon Parti') ?>" placeholder="Ex: AgoraCMS Demo, Mon Mouvement, PHOBOS Voice..." required>
      </div>
      <div class="field">
        <label>Slogan</label>
        <input type="text" name="site_tagline" value="<?= wiz_input('site_tagline','La voix de nos citoyens') ?>" placeholder="Votre slogan...">
      </div>
      <div class="field">
        <label>URL du site *</label>
        <input type="url" name="site_url" value="<?= wiz_input('site_url','https://') ?>" placeholder="https://mon-site.fr" required>
        <p class="field-hint">Sans slash final. Doit correspondre à l'URL réelle.</p>
      </div>
      <div class="field">
        <label>Email de contact *</label>
        <input type="email" name="site_email" value="<?= wiz_input('site_email') ?>" placeholder="contact@mon-site.fr" required>
      </div>
      <div class="field">
        <label>🎨 Couleurs du site</label>
        <div class="row2">
          <div>
            <p class="field-hint" style="margin-bottom:6px">Couleur principale</p>
            <div class="color-row">
              <input type="color" class="cpick" name="color_primary" value="<?= wiz_input('color_primary','#002395') ?>" id="cp1">
              <button type="button" class="color-preset" style="background:#002395" onclick="document.getElementById('cp1').value='#002395'" title="Bleu France"></button>
              <button type="button" class="color-preset" style="background:#ED2939" onclick="document.getElementById('cp1').value='#ED2939'" title="Rouge"></button>
              <button type="button" class="color-preset" style="background:#1a7a4a" onclick="document.getElementById('cp1').value='#1a7a4a'" title="Vert"></button>
              <button type="button" class="color-preset" style="background:#7c3aed" onclick="document.getElementById('cp1').value='#7c3aed'" title="Violet"></button>
              <button type="button" class="color-preset" style="background:#f59e0b" onclick="document.getElementById('cp1').value='#f59e0b'" title="Jaune"></button>
            </div>
          </div>
          <div>
            <p class="field-hint" style="margin-bottom:6px">Couleur accentuation</p>
            <div class="color-row">
              <input type="color" class="cpick" name="color_accent" value="<?= wiz_input('color_accent','#ED2939') ?>" id="cp2">
              <button type="button" class="color-preset" style="background:#ED2939" onclick="document.getElementById('cp2').value='#ED2939'" title="Rouge France"></button>
              <button type="button" class="color-preset" style="background:#002395" onclick="document.getElementById('cp2').value='#002395'" title="Bleu"></button>
              <button type="button" class="color-preset" style="background:#f59e0b" onclick="document.getElementById('cp2').value='#f59e0b'" title="Jaune"></button>
              <button type="button" class="color-preset" style="background:#34d399" onclick="document.getElementById('cp2').value='#34d399'" title="Vert clair"></button>
              <button type="button" class="color-preset" style="background:#f97316" onclick="document.getElementById('cp2').value='#f97316'" title="Orange"></button>
            </div>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-next">Continuer →</button>
    </form>
    <!-- ═══ ÉTAPE 4 — MODULES ═══ -->
    <?php elseif ($step === 4): ?>
    <h2>🧩 Choisissez vos modules</h2>
    <p class="lead">Activez les fonctionnalités dont vous avez besoin. Tout est modifiable en admin après installation.</p>
    <form method="post" action="?step=4">
      <div class="module-grid">
        <?php
        $allMods = [
            'handicap'      => ['icon'=>'♿','name'=>'Handicap & Inclusion','desc'=>'Section accessibilité RGAA','required'=>true],
            'lois'          => ['icon'=>'⚖️','name'=>'Lois françaises','desc'=>'Textes de loi expliqués','required'=>true],
            'manifeste'     => ['icon'=>'📜','name'=>'Programme politique','desc'=>'Manifeste & vision'],
            'debats'        => ['icon'=>'💬','name'=>'Débats en ligne','desc'=>'Forum de discussions'],
            'petitions'     => ['icon'=>'✍️','name'=>'Pétitions','desc'=>'Recueil de signatures'],
            'rejoindre'     => ['icon'=>'🤝','name'=>'Rejoindre','desc'=>'Formulaire d\'adhésion'],
            'proteger_docs' => ['icon'=>'🔒','name'=>'Protection docs','desc'=>'Outil filigrane gratuit'],
            'souverainevoix'=> ['icon'=>'🎙️','name'=>'Audio Accessibilité','desc'=>'Lecture vocale du site'],
        ];
        $activeMods = $_SESSION['wizard_data']['modules'] ?? array_keys($allMods);
        foreach ($allMods as $key => $mod):
            $checked = in_array($key, $activeMods) ? 'checked' : '';
            $req = $mod['required'] ?? false;
        ?>
        <label class="module-card">
          <input type="checkbox" name="modules[<?= $key ?>]" value="1" <?= $checked ?> <?= $req ? 'required' : '' ?>>
          <span class="m-icon"><?= $mod['icon'] ?></span>
          <strong><?= $mod['name'] ?> <?= $req ? '<span class="module-required">● REQUIS</span>' : '' ?></strong>
          <span><?= $mod['desc'] ?></span>
        </label>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="btn-next">Continuer →</button>
    </form>
    <!-- ═══ ÉTAPE 5 — COMPTE ADMIN ═══ -->
    <?php elseif ($step === 5): ?>
    <h2>👤 Compte administrateur</h2>
    <p class="lead">Créez votre accès à l'interface d'administration. Choisissez un mot de passe fort !</p>
    <form method="post" action="?step=5">
      <div class="row2">
        <div class="field">
          <label>Identifiant *</label>
          <input type="text" name="admin_user" value="<?= wiz_input('admin_user','admin') ?>" placeholder="admin" required minlength="3">
        </div>
        <div class="field">
          <label>Email admin *</label>
          <input type="email" name="admin_email" value="<?= wiz_input('admin_email') ?>" placeholder="admin@monparti.fr" required>
        </div>
      </div>
      <div class="row2">
        <div class="field">
          <label>Mot de passe * (min. 8 caractères)</label>
          <input type="password" name="admin_pass" placeholder="Mot de passe fort..." required minlength="8">
        </div>
        <div class="field">
          <label>Confirmer le mot de passe *</label>
          <input type="password" name="admin_pass2" placeholder="Répétez..." required>
        </div>
      </div>
      <div style="background:rgba(217,119,6,.08);border:1px solid rgba(217,119,6,.3);border-radius:10px;padding:14px;font-size:0.82rem;color:#fbbf24;margin-bottom:16px">
        💡 Notez votre identifiant et mot de passe maintenant. Ne les communiquez à personne.
      </div>
      <button type="submit" class="btn-next">Continuer →</button>
    </form>
    <!-- ═══ ÉTAPE 6 — VÉRIFICATION ═══ -->
    <?php elseif ($step === 6): ?>
    <?php $d = $_SESSION['wizard_data'] ?? []; ?>
    <h2>🔍 Récapitulatif avant installation</h2>
    <p class="lead">Vérifiez les informations avant de lancer l'installation.</p>
    <div style="background:#0d1117;border:1px solid #2d3748;border-radius:12px;padding:20px;margin-bottom:20px;font-size:0.88rem;line-height:2">
      <p>🌐 <strong>Site :</strong> <?= wiz_input('site_name') ?></p>
      <p>🔗 <strong>URL :</strong> <?= wiz_input('site_url') ?></p>
      <p>📧 <strong>Email :</strong> <?= wiz_input('site_email') ?></p>
      <p>🗄️ <strong>BDD :</strong> <?= wiz_input('db_name') ?> @ <?= wiz_input('db_host','localhost') ?></p>
      <p>👤 <strong>Admin :</strong> <?= wiz_input('admin_user') ?></p>
      <p>🧩 <strong>Modules :</strong> <?= implode(', ', $d['modules'] ?? []) ?></p>
    </div>
    <form method="post" action="?step=6">
      <button type="submit" class="btn-next" style="background:linear-gradient(135deg,#059669,#047857)">
        🚀 Lancer l'installation finale !
      </button>
    </form>
    <!-- ═══ ÉTAPE 7 — TERMINÉ ═══ -->
    <?php elseif ($step === 7): ?>
    <div class="done-hero">
      <span class="done-icon">🎉</span>
      <h2>Installation réussie !</h2>
      <p class="lead">AgoraCMS est installé et prêt à l'emploi.</p>
    </div>
    <div class="done-creds">
      <p>🔗 <strong>Votre site :</strong> <a href="<?= wiz_input('site_url') ?>" style="color:#60a5fa"><?= wiz_input('site_url') ?></a></p>
      <p>⚙️ <strong>Administration :</strong> <a href="<?= wiz_input('site_url') ?>/admin/" style="color:#60a5fa"><?= wiz_input('site_url') ?>/admin/</a></p>
      <p>👤 <strong>Identifiant :</strong> <?= wiz_input('admin_user') ?></p>
      <p>🔑 <strong>Mot de passe :</strong> celui que vous avez choisi à l'étape 5</p>
    </div>
    <div class="warn-box">
      ⚠️ <strong>SÉCURITÉ OBLIGATOIRE :</strong><br>
      Supprimez le dossier <code>install/</code> via votre gestionnaire de fichiers cPanel MAINTENANT.<br>
      Tant qu'il est présent, n'importe qui peut relancer l'installation !
    </div>
    <div style="text-align:center;margin-top:20px">
      <a href="<?= wiz_input('site_url') ?>/admin/" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#002395,#0033cc);color:white;border-radius:12px;font-weight:700;text-decoration:none">
        Accéder à l'administration →
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
<?php if ($step === 7) { session_destroy(); } ?>

