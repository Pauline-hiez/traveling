<?php

// Point d'entrée unique
// Chargement des variables d'environnement depuis .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Configuration des erreurs selon l'environnement
if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Démarrage de la session
session_start();

// Chargement de la config
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/config/constants.php';

// Chargement des middlewares globaux
require_once __DIR__ . '/backend/middleware/CsrfMiddleware.php';
CsrfMiddleware::generate();

// Routage
require_once __DIR__ . '/backend/router.php';
