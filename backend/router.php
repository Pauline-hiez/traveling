<?php

$url    = trim($_GET['url'] ?? '', '/');
$url    = filter_var($url, FILTER_SANITIZE_URL);
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    // Accueil
    ['GET',  '',                                      'HomeController',    'index'],

    // Articles
    ['GET',  'articles',                              'ArticleController', 'liste'],
    ['POST', 'articles/autocomplete',                 'ArticleController', 'autocomplete'],
    ['GET',  'articles/(\d+)',                        'ArticleController', 'show'],
    ['POST', 'articles/(\d+)/like',                   'ArticleController', 'like'],
    ['POST', 'articles/(\d+)/favori',                 'ArticleController', 'favori'],

    // Commentaires
    ['POST', 'commentaires/(\d+)/creer',              'CommentController', 'create'],
    ['POST', 'commentaires/(\d+)/signaler',           'CommentController', 'report'],
    ['POST', 'commentaires/(\d+)/like',               'CommentController', 'like'],

    // Films
    ['GET',  'films',                                 'FilmController',    'liste'],
    ['POST', 'films/autocomplete',                    'FilmController',    'autocomplete'],
    ['GET',  'films/(\d+)',                           'FilmController',    'show'],

    // Recherche globale (autocomplete local uniquement)
    ['POST', 'search/autocomplete',                   'SearchController',  'autocomplete'],

    // Lieux
    ['GET',  'lieux',                                 'LieuController',    'liste'],
    ['POST', 'lieux/autocomplete',                    'LieuController',    'autocomplete'],
    ['GET',  'lieux/(\d+)',                           'LieuController',    'show'],

    // Auth
    ['POST', 'auth/inscription',                      'AuthController',    'register'],
    ['POST', 'auth/connexion',                        'AuthController',    'login'],
    ['POST', 'auth/mot-de-passe-oublie',              'AuthController',    'forgotPassword'],
    ['GET',  'auth/reinitialiser-mot-de-passe',       'AuthController',    'showResetPassword'],
    ['POST', 'auth/reinitialiser-mot-de-passe',       'AuthController',    'resetPassword'],
    ['GET',  'auth/deconnexion',                      'AuthController',    'logout'],
    ['GET',  'auth/google',                           'AuthController',    'googleRedirect'],
    ['GET',  'auth/google/callback',                  'AuthController',    'googleCallback'],

    // Profil
    ['GET',  'profil',                                'UserController',    'index'],
    ['POST', 'profil/email',                          'UserController',    'updateEmail'],
    ['POST', 'profil/pseudo',                         'UserController',    'updatePseudo'],
    ['POST', 'profil/password',                       'UserController',    'updatePassword'],
    ['POST', 'profil/avatar',                         'UserController',    'updateAvatar'],
    ['POST', 'profil/background',                     'UserController',    'updateBackground'],
    ['POST', 'profil/supprimer',                      'UserController',    'delete'],

    // Admin
    ['GET',  'admin',                                 'AdminController',   'dashboard'],
    ['GET',  'admin/utilisateurs',                    'AdminController',   'users'],
    ['POST', 'admin/utilisateurs/(\d+)/role',         'AdminController',   'changeRole'],
    ['POST', 'admin/utilisateurs/(\d+)/supprimer',    'AdminController',   'deleteUser'],
    ['GET',  'admin/commentaires',                    'AdminController',   'comments'],
    ['POST', 'admin/commentaires/(\d+)/supprimer',    'AdminController',   'deleteComment'],
    ['POST', 'admin/commentaires/(\d+)/avertir',      'AdminController',   'warnUser'],
    ['GET',  'admin/articles',                        'AdminController',   'articles'],
    ['POST', 'admin/articles/(\d+)/data',             'AdminController',   'articleData'],
    ['POST', 'admin/articles/publier',                'AdminController',   'publishArticle'],
    ['POST', 'admin/articles/(\d+)/modifier',         'AdminController',   'editArticle'],
    ['POST', 'admin/articles/(\d+)/supprimer',        'AdminController',   'deleteArticle'],
    // CORRECTION : routes manquantes pour les recherches TMDB/lieux
    ['POST', 'admin/tmdb-search',                     'AdminController',   'tmdbSearch'],
    ['POST', 'admin/lieu-search',                     'AdminController',   'lieuSearch'],

    // Modérateur
    ['GET',  'moderateur/soumettre',                  'ModerateurController', 'soumettre'],

    // Pages statiques
    ['GET',  'contact',                               'PageController',    'contact'],
    ['POST', 'contact',                               'PageController',    'sendContact'],
    ['GET',  'faq',                                   'PageController',    'faq'],
    ['GET',  'about',                               'PageController',    'about'],
    ['GET',  'mentions-legales',                      'PageController',    'mentions'],

    // Newsletter
    ['GET',  'newsletter/desabonnement',             'PageController',    'newsletterUnsubscribe'],
    ['GET',  'newsletter/apercu',                     'PageController',    'newsletterPreview'],
    ['POST', 'newsletter',                            'PageController',    'newsletter'],
];

$matched = false;

foreach ($routes as [$routeMethod, $pattern, $controllerName, $action]) {
    if ($method !== $routeMethod) continue;

    if (preg_match('#^' . $pattern . '$#', $url, $params)) {
        array_shift($params);

        $controllerFile = ROOT . '/backend/controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) break;

        require_once $controllerFile;
        $controller = new $controllerName();
        $controller->$action(...$params);

        $matched = true;
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    require_once ROOT . '/backend/services/RendererService.php';
    $renderer = new \Services\Renderer();
    echo $renderer->render('errors/404');
}
