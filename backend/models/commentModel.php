<?php

class Comment
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByArticle(int $articleId): array
    {
        // Commentaires d'un article par ordre chronologique
        $sql = "SELECT c.*, u.pseudo, u.avatar
                FROM comments c
                JOIN users u ON u.id = c.user_id
                WHERE c.article_id = :aid
                ORDER BY c.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aid' => $articleId]);
        return $stmt->fetchAll();
    }

    public function create(int $articleId, int $userId, string $content, ?int $parentId = null): void
    {
        // Création d'un commentaire
        $this->db->prepare("
            INSERT INTO comments (article_id, user_id, content, parent_id)
            VALUES (:aid, :uid, :content, :pid)
        ")->execute([':aid' => $articleId, ':uid' => $userId, ':content' => $content, ':pid' => $parentId]);

        if (file_exists(ROOT . '/backend/models/historyModel.php')) {
            require_once ROOT . '/backend/models/historyModel.php';
            try {
                $tstmt = $this->db->prepare("SELECT title FROM articles WHERE id = :aid");
                $tstmt->execute([':aid' => $articleId]);
                $title = $tstmt->fetchColumn();
                $label = $title ? "\"$title\"" : "#{$articleId}";
                $h = new History();
                $h->add($userId, "A commenté l'article {$label}");
            } catch (Throwable $e) {
                // Ignore logging errors
            }
        }
    }

    public function delete(int $id): void
    {
        // Suppression d'un commentaire
        $this->db->prepare("DELETE FROM comments WHERE id = :id")->execute([':id' => $id]);
    }

    public function getReported(): array
    {
        // Liste des signalements
        $sql = "SELECT r.*, u1.pseudo AS reporter, c.content AS comment_content, c.id AS comment_id, c.article_id
                FROM reports r
                JOIN users u1 ON u1.id = r.reporter_id
                JOIN comments c ON c.id = r.comment_id
                JOIN users u2 ON u2.id = c.user_id
                ORDER BY r.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function markReportsHandled(int $commentId): void
    {
        // Marque les signalements comme traités
        $stmt = $this->db->prepare("UPDATE reports SET treated_at = NOW() WHERE comment_id = :cid AND treated_at IS NULL");
        $stmt->execute([':cid' => $commentId]);
    }

    public function report(int $reportedId, int $commentId, string $reason): void
    {
        // Enregistre un signalement (ignore si déjà signalé)
        $this->db->prepare("
            INSERT IGNORE INTO reports (reporter_id, comment_id, reason)
            VALUES (:rid, :cid, :reason)
        ")->execute([':rid' => $reportedId, ':cid' => $commentId, ':reason' => $reason]);
    }
}
