<?php

class Favorite
{
    private PDO $db;
    private ?History $history = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if (file_exists(ROOT . '/backend/models/historyModel.php')) {
            require_once ROOT . '/backend/models/historyModel.php';
            $this->history = new History();
        }
    }

    public function toggle(int $userId, int $articleId): bool
    {
        // Bascule favori/non favori
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :uid AND article_id = :aid");
        $stmt->execute([':uid' => $userId, ':aid' => $articleId]);

        if ($stmt->fetchColumn()) {
            $this->db->prepare("DELETE FROM favorites WHERE user_id = :uid AND article_id = :aid")->execute([':uid' => $userId, ':aid' => $articleId]);
            if ($this->history) {
                // Log de l'action
                $tstmt = $this->db->prepare("SELECT title FROM articles WHERE id = :aid");
                $tstmt->execute([':aid' => $articleId]);
                $title = $tstmt->fetchColumn();
                $label = $title ? "\"$title\"" : "#{$articleId}";
                $this->history->add($userId, "A retiré des favoris l'article {$label}");
            }
            return false;
        }

        $this->db->prepare("INSERT INTO favorites (user_id, article_id) VALUES (:uid, :aid)")->execute([':uid' => $userId, ':aid' => $articleId]);
        if ($this->history) {
            // Log de l'action
            $tstmt = $this->db->prepare("SELECT title FROM articles WHERE id = :aid");
            $tstmt->execute([':aid' => $articleId]);
            $title = $tstmt->fetchColumn();
            $label = $title ? "\"$title\"" : "#{$articleId}";
            $this->history->add($userId, "A ajouté aux favoris l'article {$label}");
        }

        return true;
    }

    public function exists(int $userId, int $articleId): bool
    {
        // Vérifie si l'article est en favori
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :uid AND article_id = :aid");
        $stmt->execute([':uid' => $userId, ':aid' => $articleId]);
        return (bool) $stmt->fetchColumn();
    }
}
