<?php

class CommentLike
{
    private PDO $db;

    public function __construct()
    {
        // Connexion unique a la base
        $this->db = Database::getInstance();
    }

    public function toggle(int $userId, int $commentId): bool
    {
        // Bascule like/unlike
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM comment_likes WHERE user_id = :uid AND comment_id = :cid");
        $stmt->execute([':uid' => $userId, ':cid' => $commentId]);

        if ($stmt->fetchColumn()) {
            $this->db->prepare("DELETE FROM comment_likes WHERE user_id = :uid AND comment_id = :cid")->execute([':uid' => $userId, ':cid' => $commentId]);
            return false;
        }

        $this->db->prepare("INSERT INTO comment_likes (user_id, comment_id) VALUES (:uid, :cid)")->execute([':uid' => $userId, ':cid' => $commentId]);
        return true;
    }

    public function exists(int $userId, int $commentId): bool
    {
        // Verifie si l'utilisateur a deja like
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM comment_likes WHERE user_id = :uid AND comment_id = :cid");
        $stmt->execute([':uid' => $userId, ':cid' => $commentId]);
        return (bool) $stmt->fetchColumn();
    }

    public function countForComment(int $commentId): int
    {
        // Compte les likes d'un commentaire
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = :cid");
        $stmt->execute([':cid' => $commentId]);
        return (int) $stmt->fetchColumn();
    }
}
