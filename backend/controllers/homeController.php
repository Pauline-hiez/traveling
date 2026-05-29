<?php

// require_once ROOT . '/backend/models/articleModel.php';
require_once ROOT . '/backend/services/RendererService.php';

class HomeController
{
    public function index(): void
    {
        // Récupère les articles à mettre en avant
        // $articleModel = new Article();
        // $popular = $articleModel->getMostPopular();
        // $latest = $articleModel->getLatest(3);

        // // Envoie les données à la page d'accueil
        $renderer = new \Services\Renderer();
        // $renderer->addParamsArray([
        //     'popular' => $popular,
        //     'latest' => $latest,
        //     'title' => 'Traveling - Explorez le monde à travers le cinéma',
        // ]);
        echo $renderer->render('home/index');
    }
}
