<?php

require_once ROOT . '/backend/models/userModel.php';
require_once ROOT . '/backend/models/passwordResetModel.php';
require_once ROOT . '/backend/services/AuthService.php';
require_once ROOT . '/backend/services/MailService.php';
require_once ROOT . '/backend/services/JsonResponseService.php';
require_once ROOT . '/backend/services/RendererService.php';
require_once ROOT . '/backend/middleware/CsrfMiddleware.php';

class AuthController
{
    private User $userModel;
    private PasswordReset $passwordResetModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->passwordResetModel = new PasswordReset();
    }

    public function register(): void
    {
        CsrfMiddleware::verify();

        // Récupération des champs
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        // Validation de base
        if (strlen($pseudo) < 3 || strlen($password) < 8 || $password !== $confirm || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            JsonResponse::jsonError('Données invalides');
            return;
        }
        if ($this->userModel->emailExists($email)) {
            JsonResponse::jsonError('Pseudo déjà pris.');
            return;
        }
        if ($this->userModel->pseudoExists($pseudo)) {
            JsonResponse::jsonError('Pseudo déjà pris.');
            return;
        }

        $id = $this->userModel->create([
            'pseudo' => $pseudo,
            'email' => $email,
            'password' => AuthService::hash($password),
            'google_id' => null,
        ]);
        // Connexion et email de bienvenue
        $user = $this->userModel->findById($id);
        AuthService::login($user);
        // MailService::sendWelcome($email, $pseudo);

        JsonResponse::jsonSuccess('Inscription réussie !', BASE_URL . 'profil');
    }

    public function login(): void
    {
        CsrfMiddleware::verify();

        $identifier = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $this->userModel->findByEmail($identifier)
            : $this->userModel->findByPseudo($identifier);

        // Migration des mots de passe en clair vers bcrypt
        if ($user && !empty($user['password']) && !str_starts_with($user['password'], '$2y$') && hash_equals($user['password'], $password)) {
            $newHash = AuthService::hash($password);
            $this->userModel->update((int)$user['id'], ['password' => $newHash]);
            $user['password'] = $newHash;
        }

        // Validation du mot de passe
        if (!$user || !AuthService::verify($password, $user['password'])) {
            JsonResponse::jsonError('Email ou mot de passe incorrect.');
            return;
        }

        AuthService::login($user);
        $redirect = $user['role'] === 'admin' ? BASE_URL . 'admin' : BASE_URL . 'profil';
        JsonResponse::jsonSuccess('conenxion réussie !', $redirect);
    }

    public function forgotPassword(): void
    {
        CsrfMiddleware::verify();

        // Validation de l'email
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            JsonResponse::jsonError('Email invalide');
            return;
        }

        // Réponse générique pour éviter l'énumération des comptes
        $genericMessage = 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.';
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            JsonResponse::jsonSuccess($genericMessage);
            return;
        }

        $token = bin2hex(random_bytes(32));
        $this->passwordResetModel->create(
            (int)$user['id'],
            hash('sha256', $token),
            date('Y-m-d H:i:s', time() + 86400)
        );

        // Envoi de l'email de reset
        $resetLink = BASE_URL . 'auth/reinitialiser-mot-de-passe?token=' . urlencode($token);
        if (!MailService::sendPasswordReset($email, (string)($user['pseudo'] ?? 'voyageur'), $resetLink)) {
            JsonResponse::jsonError('L\'email de réinitialisation n\'a pas pu être envoyeé. Vérifie la configuration SMTP.');
            return;
        }
        JsonResponse::jsonSuccess($genericMessage);
    }

    public function showResetPassword(): void
    {
        // Vérification du token pour afficher le bon message
        $token = trim($_GET['token'] ?? '');
        $reset = $token !== '' ? $this->passwordResetModel->findValidByToken($token) : false;

        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'title' => 'Réinitialisation du mot de passe - Traveling',
            'token' => $token,
            'validToken' => (bool)$reset,
            'message' => $token === '' ? 'Lien manquant.' : ($reset ? '' : 'Lien ivalide ou expiré.'),
        ]);
        echo $renderer->render('auth/reset-password');
    }

    public function resetPassword(): void
    {
        CsrfMiddleware::verify();

        // Validation des champs
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if ($token === '' || strlen($password) < 8 || $password !== $confirm) {
            JsonResponse::jsonError('Données invalides.');
            return;
        }

        $reset = $this->passwordResetModel->findValidByToken($token);
        if (!$reset) {
            JsonResponse::jsonError('Lien invalide ou expiré.');
            return;
        }

        // Mise à jour du mot de passe et invalidation du token
        $this->userModel->update((int)$reset['user_id'], ['password' => AuthService::hash($password)]);
        $this->passwordResetModel->markUsed((int)$reset['id']);

        JsonResponse::jsonSuccess('Mot de passe mis à jour !', BASE_URL . 'login?login=1');
    }

    public function logout(): void
    {
        // Déconnexion simple
        AuthService::logout();
        header('Location: ' . BASE_URL);
        exit;
    }

    public function googleRedirect(): void
    {
        // Redirection vers Google OAuth
        header('Location: ' . AuthService::googleAuthUrl());
        exit;
    }

    public function googleCallback(): void
    {
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';

        // Echange du code OAuth
        try {
            $googleUser = AuthService::googleCallback($code, $state);
        } catch (RuntimeException $e) {
            header('Location: ' . BASE_URL . '?error=google');
            exit;
        }

        // Rechercher par google_id ou email
        $user = $this->userModel->findByGoogleId($googleUser['id']) ?: $this->userModel->findByEmail($googleUser['email']);

        // Création du compte si besoin
        if (!$user) {
            $pseudo = preg_replace('/[^a-zA-Z0-9_]/', '', $googleUser['name']) ?: 'user' . rand(1000, 9999);
            while ($this->userModel->pseudoExists($pseudo)) $pseudo .= rand(1, 9);

            $id = $this->userModel->create([
                'pseudo' => $pseudo,
                'email' => $googleUser['email'],
                'password' => AuthService::hash(bin2hex(random_bytes(16))),
                'google_id' => $googleUser['id'],
            ]);
            $user = $this->userModel->findById($id);
        }

        // Lie le compte si pas encore associé
        if (!$user['google_id']) {
            $this->userModel->update($user['id'], ['google_id' => $googleUser['id']]);
        }

        // Connexion et redirection
        AuthService::login($user);
        header('Location: ' . BASE_URL . 'profil');
        exit;
    }
}
