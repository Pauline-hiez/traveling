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

    private function buildDashboardStats(): array
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

        return [
            'articles' => [
                'total' => $articlesTotal,
                'published' => $articlesPublished,
                'draft' => $articlesDraft,
                'cinema' => $byCategory['cinema'],
                'voyage' => $byCategory['voyage'],
            ],
            'users' => [
                'total' => count($allUsers),
                'admin' => $usersByRole['admin'],
                'moderateur' => $usersByRole['moderateur'],
                'user' => $usersByRole['user'],
            ],
            'reports' => [
                'total' => count($allReports),
                'pending' => $reportsPending,
                'handled' => count($allReports) - $reportsPending,
            ],
            'nav' => [
                'articles' => $articlesTotal,
                'users' => count($allUsers),
                'reports_pending' => $reportsPending,
            ],
        ];
    }

    private function buildAdminNavStats(): array
    {
        // Stats pour la sidebar 
        $allArticles = $this->articleModel->getAll();
        $allUsers = $this->userModel->getAll();
        $allReports = $this->commentModel ? $this->commentModel->getReported() : [];

        $reportsPending = 0;
        foreach ($allReports as $report) {
            if (empty($report['treated_at'])) {
                $reportsPending++;
            }
        }

        return [
            'articles' => count($allArticles),
            'users' => count($allUsers),
            'reports_pending' => $reportsPending,
        ];
    }

    public function articleData(string $id): void
    {
        RoleMiddleware::require('admin');
        CsrfMiddleware::verify();

        // Charge les données de l'article
        $article =  $this->articleModel->getAdminById((int)$id);
        if (!$article) {
            JsonResponse::jsonError('Article introuvable.');
            return;
        }

        $filmModel = new Film();
        $lieuModel = new Lieu();

        JsonResponse::jsonSuccess('Article chargé.', [
            'article' => $article,
            'tmdb_ids' => $filmModel->getTmdbIdsByArticle((int)$id),
            'lieu_ids' => array_map(static fn($lieu) => (string)$lieu['id'], $lieuModel->getByArticle((int)$id)),
        ]);
    }

    public function publishArticle(): void
    {
        RoleMiddleware::require('admin', 'moderateur');
        CsrfMiddleware::verify();

        // Validation du titre
        $title = trim($_POST['title'] ?? '');
        if (!$title) {
            JsonResponse::jsonError('Le titre est obligatoire.');
            return;
        }

        $data = [
            'author_id' => (int)$_SESSION['user']['id'],
            'title' => $title,
            'subtitle' => trim($_POST['subtitle'] ?? '') ?: null,
            'content' => trim($_POST['content'] ?? ''),
            'quote' => trim($_POST['quote'] ?? '') ?: null,
            'anecdote' => trim($_POST['anecdote'] ?? '') ?: null,
            'img_cover' => FileUploader::upload('img_cover', 'articles/covers'),
            'img_illus' => FileUploader::upload('img_illus', 'articles/illustrations'),
            'img_caption' => trim($_POST['img_caption'] ?? '') ?: null,
            'img_bg' => FileUploader::upload('img_bg', 'articles/hero'),
            'category' => in_array($_POST['category'] ?? '', ['cinema', 'voyage']) ? $_POST['category'] : 'cinema',
        ];

        // Gestion de la date de publication
        $publishAtRaw = trim($_POST['publish_at'] ?? '');
        if ($publishAtRaw !== '') {
            $data['published'] = 2;
            $data['publish_at'] = str_replace('T', ' ', $publishAtRaw) . ':80';
        } else {
            $data['published'] = 1;
            $data['publish_at'] = null;
        }

        // Création de l'article
        $articleId = $this->articleModel->create($data);
        $sliderImages = [];
        for ($i = 1; $i <= 5; $i++) {
            $field = 'slider_img_' . $i;
            $path = FileUploader::upload($field, 'articles/slider');
            if (!$path) {
                continue;
            }
            $title = trim($_POST['slider_title_' . $i] ?? '') ?: null;
            $text = trim($_POST['slider_text_' . $i] ?? '') ?: null;
            $sliderImages[] = ['path' => $path, 'title' => $title, 'text' => $text];
        }

        $tmdbIds = array_filter(array_map('intval', explode(',', $_POST['tmdb_ids'] ?? '')));
        $lieuIds = array_filter(array_map('intval', explode(',', $_POST['lieu_ids'] ?? '')));

        $lieuDetails = [
            'description' => trim($_POST['lieu_description'] ?? '') ?: null,
            'author_tips' => trim($_POST['lieu_tips'] ?? '') ?: null,
            'author_suggestions' => trim($_POST['lieu_tips'] ?? '') ?: null,
        ];

        // Liaison films/lieux
        if ($tmdbIds) $this->articleModel->syncFilms($articleId, array_values($tmdbIds));
        if ($lieuIds) $this->articleModel->syncLieux($articleId, array_values($lieuIds));

        if ($sliderImages) {
            $this->articleModel->replaceSliderImages($articleId, $sliderImages);
        }

        if ($lieuIds && array_filter($lieuDetails)) {
            $lieuModel = new Lieu();
            foreach ($lieuIds as $lid) {
                $lieuModel->updateDetails((int)$lid, $lieuDetails);
            }
        }

        // Sauvegarde l'image du lieu fournie
        $tournagePath = FileUploader::upload('lieu_img', 'lieux/tournage');
        if ($tournagePath && $lieuIds) {
            $lieuModel = new Lieu();
            foreach ($lieuIds as $lid) {
                $lieuModel->setImage((int)$lid, $tournagePath);
            }
        }

        // Sauvegarde le fond du lieu fourni
        $lieuBgPath = FileUploader::upload('lieu_bg', 'lieux/bg');
        if ($lieuBgPath && $lieuIds) {
            $lieuModel = $lieuModel ?? new Lieu();
            foreach ($lieuIds as $lid) {
                $lieuModel->setBackground((int)$lid, $lieuBgPath);
            }
        }

        // Historique
        $historyMsg = "A publié l'article \"" . $title . "\"";
        if (file_exists(ROOT . '/backend/models/historyModel.php')) {
            require_once ROOT . 'backend/models/historyModel.php';
            $history = new History();
            $history->add((int)$_SESSION['user']['id'], $historyMsg);
        }

        // Redirection selon le rôle
        $redirect = (($_SESSION['user']['role'] ?? '') === 'moderateur') ? (BASE_URL . 'profil') : (BASE_URL . 'admin/articles');
        JsonResponse::jsonSuccess('Article publié.', $redirect);
    }

    public function editArticle(string $id): void
    {
        RoleMiddleware::require('admin');
        CsrfMiddleware::verify();

        // Champs éditables
        $data = array_filter([
            'title' => trim($_POST['title'] ?? ''),
            'subtitle' => trim($_POST['subtitle'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'quote' => trim($_POST['quote'] ?? ''),
            'anecdote' => trim($_POST['anecdote'] ?? ''),
            'img_caption' => trim($_POST['img_caption'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
        ]);

        // Programmation via publish_at
        if (isset($_POST['publish_at'])) {
            $publishAtRaw = trim($_POST['publish_at']);
            if ($publishAtRaw !== '') {
                $data['published'] = 2;
                $data['publish_at'] = str_replace('T', ' ', $publishAtRaw) . ':00';
            } else {
                $data['published'] = 1;
                $data['publish_at'] = null;
            }
        }

        // Upload des images
        foreach (['img_cover' => 'articles/covers', 'img_illus' => 'articles/illustrations', 'img_bg' => 'articles/hero'] as $field => $sub) {
            $path = FileUploader::upload($field, $sub);
            if ($path) $data[$field] = $path;
        }

        $sliderImages = [];
        for ($i = 1; $i <= 5; $i++) {
            $field = 'slider_img_' . $i;
            $path = FileUploader::upload($field, 'articles/slider');
            if (!$path) {
                continue;
            }
            $title = trim($_POST['slider_title_' . $i] ?? '') ?: null;
            $text = trim($_POST['slider_text_' . $i] ?? '') ?: null;
            $sliderImages[] = ['path' => $path, 'title' => $title, 'text' => $text];
        }

        // Mise à jour
        $this->articleModel->update((int)$id, $data);

        // Mise à jour des liaisons
        $tmdbIds = array_filter(array_map('intval', explode(',', $_POST['tmdb_ids'] ?? '')));
        $lieuIds = array_filter(array_map('intval', explode(',', $_POST['lieu_ids'] ?? '')));
        $this->articleModel->syncFilms((int)$id, array_values($tmdbIds));
        $this->articleModel->syncLieux((int)$id, array_values($lieuIds));

        if ($sliderImages) {
            $this->articleModel->replaceSliderImages((int)$id, $sliderImages);
        }

        $lieuDetails = [
            'description' => trim($_POST['lieu_description'] ?? '') ?: null,
            'author_tips' => trim($_POST['lieu_tips'] ?? '') ?: null,
            'author_suggestions' => trim($_POST['lieu_suggestions'] ?? '') ?: null,
        ];

        if ($lieuIds && array_filter($lieuDetails)) {
            $lieuModel = new Lieu();
            foreach ($lieuIds as $lid) {
                $lieuModel->updateDetails((int)$lid, $lieuDetails);
            }
        }

        // Sauvegarde l'image du lieu fournie
        $tournagePath = FileUploader::upload('lieu_img', 'lieux/tournage');
        if ($tournagePath && $lieuIds) {
            $lieuModel = new Lieu();
            foreach ($lieuIds as $lid) {
                $lieuModel->setImage((int)$lid, $tournagePath);
            }
        }

        $lieuBgPath = FileUploader::upload('lieu_bg', 'lieux/bg');
        if ($lieuBgPath && $lieuIds) {
            $lieuModel = $lieuModel ?? new Lieu();
            foreach ($lieuIds as $lid) {
                $lieuModel->setBackground((int)$lid, $lieuBgPath);
            }
        }

        JsonResponse::jsonSuccess('Article mis à jour.');
    }

    public function deleteArticle(string $id): void
    {
        RoleMiddleware::require('admin');
        CsrfMiddleware::verify();

        // Suppression de l'article
        $this->articleModel->delete((int)$id);
        JsonResponse::jsonSuccess('Article supprimé.');
    }

    public function tmdbSearch(): void
    {
        RoleMiddleware::require('admin', 'moderateur');

        // Recherche TMDB
        $query = trim($_POST['query'] ?? '');
        $films = $query ? TmdbService::search($query) : [];
        $filtered = array_filter($films, fn($f) => in_array($f['media_type'] ?? 'movie', ['movie', 'tv']));
        $result = array_map(fn($f) => [
            'id' => $f['id'],
            'title' => $f['title'] ?? $f['name'] ?? '',
            'release_date' => $f['release_date'] ?? $f['first_air_date'] ?? '',
            'poster' => TmdbService::imgUrl($f['poster_path'] ?? null, 'w92'),
            'media_type' => $f['media_type'] ?? 'movie',
        ], array_slice(array_values($filtered), 0, 8));

        header('Content-Type: application/json');
        echo json_encode(['films' => $result]);
    }

    public function lieuSearch(): void
    {
        RoleMiddleware::require('admin', 'moderateur');

        // Recherche OSM
        $query = trim($_POST['query'] ?? '');
        $osm = $query ? MapService::search($query) : [];
        $lieuClass = 'Lieu';
        if (!class_exists($lieuClass)) {
            JsonResponse::jsonError('Module lieu indisponible.');
            return;
        }

        $lieuModel = new $lieuClass();
        $result = [];

        foreach (array_slice($osm, 0, 5) as $item) {
            $name = $item['display_name'] ?? 'Inconnu';
            $country = $item['address']['country'] ?? null;
            $lat = isset($item['lat']) ? (float)$item['lat'] : null;
            $lng = isset($item['lon']) ? (float)$item['lon'] : null;

            // Cherche ou crée le lieu en BDD
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id FROM lieux WHERE name = :name LIMIT 1");
            $stmt->execute([':name' => $name]);
            $row = $stmt->fetch();
            $lieuId = $row ? (int)$row['id'] : $lieuModel->create([
                'name' => $name,
                'country' => $country,
                'lat' => $lat,
                'lng' => $lng,
                'img' => null,
                'bg_lieux' => null,
            ]);

            $result[] = ['id' => $lieuId, 'name' => $name, 'country' => $country];
        }

        header('Content-Type: application/json');
        echo json_encode(['lieux' => $result]);
    }
}
