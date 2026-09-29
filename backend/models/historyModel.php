<?php

class History
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function add(int $userId, string $action): void
    {
        // Ajoute une action à l'historique
        $this->db->prepare("INSERT INTO history (user_id, action) VALUES (:uid, :action)")->execute([':uid' => $userId, ':action' => $action]);
    }

    public function getByUser(int $userId, int $limit = 0): array
    {
        // Récupère l'historique d'un utilisateur
        $limitStr = $limit > 0 ? "LIMIT $limit" : '';
        $stmt = $this->db->prepare("
            SELECT * FROM history WHERE user_id = :uid ORDER BY created_at DESC $limitStr
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }
}
