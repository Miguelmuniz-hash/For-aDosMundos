<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const CHAVE = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::CHAVE])) {
            $_SESSION[self::CHAVE] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CHAVE];
    }

    public static function refresh(): void
    {
        unset($_SESSION[self::CHAVE]);
    }

    public static function valid(): bool
    {
        $enviado = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $esperado = $_SESSION[self::CHAVE] ?? '';
        return is_string($enviado) && $esperado !== '' && hash_equals($esperado, $enviado);
    }

    /** Interrompe a requisicao (419) se o token for invalido. */
    public static function verifyOrFail(): void
    {
        if (!self::valid()) {
            Response::abort(419, 'Sessao expirada ou formulario invalido. Recarregue a pagina e tente novamente.');
        }
    }
}
