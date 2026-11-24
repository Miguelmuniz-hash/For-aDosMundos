<?php
declare(strict_types=1);

/**
 * Front controller: todas as requisicoes passam por aqui (ver public/.htaccess).
 */
require dirname(__DIR__) . '/app/Core/bootstrap.php';

use App\Core\Router;

$router = new Router();
require ROUTES_PATH . '/web.php';
$router->dispatch();
