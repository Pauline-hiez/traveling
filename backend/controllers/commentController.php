<?php

require_once ROOT . '/backend/models/commentModel.php';
require_once ROOT . '/backend/models/commentLikeModel.php';
require_once ROOT . '/backend/services/JsonResponseService.php';
require_once ROOT . '/backend/middleware/AuthMiddleware.php';
require_once ROOT . '/backend/middleware/CsrfMiddleware.php';

class CommentController
{
    private Comment $commentModel;
    private CommentLike $commentLikeModel;

    public function __construct()
    {
        $this->commentModel = new Comment();
        $this->commentLikeModel = new CommentLike();
    }

    public function create(string $articleId): void
    {
        AuthMiddleware::require();
        CsrfMiddleware::verify();

        // Validation du contenu
        $content = trim($_POST['content'] ?? '');
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;

        if ($content === '') {
            JsonResponse::jsonError('Le commentaire ne peut pas être vide.');
            return;
        }

        // Création d'un commentaire
        $this->commentModel->create((int)$articleId, (int)$_SESSION['user']['id'], $content, $parentId);
        JsonResponse::jsonSuccess('Commentaire publié.');
    }

    // Signalement de commentaire
    public function report(string $commentId): void
    {
        AuthMiddleware::require();
        CsrfMiddleware::verify();

        // Motif obligatoire
        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') {
            JsonResponse::jsonError('Le motif est obligatoire.');
            return;
        }

        // Enregistre le signalement
        $this->commentModel->report((int)$_SESSION['user']['id'], (int)$commentId, $reason);
        JsonResponse::jsonSuccess('Signalement envoyé.');
    }

    public function like(string $commentId): void
    {
        AuthMiddleware::require();
        CsrfMiddleware::verify();

        // Like/unlike du commentaire
        $liked = $this->commentLikeModel->toggle((int)$_SESSION['user']['id'], (int)$commentId);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'liked' => $liked]);
    }
}
