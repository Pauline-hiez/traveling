<?php

require_once ROOT . '/backend/models/lieuModel.php';
require_once ROOT . '/backend/services/TmdbService.php';
require_once ROOT . '/backend/services/MapService.php';
require_once ROOT . '/backend/services/RendererService.php';

class LieuController
{
    private Lieu $lieuModel;

    public function __construct()
    {
        $this->lieuModel = new Lieu();
    }

    public function liste(): void
    {
        // Charge tous les lieux publiés
        $search = trim((string)($_GET['search'] ?? ''));
        $lieuxAll = $this->lieuModel->getPublishedLieux();

        // Filtre par nom si recherché
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $lieuxAll = array_values(array_filter($lieuxAll, static function ($l) use ($needle) {
                return mb_stripos(mb_strtolower($l['name'] ?? ''), $needle) !== false || (!empty($l['country']) && mb_stripos(mb_strtolower($l['country']), $needle) !== false);
            }));
        }

        // Pagination en mémoire
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = LIEU_PER_PAGE;
        $total = count($lieuxAll);
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $lieux = array_slice($lieuxAll, $offset, $perPage);

        // Envoi des données à la vue
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'lieux' => $lieux,
            'page' => $page,
            'pages' => $pages,
            'search' => $search,
            'title' => 'Lieux de tournage - Traveling',
        ]);
        echo $renderer->render('lieux/lieux-list');
    }

    public function show(string $id): void
    {
        // Récupère le lieu
        $lieuId = (int)$id;
        $lieu = $this->lieuModel->getById($lieuId);
        if (!$lieu) {
            http_response_code(404);
            $renderer = new \Services\Renderer();
            echo $renderer->render('errors/404');
            return;
        }

        // Données principales de la page
        $articles = $this->lieuModel->getArticles($lieuId);
        $heroImage = $lieu['img'] ?? null;
        $osmPlace = null;
        $lieuSummary = null;
        $lieuCountry = $lieu['country'] ?? null;
        $lieuRegion = null;
        $lieuType = null;
        $lieuDisplayName = null;
        $lieuExtraTags = [];

        if (!empty($lieu['lat']) && !empty($lieu['lng'])) {
            $osmPlace = MapService::reverse((float)$lieu['lat'], (float)$lieu['lng']);
            if (is_array($osmPlace)) {
                $osmAddress = $osmPlace['address'] ?? [];
                $lieuDisplayName = $osmPlace['display_name'] ?? null;
                $lieuCountry = $osmAdress['country'] ?? $lieuCountry;
                $lieuRegion = $osmAddress['state'] ?? $osmAddress['region'] ?? null;
                $lieuType = trim((string)(($osmPlace['category'] ?? '') . ' ' . ($osmPlace['type'] ?? '')));
                $lieuExtraTags = [];

                if (!empty($lieuRegion) || !empty($lieuCountry)) {
                    $lieuSummary = 'est situé' . (!empty($lieuRegion) ? ' en ' . $lieuRegion : '') . (!empty($lieuCountry) ? ', ' . $lieuCountry : '') . '.';
                }
            }
        }

        // Fallback si OSM ne renvoie rien d'utilisable
        if (!$lieuSummary) {
            if (!empty($lieuCountry)) {
                $lieuSummary = 'est un lieu de tournage situé en ' . $lieuCountry . '.';
            } else {
                $lieuSummary = 'est un lieu de tournage emblématique référence dans Traveling.';
            }
        }

        // Liste des films associés
        $associatedFilms = [];

        foreach ($this->lieuModel->getAssociatedTmdbIds($lieuId) as $tmdbId) {
            $film = TmdbService::getDetails((int)$tmdbId);
            if ($film) {
                $associatedFilms[] = $film;
            }
        }

        $associatedFilms = array_slice($associatedFilms, 0, 5);
        $sliderImages = $this->lieuModel->getSliderImagesByLieu($lieuId);

        // Envoi des données à la vue
        $renderer = new \Services\Renderer();
        $renderer->addParamsArray([
            'lieu' => $lieu,
            'articles' => $articles,
            'heroImages' => $heroImage,
            'lieuSummary' => $lieuSummary,
            'lieuCountry' => $lieuCountry,
            'lieuRegion' => $lieuRegion,
            'lieuType' => $lieuType,
            'lieuDisplayName' => $lieuDisplayName,
            'lieuExtraTags' => $lieuExtraTags,
            'associatedFilms' => $associatedFilms,
            'sliderImages' => $sliderImages,
            'title' => ($lieu['name'] ?? 'Lieu') . ' - Traveling',
        ]);

        $renderer->addScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
        $renderer->addScript(ASSETS_URL . 'js/lieu.js');
        $renderer->addScript(ASSETS_URL . 'js/lieu-slider.js');

        echo $renderer->render('lieux/lieu');
    }

    public function autocomplete(): void
    {
        $query = trim((string)($_POST['query'] ?? ''));
        $results = [];
        if ($query === '') {
            header('Content-Type: application/json');
            echo json_encode(['results' => []]);
            return;
        }
        $lieux = $this->lieuModel->getPublishedLieux();
        $q = mb_strtolower($query);
        foreach ($lieux as $l) {
            $name = $l['name'] ?? '';
            $country = $l['country'] ?? '';
            if (($name !== '' && mb_stripos(mb_strtolower($name), $q) !== false)
                || ($country !== '' && mb_stripos(mb_strtolower($country), $q) !== false)
            ) {
                $poster = null;
                $img = $l['representative_image'] ?? $l['img'] ?? null;
                if (!empty($img)) {
                    if (preg_match('#^(https?://|/)#', $img)) {
                        $poster = $img;
                    } elseif (strpos(ltrim($img, '/'), 'frontend/assets') === 0) {
                        $poster = '/' . ltrim($img, '/');
                    } else {
                        $poster = ASSETS_URL . ltrim($img, '/');
                    }
                }

                $results[] = [
                    'id' => $l['id'] ?? null,
                    'label' => $name,
                    'poster' => $poster,
                ];
                if (count($results) >= 8) break;
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['results' => $results]);
    }
}
