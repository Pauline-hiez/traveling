<?php

require_once ROOT . '/backend/models/filmModel.php';
require_once ROOT . '/backend/services/TmdbService.php';
require_once ROOT . '/backend/services/RendererService.php';

class FilmController
{
    public function liste(): void
    {
        // Charge les films publiés depuis la base de données
        $filmModel = new Film();
        $search = trim($_GET['search'] ?? '');
        $films = [];

        // Récupère les détails TMDB pour chaque film publié
        foreach ($filmModel->getPublishedTmdbIds() as $tmdbId) {
            $film = TmdbService::getDetails((int)$tmdbId);
            if ($film) {
                $films[] = $film;
            }
        }

        // Filtre par titre si une recherche est demandée
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $films = array_values(array_filter($films, static function (array $film) use ($needle): bool {
                return str_contains(mb_strtolower($film['title'] ?? ''), $needle);
            }));
        }

        // Pagination simple en mémoire
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = FILM_PER_PAGE;
        $total = count($films);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $films = array_slice($films, $offset, $perPage);

        // Envoi des données à la vue
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'films' => $films,
            'search' => $search,
            'page' => $page,
            'pages' => $pages,
            'title' => 'Films & séries - Traveling',
        ]);
        echo $renderer->render('films/film-list');
    }

    public function show(string $tmdbId): void
    {
        // Charge les détails du film et ses données associées
        $filmModel = new Film();
        $film = TmdbService::getDetails((int)$tmdbId);
        $credits = TmdbService::getCredits((int)$tmdbId);
        $similarFilms = TmdbService::getSimilar((int)$tmdbId);
        $cast = $credits['cast'] ?? [];

        // Film introuvable
        if (!$film) {
            http_response_code(400);
            $renderer = new \Services\Renderer();
            echo $renderer->render('errors/404');
            return;
        }

        // Articles reliés au film
        $articles = $filmModel->getArticlesByTmdb((int)$tmdbId);
        $publishedLookup = array_flip(array_map('intval', $filmModel->getPublishedTmdbIds()));

        // Conserve seulement les films similaires publiés
        $similarFilms = array_values(array_filter($similarFilms, static function (array $similarFilm) use ($publishedLookup): bool {
            return isset($publishedLookup[(int)$similarFilm['id']]);
        }));

        // Limite l'affichage à 5 suggestions
        $similarFilms = array_slice($similarFilms, 0, 5);

        // Envoi des données à la vue
        $renderer = new \Services\Renderer();
        $renderer->addparamsArray([
            'film' => $film,
            'credits' => $credits,
            'cast' => $cast,
            'similarFilm' => $similarFilms,
            'articles' => $articles,
            'title' => ($film['title'] ?? 'Film') . ' - Traveling',
        ]);
        echo $renderer->render('films/film');
    }

    public function autocomplete(): void
    {
        // Autocomplète utilise la recherche TMDB (mots-clés) puis filtre pour ne garder que les IDs TMDB déjà publiés en BDD
        $query = trim((string)($_POST['query'] ?? ''));
        if ($query === '') {
            header('Content-Type: application/json');
            echo json_encode(['results' => []]);
            return;
        }

        $filmModel = new Film();
        $published = array_map('intval', $filmModel->getPublishedTmdbIds());
        $publishedLookup = array_flip($published);

        // Requête TMDB pour obtenir des résultats pertinents par mots-clés
        $candidates = TmdbService::search($query);
        $results = [];

        foreach ($candidates as $c) {
            $id = isset($c['id']) ? (int)$c['id'] : null;
            if ($id === null) continue;
            if (!isset($publishedLookup[$id])) continue;

            $title = $c['title'] ?? $c['name'] ?? '';
            $results[] = [
                'id' => $id,
                'label' => $title,
                'release_date' => $c['release_date'] ?? $c['first_air_date'] ?? '',
                'poster' => TmdbService::imgUrl($c['poster_path'] ?? null, 'w200'),
            ];
            if (count($results) >= 8) break;
        }

        header('Content-Type: application/json');
        echo json_encode(['results' => $results]);
    }
}
