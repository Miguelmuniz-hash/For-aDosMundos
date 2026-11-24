<?php
declare(strict_types=1);

define('ROOT_PATH',   dirname(__DIR__, 2));
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEW_PATH',   APP_PATH . '/Views');
define('ROUTES_PATH', ROOT_PATH . '/routes');

// Autoload PSR-4 simples: App\Core\Router -> app/Core/Router.php
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require __DIR__ . '/helpers.php';

use App\Core\Config;
use App\Core\Response;
use App\Core\Session;

Config::load(CONFIG_PATH . '/config.php');
date_default_timezone_set((string) Config::get('app.timezone', 'America/Sao_Paulo'));
mb_internal_encoding('UTF-8');

// Tratamento global de erros: nunca vaza detalhes fora do modo debug.
set_error_handler(static function (int $no, string $str, string $file, int $line): bool {
    if (!(error_reporting() & $no)) {
        return false;
    }
    throw new ErrorException($str, 0, $no, $file, $line);
});
set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);
    $detalhe = Config::get('app.debug') ? $e->getMessage() . ' em ' . basename($e->getFile()) . ':' . $e->getLine() : null;
    Response::abort(500, 'Erro interno do servidor.', $detalhe);
});
ini_set('display_errors', '0');

Session::start();
