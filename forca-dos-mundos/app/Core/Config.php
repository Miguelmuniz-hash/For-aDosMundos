<?php
declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static array $itens = [];

    public static function load(string $arquivo): void
    {
        self::$itens = require $arquivo;
    }

    /** Leitura com notacao de ponto: Config::get('db.host'). */
    public static function get(string $chave, mixed $padrao = null): mixed
    {
        $valor = self::$itens;
        foreach (explode('.', $chave) as $parte) {
            if (!is_array($valor) || !array_key_exists($parte, $valor)) {
                return $padrao;
            }
            $valor = $valor[$parte];
        }
        return $valor;
    }

    /** Caminho base do projeto na web (ex.: /forca-dos-mundos/public). */
    public static function baseUrl(): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $base = rtrim(dirname($script), '/');
        return $base === '.' ? '' : $base;
    }
}
