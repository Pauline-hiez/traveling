<?php

require_once ROOT . '/backend/config/database.php';

class PasswordReset
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        // Invalide les anciens tokens et en crée un nouveau
        $this->db->prepare("DELETE FROM password_resets WHERE user_id = :user_id AND used_at IS NULL")->execute([':user_id' => $userId]);
        $this->db->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:uid, :token_hash, expires_at)")->execute([
            ':user_id' => $userId,
            ':token_hash' => $tokenHash,
            ':expires_at' => $expiresAt,
        ]);
    }

    public function findValidByToken(string $token): array|false
    {
        // Recherche un token valide
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1");
        $stmt->execute([':token_hash' => $tokenHash]);
        return $stmt->fetch();
    }

    public function markUsed(int $id): void
    {
        // Marque le token comme utilisé
        $stmt = $this->db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
