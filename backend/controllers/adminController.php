<?php

require_once ROOT . '/backend/models/userModel.php';
require_once ROOT . '/backend/models/articleModel.php';
require_once ROOT . '/backend/models/commentModel.php';
require_once ROOT . '/backend/models/likeModel.php';
require_once ROOT . '/backend/models/filmModel.php';
require_once ROOT . '/backend/models/lieuModel.php';
require_once ROOT . '/backend/middleware/RoleMiddleware.php';
require_once ROOT . '/backend/middleware/AuthMiddleware.php';
require_once ROOT . '/backend/middleware/CsrfMiddleware.php';
require_once ROOT . '/backend/services/TmdbService.php';
require_once ROOT . '/backend/services/MapService.php';
require_once ROOT . '/backend/services/MailService.php';
require_once ROOT . '/backend/services/JsonResponseService.php';
require_once ROOT . '/backend/services/FileUploaderService.php';
require_once ROOT . '/backend/config/database.php';

class AdminController
{
    private User $userModel;
    private Article $articleModel;
    private mixed $commentModel = null;

    public function __construct()
    {
        $this->userModel = new User();
        $this->articleModel = new Article();
        $commentClass = 'Comment';
        if (class_exists($commentClass)) {
            $this->commentModel = new $commentClass();
        }
    }

    private function paginateArray(array $items, int $page): array
    {
        // Pagination simple en mémoire
        $perPage = ADMIN_PER_PAGE;
        $total = count($items);
        $pages = (int) ceil($total / $perPage);

        return [
            'page' => $page,
            'pages' => $pages,
            'items' => array_slice($items, ($page - 1) * $perPage, $perPage),
        ];
    }

