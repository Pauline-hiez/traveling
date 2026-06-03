<?php

require_once ROOT . '/backend/models/userModel.php';
require_once ROOT . '/backend/models/articleModel.php';
require_once ROOT . '/backend/services/AuthService.php';
require_once ROOT . '/backend/services/JsonResponseService.php';
// require_once ROOT . '/backend/services/FileUploaderService.php';
require_once ROOT . '/backend/middleware/AuthMiddleware.php';
require_once ROOT . '/backend/middleware/CsrfMiddleware.php';

class UserController
{
    private User $userModel;
    private Article $articleModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->articleModel = new Article();
    }

    private function persistPositionOffsets(int $userId, string $prefix): void
    {
        // Enregistre la position de recadrage si fournie
        $posX = isset($_POST[$prefix . '_pos_x']) ? (int)$_POST[$prefix . '_pos_x'] : null;
        $posY = isset($_POST[$prefix . '_pos_y']) ? (int)$_POST[$prefix . '_pos_y'] : null;

        if ($posX !== null || $posY !== null) {
            $updateData = [];
            if ($posX !== null) {
                $updateData[$prefix . '_pos_x'] = $posX;
            }
            if ($posY !== null) {
                $updateData[$prefix . '_pos_y'] = $posY;
            }
            $this->userModel->update($userId, $updateData);
        }
    }

    public function index(): void
    {
        AuthMiddleware::require();
        // Chargement des infos du profil
        $userId = (int)$_SESSION['user']['id'];
        $profileUser = $this->userModel->findById($userId) ?: $_SESSION['user'];
        $favorites = $this->articleModel->getUserFavorites($userId, FAVORITES_PREVIEW);
        $allFavorites = $this->articleModel->getUserFavorites($userId);
        $favoriteCount = $this->userModel->countFavorites($userId);
        $likeCount = $this->userModel->countLikes($userId);
        $commentCount = $this->userModel->countComments($userId);
        $memberSince = !empty($profileUser['created_at']) ? date('d/m/Y', strtotime($profileUser['created_at'])) : 'Date inconnue';
        // Historique utilisateur
        $history = [];
        if (file_exists(ROOT . '/backend/models/historyModel')) {
            require_once ROOT . '/backend/models/historyModel.php';
            $historyModel = new History();
            $historyModel->getByUser($userId, HISTORY_PREVIEW);
        }

        // Articles publiés de l'auteur (modérateur ou admin)
        $myArticles = [];
        if (!empty($profileUser['role']) && in_array($profileUser['role'], ['moderateur', 'admin'], true)) {
            $myArticles = $this->articleModel->getByAuthor((int)$profileUser['id']);
        }

        require_once ROOT . '/backend/services/RendererService.php';

        // Envoi des données à la vue
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'profileUser' => $profileUser,
            'favorites' => $favorites,
            'allFavorites' => $allFavorites,
            'favoriteCount' => $favoriteCount,
            'likeCount' => $likeCount,
            'commentCount' => $commentCount,
            'memberSince' => $memberSince,
            'history' => $history,
            'myArticles' => $myArticles,
            'title' => 'Mon profil - Traveling',
        ]);

        $renderer->addScript(ASSETS_URL . 'js/profile.js');
        if (!empty($myArticles)) {
            $renderer->addScript(ASSETS_URL . 'js/admin.js');
        }
        echo $renderer->render('user/profil');
    }

    public function updateEmail(): void
    {
        AuthMiddleware::require();
        CsrfMiddleware::verify();

        // Validation de l'email
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            JsonResponse::jsonError('Email invalide.');
            return;
        }
        if ($this->userModel->emailExists($email)) {
            JsonResponse::jsonError('Email déjà utilisé.');
            return;
        }

        $this->userModel->update((int)$_SESSION['user']['id'], ['email' => $email]);
        $_SESSION['user']['email'] = $email;
        JsonResponse::jsonSuccess('Email mis à jour.');
    }

    public function updatePseudo(): void
    {
        AuthMiddleware::require();
        CsrfMiddleware::verify();

        // Validation du pseudo
        $pseudo = trim($_POST['pseudo'] ?? '');
        if (strlen($pseudo) < 3) {
            JsonResponse::jsonError('Pseudo trop court (3 car. min.)');
            return;
        }

        if ($this->userModel->pseudoExists($pseudo)) {
            JsonResponse::jsonError('Pseudo déjà pris.');
            return;
        }

        $this->userModel->update((int)$_SESSION['user']['id'], ['pseudo' => $pseudo]);
        $_SESSION['user']['pseudo'] = $pseudo;
        JsonResponse::jsonSuccess('Pseudo mis à jour');
    }

    public function updatePassword(): void
    {
        AuthMiddleware::require();
        CsrfMiddleware::verify();

        // Vérification du mot de passe actuel
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $user = $this->userModel->findById((int)$_SESSION['user']['id']);

        if (!AuthService::verify($current, $user['password'])) {
            JsonResponse::jsonError('Mot de passe actuel incorrect.');
            return;
        }
        if (strlen($new) < 8 || $new !== $confirm) {
            JsonResponse::jsonError('Nouveau mot de passe invalide.');
            return;
        }
        $this->userModel->update((int)$_SESSION['user']['id'], ['password' => AuthService::hash($new)]);
        JsonResponse::jsonSuccess('Mot de passe mis à jour.');
    }

    // public function updateAvatar(): void {
    //     AuthMiddleware::require();
    //     CsrfMiddleware::verify();

    //     // Upload et sauvegarde de l'avatar
    //     $path = FileUploader::upload('avatar', 'profil/avatar');
    //     if (!$path) {
    //         JsonResponse::jsonError('Erreur upload (JPG/PNG/WEBP requis).');
    //         return;
    //     }

    //     $userId = (int)$_SESSION['user']['id'];
    //     $this->userModel->update($userId, ['avatar' => $path]);
    //     $_SESSION['user']['avatar'] = $path;
    //     $this->persistPositionOffsets($userId, 'avatar');

    //     JsonResponse::jsonSuccess('Avatar mis à jour.', $path);
    // }

    // public function updateBackgrond(): void {
    //     AuthMiddleware::require();
    //     CsrfMiddleware::verify();

    //     // Upload et sauvegarde du fond
    //     $path = FileUploader::upload('background', 'profil/bg');
    //     if (!$path) {
    //         JsonResponse::jsonError('Erreur upload (JPG/PNG/WEBP requis)');
    //         return;
    //     }

    //     $userId = (int)$_SESSION['user']['id'];
    //     $this->userModel->update($userId, ['background' => $path]);
    //     $_SESSION['user']['bg'] = $path;
    //     $this->persistPositionOffsets($userId, 'background');

    //     JsonResponse::jsonSuccess('Fond mis à jour.', $path);
    // }

    public function delete(): void
    {
        AuthMiddleware::require();
        CsrfMiddleware::verify();

        // Suppression du compte
        $this->userModel->delete((int)$_SESSION['user']['id']);
        AuthService::logout();

        header('Content-Tupe: application/json');
        echo json_encode(['success' => true, 'redirect' => BASE_URL]);
    }
}
