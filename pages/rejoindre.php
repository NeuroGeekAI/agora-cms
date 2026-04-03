<?php
define('AGORA', true);
session_name('ag_session');
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
check_maintenance();
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) die('CSRF invalide');
    $nom     = trim($_POST['nom'] ?? '');
    $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $message = "Adhésion : " . trim($_POST['motivation'] ?? '');
    if (!$nom || !$email) $error = "Nom et email sont obligatoires.";
    else {
        $ip_hash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . SECRET_KEY);
        Database::query(
            "INSERT INTO ag_messages (nom, email, sujet, message, type, ip_hash) VALUES (?,?,?,?,?,?)",
            [$nom, $email, 'Demande d\'adhésion', $message, 'adhesion', $ip_hash]
        );
        $success = "✅ Bienvenue dans le mouvement ! Nous vous contacterons bientôt.";
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Rejoindre le mouvement — <?= h(SITE_NAME) ?></title>
<link rel="canonical" href="<?= ag_canonical('rejoindre/') ?>">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.join-hero{background:linear-gradient(135deg,#002395 0%,#1a3a8f 50%,#ED2939 100%);padding:80px 0;text-align:center}
.join-hero h1{font-size:3rem;font-weight:900;color:white;margin-bottom:16px}
.join-hero p{color:rgba(255,255,255,.85);font-size:1.1rem;max-width:600px;margin:0 auto}
.join-content{max-width:900px;margin:0 auto;padding:60px 20px}
.values-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:48px}
.value-card{background:var(--dark2);border:1px solid var(--border);border-radius:16px;padding:24px;text-align:center}
.value-icon{font-size:2.5rem;margin-bottom:12px}
.value-card h3{font-size:1rem;font-weight:700;margin-bottom:8px;color:white}
.value-card p{color:var(--muted);font-size:0.88rem}
.join-form-card{background:var(--dark2);border:1px solid var(--border);border-radius:20px;padding:40px;max-width:600px;margin:0 auto}
.field{margin-bottom:16px}
label{display:block;font-size:0.83rem;color:var(--muted);margin-bottom:6px;font-weight:600}
input,textarea{width:100%;background:var(--dark);border:1px solid var(--border);color:var(--text);padding:12px 14px;border-radius:10px;font-size:0.9rem;outline:none;font-family:inherit;transition:.2s}
input:focus,textarea:focus{border-color:var(--bleu);box-shadow:0 0 0 3px rgba(0,35,149,.15)}
textarea{resize:vertical;min-height:100px}
.msg-s{background:rgba(5,150,105,.1);border:1px solid rgba(5,150,105,.3);color:#34d399;padding:14px;border-radius:10px;margin-bottom:16px;text-align:center}
@media(max-width:768px){.values-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<header class="site-header">
  <div class="header-top"><div class="tricolor-bar"><span></span><span></span><span></span></div></div>
  <div class="container header-inner">
    <a href="/" class="site-logo"><span class="logo-icon">🏛️</span><div><div class="logo-name"><?= h(SITE_NAME) ?></div></div></a>
    <nav class="main-nav">
      <a href="/" class="nav-link">Accueil</a>
      <a href="/handicap/" class="nav-link nav-handicap">♿ Handicap</a>
      <a href="/manifeste/" class="nav-link">Programme</a>
      <a href="/rejoindre/" class="nav-link active">Rejoindre</a>
    </nav>
    <div class="header-actions"><button class="hamburger" id="hamburger">☰</button></div>
  </div>
</header>
<section class="join-hero">
  <div class="container">
    <h1>🤝 Rejoindre le mouvement</h1>
    <p>La France a besoin de vous. Chaque citoyen engagé est une victoire contre l'indifférence et la censure.</p>
  </div>
</section>
<main id="main">
  <div class="join-content">
    <div class="values-grid">
      <div class="value-card"><div class="value-icon">🇫🇷</div><h3>Souveraineté</h3><p>Une France libre, indépendante, décidant de son destin sans diktat extérieur.</p></div>
      <div class="value-card"><div class="value-icon">♿</div><h3>Inclusion totale</h3><p>12 millions de Français handicapés au cœur de notre programme — par conviction, pas par obligation.</p></div>
      <div class="value-card"><div class="value-icon">💻</div><h3>Souveraineté numérique</h3><p>Nos propres systèmes, nos propres données, notre propre OS. L'indépendance technologique d'abord.</p></div>
    </div>
    <?php if ($success): ?>
    <div class="msg-s" style="font-size:1.1rem;padding:24px"><?= $success ?></div>
    <?php else: ?>
    <div class="join-form-card">
      <h2 style="font-size:1.3rem;font-weight:900;color:white;text-align:center;margin-bottom:28px">✍️ Rejoindre maintenant</h2>
      <?php if ($error): ?><div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:12px;border-radius:8px;margin-bottom:16px">⚠️ <?= h($error) ?></div><?php endif; ?>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="field"><label>Prénom et Nom *</label><input type="text" name="nom" required autocomplete="name" placeholder="Marie Dupont"></div>
          <div class="field"><label>Email *</label><input type="email" name="email" required autocomplete="email" placeholder="marie@email.fr"></div>
        </div>
        <div class="field"><label>Pourquoi rejoignez-vous le mouvement ?</label><textarea name="motivation" placeholder="Ce qui vous motive..."></textarea></div>
        <p style="font-size:0.78rem;color:var(--muted);margin-bottom:16px">Vos données sont protégées (RGPD). Vous pouvez vous désinscrire à tout moment.</p>
        <button type="submit" style="width:100%;padding:16px;background:linear-gradient(135deg,var(--bleu),var(--rouge));color:white;border:none;border-radius:12px;font-size:1.1rem;font-weight:700;cursor:pointer">🤝 Je rejoins le mouvement →</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</main>
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom"><p>© <?= date('Y') ?> <?= h(SITE_NAME) ?> — <a href="/mentions-legales/">Mentions légales</a></p></div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>

