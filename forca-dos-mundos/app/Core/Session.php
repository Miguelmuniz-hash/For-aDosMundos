<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) Config::get('session.lifetime', 7200));
        session_name((string) Config::get('session.name', 'FDM_SESSID'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Flash: o que foi gravado na requisicao anterior fica legivel apenas nesta.
        $_SESSION['_flash_now'] = $_SESSION['_flash'] ?? [];
        $_SESSION['_flash']     = [];
    }

    public static function get(string $chave, mixed $padrao = null): mixed
    {
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function set(string $chave, mixed $valor): void
    {
        $_SESSION[$chave] = $valor;
    }

    public static function has(string $chave): bool
    {
        return isset($_SESSION[$chave]);
    }

    public static function forget(string $chave): void
    {
        unset($_SESSION[$chave]);
    }

    /** Grava um valor para ser lido somente na proxima requisicao. */
    public static function flash(string $chave, mixed $valor): void
    {
        $_SESSION['_flash'][$chave] = $valor;
    }

    /** Le um valor flash vindo da requisicao anterior. */
    public static function flashed(string $chave, mixed $padrao = null): mixed
    {
        return $_SESSION['_flash_now'][$chave] ?? $padrao;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /** Destroi a sessao e abre uma nova e limpa (mantem a possibilidade de usar flash). */
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
        }
        session_destroy();
        self::start();
        session_regenerate_id(true);
    }
}
