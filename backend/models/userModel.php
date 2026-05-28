<?php

class User
{
    private PDO $db;

    public function __construct()
    {
        // Connexion unique à la base de données
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): array|false
    {
        // Recherche par email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function findByPseudo(string $pseudo): array|false
    {
        // Recherche par pseudo
        $stmt = $this->db->prepare("SELECT * FROM users WHERE pseudo = :pseudo");
        $stmt->execute([':pseudo' => $pseudo]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        // Recherche par ID
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findByGoogleId(string $googleId): array|false
    {
        // Recherche par google_id
        $stmt = $this->db->prepare("SELECT * FROM users WHERE google_id = : gid");
        $stmt->execute([':gid' => $googleId]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        // Création d'un utilisateur
        $sql = "INSERT INTO users (pseudo, email, password, role, google_id)
                VALUES (:pseudo, :email, :password, 'user', :google_id)";
        $this->db->prepare($sql)->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        // Mise à jour partielle
        $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $data['id'] = $id;
        $this->db->prepare("UPDATE users SET $sets WHERE id = :id")->execute($data);
    }

    public function delete(int $id): void
    {
        // Suppression d'un utilisateur
        $this->db->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $id]);
    }

    public function getAll(): array
    {
        // liste des utilisateurs
        return $this->db->query("SELECT id, pseudo, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    }

    public function countFavorites(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countLikes(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) likes WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countComments(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) comments WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function pseudoExists(string $pseudo): bool
    {
        // Vérifie si un pseudo existe
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE pseudo = :pseudo");
        $stmt->execute([':pseudo' => $pseudo]);
        return (bool) $stmt->fetchColumn();
    }

    public function emailExists(string $email): bool
    {
        // Vérifie si un email existe
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return (bool) $stmt->fetchColumn();
    }
}
