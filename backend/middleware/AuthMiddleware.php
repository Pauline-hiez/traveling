<?php

class AuthMiddleware
{
    public static function require(): void
    {
        // Bloque l'accès si l'utilisateur n'est pas connecté
        if (empty($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '?login=required');
            exit;
        }
    }
}
