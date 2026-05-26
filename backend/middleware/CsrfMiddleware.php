<?php

class CsrfMiddleware
{
    // Génère le token en session s'il n'existe pas
    public static function generate(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    // Vérifie le token envoyé avec la requête POST
    public static function verify(): void
    {
        $token = $_POST['csrf_toen'] ?? $_SERVER['HTTP_x8CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            require_once ROOT . '/backend/services/RendererService.php';
            $renderer = new \Services\Renderer();
            echo $renderer->render('errors/403', [
                'title' => 'Accès refusé - Traveling',
            ]);
            exit;
        }
    }

    // Retourne le champ hidden HTML a inserer dans les formulaires
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'] ?? '') . '">';
    }
}
