-- ═══════════════════════════════════════════════════════════════
-- AgoraCMS — Schéma base de données MySQL
-- CMS Politique Open-Source — agoracms.fr
-- Encodage : utf8mb4 | Collation : utf8mb4_unicode_ci
-- ═══════════════════════════════════════════════════════════════
SET NAMES utf8mb4;
SET time_zone = '+01:00';
SET foreign_key_checks = 0;

-- ── AUTEURS ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ag_auteurs (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    bio        TEXT,
    email      VARCHAR(255),
    avatar     VARCHAR(255),
    role       ENUM('admin','editeur','auteur') DEFAULT 'auteur',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ADMIN USERS ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ag_admin_users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    auteur_id     INT UNSIGNED,
    role          ENUM('superadmin','admin','editeur') DEFAULT 'editeur',
    last_login    DATETIME,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES ag_auteurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ARTICLES ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ag_articles (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(255) NOT NULL,
    slug        VARCHAR(255) NOT NULL UNIQUE,
    extrait     TEXT,
    contenu     LONGTEXT NOT NULL,
    image       VARCHAR(255),
    image_alt   VARCHAR(255),
    categorie   VARCHAR(50) NOT NULL DEFAULT 'programme',
    tags        VARCHAR(500),
    auteur_id   INT UNSIGNED,
    statut      ENUM('publie','brouillon','archive') DEFAULT 'brouillon',
    une         TINYINT(1) DEFAULT 0 COMMENT 'Article à la une',
    vues        INT UNSIGNED DEFAULT 0,
    partages    INT UNSIGNED DEFAULT 0,
    access_score TINYINT UNSIGNED DEFAULT 100 COMMENT 'Score accessibilité handicap 0-100',
    meta_title  VARCHAR(70),
    meta_desc   VARCHAR(160),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES ag_auteurs(id) ON DELETE SET NULL,
    INDEX idx_statut_cat (statut, categorie),
    INDEX idx_slug (slug),
    INDEX idx_une (une),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PÉTITIONS ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ag_petitions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre       VARCHAR(255) NOT NULL,
    slug        VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    objectif    INT UNSIGNED DEFAULT 10000,
    signatures  INT UNSIGNED DEFAULT 0,
    categorie   VARCHAR(50),
    statut      ENUM('active','fermee','atteinte') DEFAULT 'active',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ag_petition_signatures (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    petition_id INT UNSIGNED NOT NULL,
    nom         VARCHAR(100) NOT NULL,
    prenom      VARCHAR(100) NOT NULL,
    email       VARCHAR(255) NOT NULL,
    code_postal VARCHAR(10),
    ip_hash     VARCHAR(64),
    verified    TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (petition_id) REFERENCES ag_petitions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sign (petition_id, email),
    INDEX idx_petition (petition_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── CONTACT & ADHÉSION ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ag_messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    sujet      VARCHAR(255),
    message    TEXT NOT NULL,
    type       ENUM('contact','adhesion','media','signalement') DEFAULT 'contact',
    ip_hash    VARCHAR(64),
    lu         TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── MÉDIAS ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ag_medias (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename    VARCHAR(255) NOT NULL,
    alt         VARCHAR(255),
    type        VARCHAR(50),
    size_bytes  INT UNSIGNED,
    auteur_id   INT UNSIGNED,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PARAMÈTRES ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ag_settings (
    cle        VARCHAR(100) PRIMARY KEY,
    valeur     TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DONNÉES PAR DÉFAUT (générées par l'installateur wizard.php) ──────────
INSERT IGNORE INTO ag_auteurs (nom, bio, role) VALUES
('Équipe Editoriale', 'La rédaction officielle du site.', 'admin');

-- ⚠️ Le compte admin est créé automatiquement par wizard.php avec le bon hash

INSERT IGNORE INTO ag_settings (cle, valeur) VALUES
('site_name',        'AgoraCMS'),
('site_tagline',     'La plateforme politique de votre mouvement'),
('maintenance',      '0'),
('ga_id',            ''),
('articles_per_page','12'),
('color_primary',    '#002395'),
('color_accent',     '#ED2939'),
('social_twitter',   ''),
('social_facebook',  ''),
('social_telegram',  '');

-- ── TABLE MODULES (ON/OFF par module) ────────────────────────────────────
CREATE TABLE IF NOT EXISTS ag_modules (
    cle        VARCHAR(50) PRIMARY KEY,
    actif      TINYINT(1) DEFAULT 1,
    label      VARCHAR(100),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO ag_modules (cle, actif, label) VALUES
('handicap',       1, 'Handicap & Inclusion'),
('lois',           1, 'Lois françaises expliquées'),
('manifeste',      1, 'Programme & Manifeste'),
('debats',         1, 'Débats en ligne'),
('petitions',      1, 'Pétitions citoyennes'),
('rejoindre',      1, 'Rejoindre le mouvement'),
('proteger_docs',  1, 'Protection de documents'),
('souverainevoix', 1, 'Audio Accessibilité');

-- Article de bienvenue générique
INSERT IGNORE INTO ag_articles (titre, slug, extrait, contenu, categorie, auteur_id, statut, une, image_alt, access_score) VALUES
(
  'Bienvenue sur votre plateforme politique',
  'bienvenue-agoracms',
  'Votre plateforme politique est prête. Personnalisez-la depuis l''administration.',
  '<h2>Votre plateforme est prête !</h2><p>Félicitations pour l''installation d''AgoraCMS. Publiez vos articles, créez des pétitions, organisez des débats.</p><h2>Accessibilité prioritaire</h2><p>AgoraCMS intègre nativement WCAG 2.1 et RGAA pour garantir l''accès à tous les citoyens.</p>',
  'programme', 1, 'publie', 1, 'Image de bienvenue', 95
);

SET foreign_key_checks = 1;