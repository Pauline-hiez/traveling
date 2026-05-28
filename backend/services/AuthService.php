<?php

class AuthService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct(string $baseUrl = '')
    {
        // Charge les variables .env et configure OAuth Google
        $this->baseUrl = $baseUrl;
        $this->loadEnv();

        $this->clientId = trim((string)($_ENV['GOOGLE_CLIENT_ID'] ?? ''));
        $this->clientSecret = trim((string)($_ENV['GOOGLE_CLIENT_SECRET'] ?? ''));
        $this->redirectUri = trim((string)($_ENV['GOOGLE_REDIRECT_URI'] ?? ''));
    }

    // Connexion : crée la session utilisateur
    public static function login(array $user): void
    {
        // Stocke l'utilisateur en session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'pseudo' => $user['pseudo'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'],
            'bg' => $user['background'],
        ];
    }

    public static function logout(): void
    {
        // Vide la session utilisateur
        $_SESSION = [];
        session_destroy();
    }

    public static function isLogged(): bool
    {
        // Vérifie si un utilisateur est conencté
        return !empty($_SESSION['user']);
    }

    public static function current(): ?array
    {
        // Retourne l'utilisateur courant
        return $_SESSION['user'] ?? null;
    }

    public static function hash(string $password): string
    {
        // Hash bcrypt
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verify(string $password, string $hash): bool
    {
        // Vérifie un hash bcrypt
        return password_verify($password, $hash);
    }

    public function isConfigured(): bool
    {
        // Vérifie la config OAuth
        return $this->clientId !== '' && $this->clientSecret !== '';
    }

    public function getAuthorizationUrl(): string
    {
        // Construit l'URL OAuth Google
        $this->ensureSession();
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_google_state'] = $state;

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
            'access_type' => 'online',
        ]);
    }

    public function fetchUserFromCallback(string $code, string $state): array
    {
        // Echange du code OAuth et lecture du profil
        $this->ensureSession();
        $expectedState = (string)($_SESSION['oauth_google_state'] ?? '');
        unset($_SESSION['oauth_google_state']);

        if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
            throw new RuntimeException('Session Google invalide. Veuillez réessayez.');
        }

        if ($code === '') {
            throw new RuntimeException('Code Google manquant.');
        }

        $tokenData = $this->requestJson('https://oauth2.googleapis.com/token', 'POST', [
            'Content-Type: application/x-www-form-urlencoded',
        ], http_build_query([
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->getRedirectUri(),
            'grant_type' => 'authorization_code',
        ]));

        $accessToken = (string)($tokenData['access_token'] ?? '');
        if ($accessToken === '') {
            throw new RuntimeException('Impossible de récupèrer un token Google.');
        }

        $userInfo = $this->requestJson('https://openidconnect.googleapis.com/v1/userinfo', 'GET', [
            'Authorization: Bearer ' . $accessToken,
        ]);

        $email = trim((string)($userInfo['email'] ?? ''));
        $name = trim((string)($userInfo['name'] ?? ''));
        $avatar = trim((string)($userInfo['picture'] ?? ''));
        $googleId = trim((string)($userInfo['sub'] ?? ''));

        if ($email === '') {
            throw new RuntimeException('Le compte Google ne fournit pas d\'email.');
        }

        return [
            'id' => $googleId,
            'email' => $email,
            'name' => $name,
            'avatar' => $avatar,
        ];
    }

    // Construit l'URL de redirection Google OAuth
    public static function googleAuthUrl(): string
    {
        return (new self(self::extractBaseUrlFromAppUrl()))->getAuthorizationUrl();
    }

    // Echange le code OAuth contre les données utilisateurs
    public static function googleCallback(string $code, string $state): ?array
    {
        return (new self(self::extractBaseUrlFromAppUrl()))->fetchUserFromCallback($code, $state);
    }

    private function getRedirectUri(): string
    {
        // Utilise l'URI configurée ou reconstruit à partir de APP_URL
        if ($this->redirectUri !== '') {
            return $this->redirectUri;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $prefix = rtrim($this->baseUrl, '/');

        return $scheme . '://' . $host . $prefix . '/auth/google/callback';
    }

    private function ensureSession(): void
    {
        // Lance la session si besoin
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function loadEnv(): void
    {
        // Charge .env manuellement 
        $envPath = dirname(__DIR__, 2) . '/env';
        if (!is_readable($envPath)) {
            return;
        }

        $env = parse_ini_file($envPath, false, INI_SCANNER_RAW);
        if (!is_array($env)) {
            return;
        }

        foreach ($env as $key => $value) {
            $normalized = trim((string)$value, " \t\n\r\0\x0B\"'");
            $_ENV[$key] = $normalized;
            $_SERVER[$key] = $normalized;
        }
    }

    private function requestJson(string $url, string $method = 'GET', array  $headers = [], string $body = ''): array
    {
        // Requête HTTP simple
        if (!function_exists('curl_init')) {
            throw new RuntimeException('cURL est requis pour Google OAuth.');
        }

        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
        ];

        if ($body !== '') {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $options);

        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($raw === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Google OAuth indisponible: ' . $error);
        }
        curl_close($ch);

        return $this->decodeJsonResponse((string)$raw, $httpCode);
    }

    private function decodeJsonResponse(string $raw, int $httpCode): array
    {
        // Décodage JSON en gestion d'erreur HTTP
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException('Réponse Google invalide.');
        }

        if ($httpCode >= 400) {
            $message = (string)($data['error_description'] ?? $data['error'] ?? 'Erreur OAuth Google.');
            throw new RuntimeException($message);
        }

        return $data;
    }

    private static function extractBaseUrlFromAppUrl(): string
    {
        // Extrait uniquement le path de APP_URL
        $appUrl = trim((string)($_ENV['APP_URL'] ?? ''), '/');
        if ($appUrl === '') {
            return '';
        }

        $path = parse_url($appUrl, PHP_URL_PATH);
        return is_string($path) ? $path : '';
    }
}
