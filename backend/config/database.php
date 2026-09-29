<?php

class Database
{
    private static ?PDO $instance = null;
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . $_ENV['DB_HOST']
                . ';dbname=' . $_ENV['DB_NAME']
                . ';charset=utf8mb4';

            try {
                self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::ensureReportsSchema(self::$instance);
                self::ensurePasswordResetsSchema(self::$instance);
                self::ensureArticlesPublishAtSchema(self::$instance);
                self::ensureUserPositionColumns(self::$instance);
                self::ensureArticleExtraColumns(self::$instance);
                self::ensureLieuExtraColumns(self::$instance);
                self::ensureArticleSliderImagesSchema(self::$instance);
            } catch (PDOException $e) {
                // En production, ne pas afficher l'erreur
                if ($_ENV['APP_ENV'] === 'development') {
                    die('Erreur BDD : ' . $e->getMessage());
                }
                die('Erreur de connexion à la base de données.');
            }
        }
        return self::$instance;
    }
    // Empêche l'instanciation directe et le clonage
    private function __construct() {}
    private function __clone() {}

    private static function ensureReportsSchema(PDO $pdo): void
    {
        // Si la table reports n'existe pas, ne rien faire
        $tbl = $pdo->query("SHOW TABLES LIKE 'reports'");
        if (!$tbl || !$tbl->fetch()) {
            return;
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM reports LIKE 'treated_at'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE reports ADD COLUMN treated_at datetime DEFAULT NULL AFTER created_at");
        }
    }

    private static function ensurePasswordResetsSchema(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
            id int UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id int UNSIGNED NOT NULL,
            token_hash char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
            expires_at datetime NOT NULL,
            used_at datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY token_hash (token_hash),
            KEY user_id (user_id),
            KEY expires_at (expires_at),
            CONSTRAINT password_resets_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private static function ensureArticlesPublishAtSchema(PDO $pdo): void
    {
        $tbl = $pdo->query("SHOW TABLES LIKE 'articles'");
        if (!$tbl || !$tbl->fetch()) {
            return;
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM articles LIKE 'publish_at'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE articles ADD COLUMN publish_at datetime DEFAULT NULL");
        }
    }

    private static function ensureUserPositionColumns(PDO $pdo): void
    {
        $tbl = $pdo->query("SHOW TABLES LIKE 'users'");
        if (!$tbl || !$tbl->fetch()) {
            return;
        }

        foreach (['avatar_pos_x', 'avatar_pos_y', 'background_pos_x', 'background_pos_y'] as $column) {
            $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE '$column'");
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE users ADD COLUMN $column TINYINT UNSIGNED NOT NULL DEFAULT 50");
            }
        }
    }

    private static function ensureArticleExtraColumns(PDO $pdo): void
    {
        $tbl = $pdo->query("SHOW TABLES LIKE 'articles'");
        if (!$tbl || !$tbl->fetch()) {
            return;
        }

        $columns = [
            'img_cover' => "varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            'img_bg' => "varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            'img_caption' => "varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            'category' => "varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL",
        ];

        foreach ($columns as $column => $definition) {
            $stmt = $pdo->query("SHOW COLUMNS FROM articles LIKE '$column'");
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE articles ADD COLUMN $column $definition");
            }
        }
    }

    private static function ensureLieuExtraColumns(PDO $pdo): void
    {
        $tbl = $pdo->query("SHOW TABLES LIKE 'lieux'");
        if (!$tbl || !$tbl->fetch()) {
            return;
        }

        $columns = [
            'bg_lieux' => "varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            'description' => "text COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            'author_tips' => "text COLLATE utf8mb4_unicode_ci DEFAULT NULL",
            'author_suggestions' => "text COLLATE utf8mb4_unicode_ci DEFAULT NULL",
        ];

        foreach ($columns as $column => $definition) {
            $stmt = $pdo->query("SHOW COLUMNS FROM lieux LIKE '$column'");
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE lieux ADD COLUMN $column $definition");
            }
        }
    }

    private static function ensureArticleSliderImagesSchema(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS article_slider_images (
            id int UNSIGNED NOT NULL AUTO_INCREMENT,
            article_id int UNSIGNED NOT NULL,
            image_path varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            caption varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            slider_title varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            slider_text text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (id),
            KEY article_id (article_id),
            CONSTRAINT article_slider_images_ibfk_1 FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $stmt = $pdo->query("SHOW COLUMNS FROM article_slider_images LIKE 'created_at'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE article_slider_images ADD COLUMN created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER caption");
        }
    }
}
