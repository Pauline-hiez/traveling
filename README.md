# Traveling

Traveling est un site web hybride **voyage & cinéma** : un blog d'articles (destinations et films), une base de films/séries enrichie via l'API TMDB, une carte de lieux à visiter, un espace membre et un back-office d'administration.

Le projet est développé en **PHP natif** (sans framework comme Laravel ou Symfony) avec une architecture MVC maison, pensée pour tourner facilement sur un environnement local type [Laragon](https://laragon.org/) ou sur un hébergement mutualisé classique.

## Sommaire

- [Stack technique](#stack-technique)
- [Architecture du projet](#architecture-du-projet)
- [Fonctionnalités](#fonctionnalités)
- [Installation](#installation)
- [Configuration (.env)](#configuration-env)
- [Base de données](#base-de-données)
- [Envoi de la newsletter](#envoi-de-la-newsletter)
- [Sécurité](#sécurité)

## Stack technique

### Backend

- **PHP 8+** en programmation orientée objet, sans framework — architecture MVC "faite maison" (routeur, contrôleurs, modèles, services).
- **PDO / MySQL** pour l'accès aux données, avec requêtes préparées (protection injection SQL) et migrations légères auto-appliquées au démarrage (`backend/config/database.php` vérifie/ajoute certaines colonnes et tables si absentes).
- **Sessions PHP natives** (`$_SESSION`) pour l'authentification et le panier de contexte utilisateur.
- **cURL / `file_get_contents`** pour les appels aux API externes (pas de client HTTP tiers).
- Envoi d'e-mails **sans dépendance externe** (PHPMailer, Symfony Mailer, etc.) : un client SMTP "brut" écrit à la main (`MailService`) avec une alternative via l'**API transactionnelle Brevo (Sendinblue)**.
- Un **inliner CSS maison** (`CssInliner`) pour convertir les feuilles de style en styles inline dans les e-mails HTML (compatibilité clients mail).

### APIs & services externes

- **[TMDB (The Movie Database)](https://www.themoviedb.org/documentation/api)** — recherche et fiches détaillées de films/séries (`TmdbService`).
- **[Nominatim / OpenStreetMap](https://nominatim.org/)** — géocodage et recherche de lieux (`MapService`).
- **[Leaflet.js](https://leafletjs.com/)** — affichage des cartes interactives côté frontend.
- **Google OAuth 2.0** — connexion "Se connecter avec Google" (`AuthService`).
- **Brevo (Sendinblue)** — envoi transactionnel d'e-mails via API, en alternative au SMTP classique.

### Frontend

- **PHP en templating natif** (vues `.php` avec `include`/`extract`, pas de moteur de template type Twig/Blade).
- **Tailwind CSS** (chargé via CDN `cdn.tailwindcss.com`) complété par une feuille de style custom (`frontend/assets/css/style.css`).
- **JavaScript vanilla** (pas de framework front comme Vue/React) organisé par page/fonctionnalité : `app.js`, `auth.js`, `navbar.js`, `search.js`, `article.js`, `lieu.js`, `lieu-slider.js`, les scripts `admin-*.js` pour le back-office, etc.
- Polices **Google Fonts** (Playfair Display, Lato).

### Infrastructure

- Point d'entrée unique `index.php` + réécriture d'URL via **`.htaccess`** (Apache `mod_rewrite`), adapté à un serveur Apache (Laragon, OVH, o2switch...).
- Pas de build front (pas de Webpack/Vite/npm) : les assets sont servis tels quels.
- Pas de gestionnaire de dépendances (pas de `composer.json`) : tout le code est natif, à l'exception des CDN chargés côté navigateur.

## Architecture du projet

```
traveling/
├── index.php                  # Point d'entrée unique (front controller)
├── .htaccess                  # Réécriture d'URL + en-têtes de sécurité
├── .env / .env.example        # Variables d'environnement
├── backend/
│   ├── router.php             # Table de routage (méthode, pattern, contrôleur, action)
│   ├── config/                # Config BDD + constantes globales
│   ├── controllers/           # Contrôleurs (Home, Article, Film, Lieu, Auth, User, Admin, Search, Page...)
│   ├── models/                # Accès aux données (PDO)
│   ├── services/              # Logique métier / intégrations (Mail, TMDB, Map, Auth, Newsletter, CssInliner...)
│   ├── middleware/             # Auth, rôles, CSRF
│   └── commands/               # Scripts exécutables en CLI (ex. envoi newsletter via cron)
├── frontend/
│   ├── views/                  # Vues PHP par domaine (home, article, films, lieux, auth, admin, emails...)
│   ├── components/             # Layout, navbar, footer, cartes, pagination...
│   └── assets/                 # css/, js/, img/
└── database/
    └── traveling.sql           # Schéma + données de départ
```

### Flux d'une requête

1. Toute requête passe par `.htaccess` → `index.php`.
2. `index.php` charge le `.env`, configure les erreurs selon `APP_ENV`, démarre la session et le CSRF, puis délègue à `backend/router.php`.
3. Le routeur fait correspondre la méthode HTTP + l'URL à une paire `(Contrôleur, action)` définie dans un tableau de routes, instancie le contrôleur et exécute l'action.
4. Le contrôleur s'appuie sur les **models** (accès BDD) et **services** (logique métier / API externes), puis rend une vue via `Services\Renderer`, qui injecte la vue dans `frontend/components/layout.php`.

## Fonctionnalités

- **Articles** (voyage/cinéma) : liste paginée, recherche avec autocomplétion, likes, favoris, commentaires (avec likes et signalement).
- **Films/séries** : liste et fiches détaillées enrichies via TMDB (casting, genres, œuvres similaires).
- **Lieux** : liste, fiche détaillée avec carte Leaflet, recherche géographique via Nominatim.
- **Authentification** : inscription/connexion classiques (bcrypt), connexion Google OAuth, réinitialisation de mot de passe par e-mail.
- **Profil utilisateur** : modification email/pseudo/mot de passe/avatar/fond, historique, favoris.
- **Back-office admin** : gestion des utilisateurs et rôles, modération des commentaires (signalements, avertissements), gestion des articles (création/édition avec recherche TMDB et lieux intégrée).
- **Newsletter** : inscription, désabonnement par lien signé, template HTML avec CSS inliné, envoi hebdomadaire automatisable via une commande CLI.
- **Pages statiques** : à propos, contact (avec envoi d'e-mail), FAQ, mentions légales.

## Installation

Prérequis : PHP 8+, MySQL/MariaDB, Apache avec `mod_rewrite` (ex. via [Laragon](https://laragon.org/)).

1. Cloner le dépôt dans le dossier servi par Apache (ex. `www/traveling` sous Laragon).
2. Créer la base de données et importer le schéma :
   ```bash
   mysql -u root -p -e "CREATE DATABASE traveling CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   mysql -u root -p traveling < database/traveling.sql
   ```
3. Copier `.env.example` en `.env` et renseigner les variables (voir section suivante).
4. Démarrer Apache/MySQL (via Laragon ou équivalent) et ouvrir l'URL configurée dans `APP_URL`.

## Configuration (.env)

| Variable | Description |
|---|---|
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | Connexion MySQL |
| `APP_URL`, `APP_ENV` | URL de l'application, environnement (`development` affiche les erreurs) |
| `TMDB_API_KEY`, `TMDB_BASE_URL`, `TMDB_IMG_URL` | Accès à l'API TMDB |
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` | OAuth Google |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, `MAIL_FROM`, `MAIL_FROM_NAME` | Envoi SMTP classique |
| `BREVO_API_KEY` | Envoi transactionnel via l'API Brevo (alternative au SMTP) |
| `MAIL_LOGO_URL`, `MAIL_TEXTURE_URL`, `MAIL_*_HERO_URL` | Images utilisées dans les templates d'e-mails (bienvenue, avertissement, réinitialisation, newsletter) |

## Base de données

Le schéma complet est fourni dans [`database/traveling.sql`](database/traveling.sql) : `users`, `articles`, `article_films`, `article_lieux`, `comments`, `comment_likes`, `likes`, `favorites`, `history`, `lieux`, `newsletter`, `password_resets`, `reports`.

Certaines colonnes/tables (`reports.treated_at`, `password_resets`, `articles.publish_at`) sont vérifiées et créées automatiquement au premier accès à la base si elles sont absentes (voir `Database::getInstance()`), pour faciliter les montées de version sans script de migration dédié.

## Envoi de la newsletter

La logique d'envoi hebdomadaire (`NewsletterService::sendWeekly()`) sélectionne les derniers articles publiés, construit l'e-mail HTML (CSS inliné) et l'envoie à tous les abonnés avec un lien de désabonnement signé (HMAC). Ce processus est destiné à être déclenché par une tâche planifiée (cron) via le script `backend/commands/send-newsletter.php`.

## Sécurité

- Requêtes SQL préparées (PDO) contre les injections SQL.
- Protection **CSRF** (`CsrfMiddleware`) sur les formulaires.
- Mots de passe hachés en **bcrypt** (`password_hash`/`password_verify`).
- Middlewares d'**authentification** (`AuthMiddleware`) et de **rôles** (`RoleMiddleware`) pour protéger les routes sensibles (profil, admin).
- En-têtes HTTP de sécurité (`X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`) et blocage d'accès aux fichiers sensibles (`.env`, `.sql`) via `.htaccess`.
