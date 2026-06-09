<?php

require_once ROOT . '/backend/config/database.php';

class Newsletter
{
    private PDO $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function subscribe(string $email): bool
    {
        try {
            // Vérifie si l'email est déjà inscrit
            $stmt = $this->db->prepare("SELECT id FROM newsletter WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                return false; // Déjà inscrit
            }

            // Ajoute un nouvel abonné
            $stmt = $this->db->prepare("INSERT INTO newsletter (email, created_at) VALUES (:email, NOW())");
            return (bool) $stmt->execute([':email' => $email]);
        } catch (PDOException $e) {
            error_log("[NewsletterModel] subscribe error: " . $e->getMessage());
            return false;
        }
    }

    public function unsubscribe(string $email): bool
    {
        // Suppression d'un email
        try {
            $stmt = $this->db->prepare("DELETE FROM newsletter WHERE email = :email");
            return (bool) $stmt->execute([':email' => $email]);
        } catch (PDOException $e) {
            error_log("[NewsletterModel] unsubscribe error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll(): array
    {
        // Liste de tous les emails
        try {
            $stmt = $this->db->query("SELECT email FROM newsletter");
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (PDOException $e) {
            error_log("[NewsletterModel] getAll error: " . $e->getMessage());
            return [];
        }
    }
}
