<?php

class RoleMiddleware
{
    public static function require(string ...$roles): void
    {
        if (!class_exists('AuthMiddleware')) {
            require_once ROOT . '/backend/middleware/AuthMiddleware.php';
        }

        // Vérifie l'authentification
        AuthMiddleware::require();

        $currentRole = $_SESSION['user']['role'] ?? null;

        // Vérifie les droits
        if (!in_array($currentRole, $roles, true)) {
            http_response_code(403);
            require_once ROOT . '/backend/services/RendererService.php';
            $renderer = new \Services\Renderer();
            echo $renderer->render('errors/403', [
                'title' => 'Accès refusé - Traveling',
            ]);
            exit;
        }
    }
}
