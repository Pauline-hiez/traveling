<?php

$url    = trim($_GET['url'] ?? '', '/');
$url    = filter_var($url, FILTER_SANITIZE_URL);
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    // Accueil
    ['GET',  '',                                      'HomeController',    'index'],

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
