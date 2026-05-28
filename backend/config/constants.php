<?php

// Constantes globales
define('ROOT', dirname(__DIR__, 2));
define('VIEWS', ROOT . '/frontend/views/');
define('COMPONENTS', ROOT . '/frontend/components/');
define('ASSETS_URL', '/frontend/assets/');
define('BASE_URL', '/');

define('ARTICLE_PER_PAGE', 9);
// Pagination per-page values
define('FILM_PER_PAGE', 20);
define('LIEU_PER_PAGE', 12);
define('ADMIN_PER_PAGE', 10);  // users, articles, comments (signalements)

define('COMMENTS_PREVIEW', 3);
define('FAVORITES_PREVIEW', 3);
define('HISTORY_PREVIEW', 3);
