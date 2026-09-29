<?php

class Like
{
    private PDO $db;

    // Historique (optionnel)
    private ?History $history = null;

    public function __construct()
    {
        // Connexion unique a la base
        $this->db = Database::getInstance();
        if (file_exists(ROOT . '/backend/models/historyModel.php')) {
            require_once ROOT . '/backend/models/historyModel.php';
            $this->history = new History();
        }
    }

    public function toggle(int $userId, int $articleId): bool
    {
        // Bascule like / unlike
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE user_id = :uid AND article_id = :aid");
        $stmt->execute([':uid' => $userId, ':aid' => $articleId]);

        if ($stmt->fetchColumn()) {
            $this->db->prepare("DELETE FROM likes WHERE user_id = :uid AND article_id = :aid")->execute([':uid' => $userId, ':aid' => $articleId]);
            if ($this->history) {
                // Log de l'action
                $tstmt = $this->db->prepare("SELECT title FROM articles WHERE id = :aid");
                $tstmt->execute([':aid' => $articleId]);
                $title = $tstmt->fetchColumn();
                $label = $title ? "\"$title\"" : "#{$articleId}";
                $this->history->add($userId, "A retiré son like sur l'article {$label}");
            }
            return false;
        }

        $this->db->prepare("INSERT INTO likes (user_id, article_id) VALUES (:uid, :aid)")->execute([':uid' => $userId, ':aid' => $articleId]);
        if ($this->history) {
            // Log de l'action
            $tstmt = $this->db->prepare("SELECT title FROM articles WHERE id = :aid");
            $tstmt->execute([':aid' => $articleId]);
            $title = $tstmt->fetchColumn();
            $label = $title ? "\"$title\"" : "#{$articleId}";
            $this->history->add($userId, "A aimé l'article {$label}");
        }
        return true;
    }

    public function exists(int $userId, int $articleId): bool
    {
        // Verifie si l'utilisateur a deja like
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE user_id = :uid AND article_id = :aid");
        $stmt->execute([':uid' => $userId, ':aid' => $articleId]);
        return (bool) $stmt->fetchColumn();
    }
}
