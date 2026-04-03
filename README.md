# 🏛️ AgoraCMS

> **La plateforme citoyenne open-source que les partis politiques français n'ont plus aucune excuse de ne pas utiliser.**

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-34d399?style=flat-square)
![Prix](https://img.shields.io/badge/Prix-100%25%20Gratuit-ED2939?style=flat-square)
![Installation](https://img.shields.io/badge/Installation-2%20minutes-fbbf24?style=flat-square)
![WCAG](https://img.shields.io/badge/WCAG-2.1%20AA-7c3aed?style=flat-square)

---

## ⚡ Ce que c'est

AgoraCMS est un CMS PHP open-source conçu pour **tout mouvement citoyen, association, communauté ou individu** qui veut :

- Publier des articles et un programme
- Organiser des **débats structurés** (pour/contre, vote anonyme)
- Expliquer les lois françaises simplement
- Respecter les **12 millions de Français handicapés** (WCAG 2.1 natif)
- Être **hébergé en France**, **RGPD-conforme**, **100% souverain**

**En 2026, aucun parti politique ne peut prétendre ne pas avoir les outils.**
Ce dépôt est la preuve. Prenez-le. Utilisez-le. Ou assumez votre refus.

---

## 🚀 Installation en 2 minutes

### Prérequis
- Hébergement PHP 8.2+ avec MySQL (O2switch, PlanetHoster, Infomaniak...)
- Une base de données MySQL créée dans votre cPanel

### 3 étapes

**1. Télécharger et uploader**
```bash
# Télécharger ce repo
git clone https://github.com/VOTRE_USERNAME/agora-cms.git
# Ou télécharger le ZIP → Extraire dans public_html/
```

**2. Lancer le wizard dans votre navigateur**
```
https://votre-domaine.fr/install/wizard.php
```

**3. Supprimer le dossier install/ après installation**
```bash
rm -rf public_html/install/
```

C'est tout. Votre site est en ligne.

---

## ✅ Fonctionnalités incluses

| Fonctionnalité | Statut | Description |
|----------------|--------|-------------|
| 📝 Articles & Blog | ✅ | Éditeur complet, catégories, images |
| 💬 Débats citoyens | ✅ | Pour/contre structuré, vote anonyme sécurisé |
| ⚖️ Lois expliquées | ✅ | Textes de loi traduits en langage simple |
| ♿ Accessibilité WCAG 2.1 | ✅ | A+, A-, contraste, dyslexie, lecture audio |
| 🔒 Protection de documents | ✅ | Filigrane anti-fuite |
| 🗺️ Sitemap XML | ✅ | SEO automatique |
| 🛠️ Admin complet | ✅ | Articles, paramètres, modules ON/OFF |
| 🎨 Couleurs personnalisables | ✅ | Via wizard ou admin |
| 🔧 Wizard d'installation | ✅ | Comme WordPress, en 7 étapes |
| 🇫🇷 Hébergement France | ✅ | Compatible O2switch, PlanetHoster |
| 📱 Responsive mobile | ✅ | 100% adaptatif |
| 🛡️ Sécurité production | ✅ | CSRF, bcrypt, HSTS, CSP, headers |

---

## 📣 Message aux partis politiques français

En **2026** :
- **12 millions de Français handicapés** sont exclus du débat politique numérique
- Les sites politiques sont non-conformes RGPD, inaccessibles, hébergés à l'étranger
- Les lois votées sont incompréhensibles pour 90% des citoyens
- Des débats structurés et honnêtes n'existent nulle part

AgoraCMS règle **tout ça gratuitement en 2 minutes**.

**Vous n'avez plus aucune excuse.**

---

## 🏗️ Structure du projet

```
agora-cms/
├── .htaccess              ← Sécurité Apache + routing
├── index.php              ← Page d'accueil
├── article.php            ← Détail article
├── admin/                 ← Interface administration
│   ├── index.php          ← Dashboard
│   ├── articles.php       ← Gestion articles
│   ├── edit.php           ← Éditeur article
│   └── settings.php       ← Paramètres
├── assets/
│   ├── css/style.css      ← Thème complet (dark, accessible)
│   └── js/                ← Interactions + audio accessibilité
├── config/
│   ├── database.php       ← Connexion PDO MySQL/SQLite
│   └── functions.php      ← Fonctions utilitaires
├── install/
│   ├── wizard.php         ← Installateur (7 étapes)
│   └── install.sql        ← Schéma base de données
├── pages/
│   ├── debats.php         ← Débats pour/contre
│   ├── lois.php           ← Lois expliquées
│   ├── handicap.php       ← Accessibilité
│   ├── manifeste.php      ← Programme
│   ├── contact.php        ← Contact
│   ├── mentions.php       ← Mentions légales
│   └── privacy.php        ← Politique de confidentialité
└── uploads/               ← Médias (ignoré par git)
```

> ⚠️ `config/config.php` est **généré automatiquement** par le wizard. Il ne fait pas partie du dépôt.

---

## 🔒 Sécurité

- HTTPS forcé via `.htaccess`
- Tokens CSRF sur tous les formulaires
- Mots de passe hashés en bcrypt (cost 12)
- Headers HTTP de sécurité (HSTS, CSP, X-Frame-Options, X-Content-Type-Options...)
- Accès direct aux fichiers sensibles bloqué
- Votes anonymisés (IP hashée avec SECRET_KEY + date, jamais stockée en clair)
- SECRET_KEY générée cryptographiquement à chaque installation

---

## ⚙️ Configuration requise

| Composant | Minimum | Recommandé |
|-----------|---------|------------|
| PHP | 8.0 | 8.2+ |
| MySQL | 5.7 | 8.0+ |
| Espace disque | 50 Mo | 500 Mo |
| RAM PHP | 64 Mo | 128 Mo |

Compatible : O2switch ✅ · PlanetHoster ✅ · Infomaniak ✅ · OVH ✅

---

## 🤝 Contribuer

Les contributions sont les bienvenues !

1. Fork ce dépôt
2. Créez une branche `feature/ma-fonctionnalite`
3. Committez vos changements
4. Ouvrez une Pull Request

Idées de contributions :
- Thème clair
- Internationalisation (EN, ES, DE)
- Module pétitions
- Intégration réseaux sociaux
- Application mobile PWA

---

## 📄 Licence

**MIT** — Libre d'utilisation, modification et redistribution, y compris commercial.

Voir [LICENSE](LICENSE) pour les détails.

---

## 💬 Pourquoi ce projet existe

> Ce projet est né d'un constat simple : les partis politiques français dépensent des millions en communication numérique tout en ignorant les obligations légales les plus basiques — accessibilité, RGPD, hébergement souverain.
>
> AgoraCMS leur donne gratuitement, en 2 minutes, tout ce qu'il faut.
> Le reste est une question de volonté.

---

*AgoraCMS — 2026 — Licence MIT — Gratuit pour toujours*