    public function dashboard(): void
    {
        RoleMiddleware::require('admin');
        // Statistiques globales du dashboard
        $stats = $this->buildDashboardStats();
        require_once ROOT . '/backend/services/RendererService.php';

        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'user' => $_SESSION['user'] ?? null,
            'stats' => $stats,
            'adminSection' => 'dashboard',
            'adminSidebarStats' => $stats['nav'],
            'title' => 'Dashboard - Admin Traveling',
        ]);
        $renderer->addScript(ASSETS_URL . 'js/admin.js');
        $renderer->addScript(ASSETS_URL . 'js/admin-dashboard.js');
        echo $renderer->render('admin/dashboard');
    }

    public function users(): void
    {
        RoleMiddleware::require('admin');
        // Liste paginée des utilisateurs
        $all = $this->userModel->getAll();
        $pagination = $this->paginateArray($all, max(1, (int)($_GET['page'] ?? 1)));

        require_once ROOT . '/backend/services/RendererService.php';

        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'users' => $pagination['items'],
            'page' => $pagination['page'],
            'pages' => $pagination['pages'],
            'adminSection' => 'users',
            'adminSidebarStats' => $this->buildAdminNavStats(),
            'title' => 'Utilisateurs - Admin Traveling',
        ]);
        $renderer->addScript(ASSETS_URL . 'js/admin-users.js');
        echo $renderer->render('admin/users');
    }

    public function changeRole(string $id): void
    {
        RoleMiddleware::require('admin');
        CsrfMiddleware::verify();

        // Validation et mise à jour du rôle
        $role = $_POST['role'] ?? '';
        if (!in_array($role, ['user', 'modérateur', 'admin'], true)) {
            JsonResponse::jsonError('Rôle invalide.');
            return;
        }
        $this->userModel->update((int)$id, ['role' => $role]);
        JsonResponse::jsonSuccess('Rôle mis à jour.');
    }

    public function deleteUser(string $id): void
    {
        RoleMiddleware::require('admin');
        CsrfMiddleware::verify();

        // Interdit la suppression de son propre compte (Admin)
        if ((int)$id === (int)$_SESSION['user']['id']) {
            JsonResponse::jsonError('Impossible de supprimer votre propre compte.');
            return;
        }
        $this->userModel->delete((int)$id);
        JsonResponse::jsonSuccess('Utilisateur supprimé.');
    }

    public function comments(): void
    {
        if ($this->commentModel === null) {
            JsonResponse::jsonError('Module commentaire indisponible.');
            return;
        }

        // Liste des signalements
        $all = $this->commentModel->getReported();
        $pagination = $this->paginateArray($all, max(1, (int)($_GET['page'] ?? 1)));

        require_once ROOT . '/backend/services/RendererService.php';

        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'reports' => $pagination['items'],
            'page' => $pagination['page'],
            'pages' => $pagination['pages'],
            'adminSection' => 'comments',
            'adminSidebarStats' => $this->buildAdminNavStats(),
            'title' => 'Signalements - Admin Traveling',
        ]);
        $renderer->addScript(ASSETS_URL . 'js/admin-comments.js');
        echo $renderer->render('admin/comments');
    }

    public function deleteComment(string $id): void
    {
        RoleMiddleware::require('admin');
        CsrfMiddleware::verify();

        if ($this->commentModel === null) {
            JsonResponse::jsonError('Module commentaire indisponible.');
            return;
        }

        // Suppression du commentaire
        $this->commentModel->delete((int)$id);
        JsonResponse::jsonSuccess('Commentaire supprimé.');
    }

    public function warnUser(string $commentId): void
    {
        RoleMiddleware::require('admin');
        CsrfMiddleware::verify();

        // Récupère l'auteur et le contenu du commentaire
        $reason = trim($_POST['reason'] ?? '');
        $stmt = Database::getInstance()->prepare("SELECT u.email, c.content AS comment_content FROM comments c JOIN users u ON u.id = c.user_id WHERE c.id = :id");
        $stmt->execute([':id' => (int)$commentId]);
        $data = $stmt->fetch();

        // Envoi de l'avertissement par email
        if ($data && $reason) {
            MailService::sendWarning($data['email'], $data['pseudo'], $reason, (string)($data['comment_content'] ?? ''));
        }

        // Marque les signalements comme traités
        if ($this->commentModel !== null) {
            $this->commentModel->markReportsHandled((int)$commentId);
        }
        JsonResponse::jsonSuccess('Avertissement envoyé.');
    }

    public function articles(): void
    {
        RoleMiddleware::require('admin');
        // Liste paginée des articles
        $all = $this->articleModel->getAll();
        $pagination = $this->paginateArray($all, max(1, (int)($_GET['page'] ?? 1)));

        require_once ROOT . '/backend/services/RendererService.php';

        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'articles' => $pagination['items'],
            'page' => $pagination['page'],
            'pages' => $pagination['pages'],
            'adminSection' => 'articles',
            'adminSideBarStats' => $this->buildAdminNavStats(),
            'title' => 'Articles - Admin Traveling',
        ]);
        $renderer->addScript(ASSETS_URL . 'js/admin.js');
        $renderer->addScript(ASSETS_URL . 'js/admin-articles.js');
        echo $renderer->render('admin/articles');
    }

    private function buildDashboardStats(): void
    {
        // Rassemble les stats du dashboard admin
        $allArticles = $this->articleModel->getAll();
        $allUsers = $this->userModel->getAll();
        $allReports = $this->commentModel ? $this->commentModel->getReported() : [];

        $articlesTotal = count($allArticles);
        $articlesPublished = 0;
        $articlesDraft = 0;

        $byCategory = [
            'cinema' => 0,
            'voyage' => 0,
        ];

        foreach ($allArticles as $article) {
            if (!empty($articlr['published'])) {
                $articlesPublished++;
            } else {
                $articlesDraft++;
            }
            $category = $article['category'] ?? '';
            if (array_key_exists($category, $byCategory)) {
                $byCategory[$category]++;
            }
        }

        $usersByRole = [
            'user' => 0,
            'moderateur' => 0,
            'admin' => 0,
        ];

        foreach ($allUsers as $user) {
            $role = $user['role'] ?? '';
            if (array_key_exists($role, $usersByRole)) {
                $usersByRole[$role]++;
            }
        }

        $reportsPending = 0;
        foreach ($allReports as $report) {
            if (empty($report['treated_at'])) {
                $reportsPending++;
            }
        }
    }
}
