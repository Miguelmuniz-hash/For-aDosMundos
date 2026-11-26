<?php
/**
 * Front controller: toda requisição entra por aqui.
 */
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

// Autoload simples (sem Composer): App\Foo\Bar => app/Foo/Bar.php
spl_autoload_register(function (string $classe): void {
    if (strpos($classe, 'App\\') !== 0) {
        return;
    }
    $arquivo = BASE_PATH . '/app/' . str_replace('\\', '/', substr($classe, 4)) . '.php';
    if (is_file($arquivo)) {
        require $arquivo;
    }
});

require BASE_PATH . '/app/Core/helpers.php';

// Cabeçalhos de segurança
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Sessão com cookie seguro
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('FORCASESS');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $https,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

date_default_timezone_set('America/Sao_Paulo');

$router = new App\Core\Router();
require BASE_PATH . '/routes/web.php';
$router->dispatch();
