<?php

require_once ROOT . '/backend/models/articleModel';
require_once ROOT . '/backend/models/filmModel.php';
require_once ROOT . '/backend/models/lieuModel';
require_once ROOT . '/backend/services/TmdbService.php';

class SearchController
{
    public function autocomplete(): void
    {
        $query = trim((string)($_POST['query'] ?? ''));
        $q = mb_strtolower($query);
        $results = [];

        if ($q === '') {
            header('Content-Type: application/json');
            echo json_encode(['results' => []]);
            return;
        }

        // Articles (titres présents sur le site)
        $articleModel = new Article();
        $articles = $articleModel->searchTitles($query, 6);
        foreach ($articles as $a) {
            $results[] = [
                'type' => 'article',
                'label' => $a['label'],
                'url' => BASE_URL . 'articles/' . ($a['id'] ?? ''),
            ];
        }

        // Films : uniquement les films dont l'ID est publié en BDD
        $filmModel = new Film();
        foreach ($filmModel->getPublishedTmdbIds() as $tmdbId) {
            $film = TmdbService::getDetails((int)$tmdbId);
            $title = $film['title'] ?? $film['name'] ?? '';
            if ($title !== '' && mb_stripos(mb_strtolower($title), $q) !== false) {
                $results[] = [
                    'type' => 'film',
                    'label' => $title,
                    'url' => BASE_URL . 'films/' . ($film['id'] ?? $tmdbId),
                ];
                if (count($results) >= 12) break;
            }
        }

        // Lieux : uniquement lieux publiés (référencés par les articles)
        $lieuModel = new Lieu();
        $lieux = $lieuModel->getPublishedLieux();
        foreach ($lieux as $l) {
            if (!empty($l['name']) && mb_stripos(mb_strtolower($l['name']), $q) !== false) {
                $results[] = [
                    'type' => 'lieu',
                    'label' => $l['name'],
                    'url' => BASE_URL . 'lieux/' . ($l['id'] ?? ''),
                ];
                if (count($results) >= 16) break;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['results' => array_values($results)]);
    }
}
