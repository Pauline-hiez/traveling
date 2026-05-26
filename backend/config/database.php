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
}
