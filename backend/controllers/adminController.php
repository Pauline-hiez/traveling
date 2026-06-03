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
}
