<?php

require_once ROOT . '/backend/models/articleModel.php';
require_once ROOT . '/backend/models/commentModel.php';
require_once ROOT . '/backend/models/commentLikeModel.php';
require_once ROOT . '/backend/models/filmModel.php';
require_once ROOT . '/backend/models/lieuModel.php';
require_once ROOT . '/backend/models/likeModel.php';
require_once ROOT . '/backend/models/favoriteModel.php';
require_once ROOT . '/backend/middleware/AuthMiddleware.php';
require_once ROOT . '/backend/middleware/CsrfMiddleware.php';
require_once ROOT . '/backend/services/TmdbService.php';
require_once ROOT . '/backend/services/RendererService.php';

class ArticleController
{
    private Article $articleModel;

    public function __construct()
    {
        $this->articleModel = new Article();
    }

    public function liste(): void
    {
        // Publie les articles programmés
        $this->articleModel->publishDue();

        // Lecture des filtres de la liste
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $category = $_GET['category'] ?? '';
        $sort = $_GET['sort'] ?? 'date';

        // Récupération des articles et pagination
        $articles = $this->articleModel->getPaginated($page, $search, $category, $sort);
        $total = $this->articleModel->countFiltered($search, $category);
        $pages = (int) ceil($total / ARTICLE_PER_PAGE);

        // Envoi des données à la vue
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'search' => $search,
            'sort' => $sort,
            'category' => $category,
            'articles' => $articles,
            'pages' => $pages,
            'page' => $page,
            'title' => 'Articles - Traveling',
        ]);
        echo $renderer->render('article/liste');
    }

    public function show(string $id): void
    {
        // Article introuvable
        $article = $this->articleModel->getById((int)$id);
        if (!$article) {
            http_response_code(404);
            $renderer = new \Services\Renderer();
            echo $renderer->render('errors/404');
            return;
        }

        // Chargement des dépendances
        $commentModel = new Comment();
        $commentLikeModel = new CommentLike();
        $filmModel = new Film();
        $lieuModel = new Lieu();

        // Récupère les commentaires et ajoute les stats de likes
        $comments = $commentModel->getByArticle((int)$id);
        foreach ($comments as &$comment) {
            $comment['likes_count'] = $commentLikeModel->countForComment((int)$comment['id']);
            $comment['liked_by_user'] = !empty($_SESSION['user']['id'])
                ? $commentLikeModel->exists((int)$_SESSION['user']['id'], (int)$comment['id'])
                : false;
        }
        unset($comment);

        $tmdbIds = $filmModel->getTmdbIdsByArticle((int)$id);
        $lieux = $lieuModel->getByArticle((int)$id);

        $userLiked = false;
        $userFavorited = false;
        if (!empty($_SESSION['user']['id'])) {
            $userLiked = (new Like())->exists((int)$_SESSION['user']['id'], (int)$id);
            $userFavorited = (new Favorite())->exists((int)$_SESSION['user']['id'], (int)$id);
        }

        // Charge les films associés via TMDB
        $tmdbFilms = [];
        foreach ($tmdbIds as $tmdbId) {
            $film = TmdbService::getDetails((int)$tmdbId);
            if ($film) $tmdbFilms[] = $film;
        }

        // Envoi des données à la vue
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'article' => $article,
            'comments' => $comments,
            'tmdbFilms' => $tmdbFilms,
            'lieux' => $lieux,
            'userLiked' => $userLiked,
            'userFavorited' => $userFavorited,
            'title' => ($article['title'] ?? 'Article') . ' - Traveling',
        ]);

        $renderer->addScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
        $renderer->addScript(ASSETS_URL . 'js/article.js');

        echo $renderer->render('article/article');
    }

    public function like(string $id): void
    {
        AuthMiddleware::require();
        CsrfMiddleware::verify();

        // Like/unlike en une seule action
        $liked = (new Like())->toggle((int)$_SESSION['user']['id'], (int)$id);

        header('Content-Type: application/json');
        echo json_encode(['liked' => $liked]);
    }

    public function autocomplete(): void
    {
        // Autocomplete public des titres d'articles (inclut image si disponible)
        $query = trim($_POST['query'] ?? '');
        $items = $query !== '' ? $this->articleModel->searchTitles($query, 6) : [];

        $results = array_map(function ($it) {
            $poster = null;
            if (!empty($it['image'])) {
                $img = $it['image'];
                if (preg_match('#^(https?://|/)#', $img)) {
                    $poster = $img;
                } elseif (strpos(ltrim($img, '/'), 'frontend/assets') === 0) {
                    $poster = '/' . ltrim($img, '/');
                } else {
                    $poster = ASSETS_URL . ltrim($img, '/');
                }
            }

            return [
                'id' => $it['id'],
                'label' => $it['label'],
                'poster' => $poster,
            ];
        }, $items);

        header('Content-Type: application/json');
        echo json_encode(['results' => $results]);
    }
}
