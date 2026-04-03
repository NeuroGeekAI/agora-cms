<?php
define('AGORA', true);
require_once __DIR__ . '/config/config.php';
http_response_code(404);
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Page introuvable — <?= h(SITE_NAME) ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:20px">
  <div>
    <div style="font-size:5rem;margin-bottom:20px">🏛️</div>
    <div style="font-size:6rem;font-weight:900;color:#ED2939;line-height:1">404</div>
    <h1 style="font-size:1.5rem;margin:16px 0 8px">Page introuvable</h1>
    <p style="color:#9ca3af;margin-bottom:32px">Cette page n'existe pas ou a été déplacée.</p>
    <a href="/" style="display:inline-block;padding:14px 28px;background:#002395;color:white;border-radius:10px;font-weight:700;text-decoration:none">← Retour à l'accueil</a>
  </div>
</div>
</body>
</html>
