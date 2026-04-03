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
    $sujet   = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type    = in_array($_POST['type'] ?? '', ['contact','adhesion','media','signalement']) ? $_POST['type'] : 'contact';
    if (!$nom || !$email || !$message) $error = "Tous les champs obligatoires doivent être remplis.";
    elseif (strlen($message) < 20) $error = "Votre message est trop court.";
    else {
        $ip_hash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . SECRET_KEY);
        Database::query(
            "INSERT INTO ag_messages (nom, email, sujet, message, type, ip_hash) VALUES (?,?,?,?,?,?)",
            [$nom, $email, $sujet, $message, $type, $ip_hash]
        );
        $success = "✅ Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.";
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact — <?= h(SITE_NAME) ?></title>
<meta name="description" content="Contactez <?= h(SITE_NAME) ?> — Rejoignez le mouvement citoyen français.">
<link rel="canonical" href="<?= ag_canonical('contact/') ?>">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.contact-hero{background:linear-gradient(135deg,#0d1117 0%,#161b22 100%);border-bottom:1px solid var(--border);padding:60px 0;text-align:center}
.contact-hero h1{font-size:2.5rem;font-weight:900;margin-bottom:12px}
.contact-hero p{color:var(--text2);font-size:1.05rem}
.contact-layout{display:grid;grid-template-columns:1fr 1.5fr;gap:40px;max-width:900px;margin:0 auto;padding:60px 20px}
.contact-info h2{font-size:1.1rem;font-weight:800;margin-bottom:20px}
.contact-item{display:flex;gap:14px;margin-bottom:24px;align-items:flex-start}
.contact-icon{font-size:1.5rem;width:44px;height:44px;background:rgba(0,35,149,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.contact-item strong{display:block;color:white;margin-bottom:4px}
.contact-item p{color:var(--text2);font-size:0.88rem}
.contact-form-card{background:var(--dark2);border:1px solid var(--border);border-radius:20px;padding:32px}
.contact-form-card h2{font-size:1.1rem;font-weight:800;margin-bottom:24px}
.field{margin-bottom:16px}
label{display:block;font-size:0.83rem;color:var(--muted);margin-bottom:6px;font-weight:600}
input,select,textarea{width:100%;background:var(--dark);border:1px solid var(--border);color:var(--text);padding:12px 14px;border-radius:10px;font-size:0.9rem;outline:none;font-family:inherit;transition:.2s}
input:focus,select:focus,textarea:focus{border-color:var(--bleu);box-shadow:0 0 0 3px rgba(0,35,149,.15)}
textarea{resize:vertical;min-height:140px}
.msg-s{background:rgba(5,150,105,.1);border:1px solid rgba(5,150,105,.3);color:#34d399;padding:14px;border-radius:10px;margin-bottom:16px}
.msg-e{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:14px;border-radius:10px;margin-bottom:16px}
@media(max-width:768px){.contact-layout{grid-template-columns:1fr}}
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
      <a href="/rejoindre/" class="nav-link">Rejoindre</a>
      <a href="/contact/" class="nav-link active">Contact</a>
    </nav>
    <div class="header-actions"><button class="hamburger" id="hamburger">☰</button></div>
  </div>
</header>
<section class="contact-hero">
  <div class="container">
    <h1>📬 Nous contacter</h1>
    <p>Une question, une idée, un témoignage ? Écrivez-nous.</p>
  </div>
</section>
<main id="main">
  <div class="contact-layout">
    <div class="contact-info">
      <h2>📍 Nos coordonnées</h2>
      <div class="contact-item">
        <div class="contact-icon">📧</div>
        <div><strong>Email</strong><p><?= h(SITE_EMAIL) ?></p></div>
      </div>
      <div class="contact-item">
        <div class="contact-icon">♿</div>
        <div><strong>Accessibilité</strong><p>Ce formulaire est accessible aux lecteurs d'écran et compatible avec les technologies d'assistance.</p></div>
      </div>
      <div class="contact-item">
        <div class="contact-icon">🔒</div>
        <div><strong>Données personnelles</strong><p>Vos données ne sont jamais partagées. Conformité RGPD totale. Suppression sur demande.</p></div>
      </div>
      <div class="contact-item">
        <div class="contact-icon">⏱️</div>
        <div><strong>Délai de réponse</strong><p>48 à 72 heures ouvrables. Pour les urgences médias : 24h.</p></div>
      </div>
    </div>
    <div class="contact-form-card">
      <h2>✉️ Envoyer un message</h2>
      <?php if ($success): ?><div class="msg-s"><?= $success ?></div><?php endif; ?>
      <?php if ($error): ?><div class="msg-e">⚠️ <?= h($error) ?></div><?php endif; ?>
      <?php if (!$success): ?>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="field">
          <label for="type">Type de demande</label>
          <select name="type" id="type">
            <option value="contact">💬 Contact général</option>
            <option value="adhesion">🤝 Adhésion au mouvement</option>
            <option value="media">📺 Demande presse / médias</option>
            <option value="signalement">⚠️ Signalement / Urgence</option>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="field">
            <label for="nom">Nom complet *</label>
            <input type="text" id="nom" name="nom" required placeholder="Votre nom" autocomplete="name">
          </div>
          <div class="field">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required placeholder="votre@email.fr" autocomplete="email">
          </div>
        </div>
        <div class="field">
          <label for="sujet">Sujet</label>
          <input type="text" id="sujet" name="sujet" placeholder="Sujet de votre message" maxlength="255">
        </div>
        <div class="field">
          <label for="message">Message * <span style="font-weight:400">(minimum 20 caractères)</span></label>
          <textarea id="message" name="message" required placeholder="Votre message..."></textarea>
        </div>
        <p style="font-size:0.78rem;color:var(--muted);margin-bottom:16px">En envoyant ce message, vous acceptez notre <a href="/confidentialite/" style="color:#60a5fa">politique de confidentialité</a>.</p>
        <button type="submit" class="btn-primary" style="width:100%;padding:14px;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer">📬 Envoyer le message</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</main>
<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> <?= h(SITE_NAME) ?> — <a href="/mentions-legales/">Mentions légales</a> — <a href="/confidentialite/">Confidentialité</a></p>
    </div>
  </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>

