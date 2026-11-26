<?php
namespace App\Models;

use App\Core\Model;

/**
 * Controle de tentativas de login (proteção contra força bruta).
 */
class TentativaLogin extends Model
{
    private const JANELA_MINUTOS = 15;
    private const MAX_POR_EMAIL = 5;
    private const MAX_POR_IP = 20;

    public function registrar(string $email, string $ip): void
    {
        $this->executar('INSERT INTO tentativas_login (email, ip) VALUES (?, ?)', [$email, $ip]);
    }

    /** Verdadeiro se o e-mail ou o IP passaram do limite na janela de tempo. */
    public function bloqueado(string $email, string $ip): bool
    {
        $limite = date('Y-m-d H:i:s', time() - self::JANELA_MINUTOS * 60);
        $porEmail = (int) $this->valor(
            'SELECT COUNT(*) FROM tentativas_login WHERE email = ? AND ip = ? AND criado_em > ?',
            [$email, $ip, $limite]
        );
        $porIp = (int) $this->valor(
            'SELECT COUNT(*) FROM tentativas_login WHERE ip = ? AND criado_em > ?',
            [$ip, $limite]
        );
        return $porEmail >= self::MAX_POR_EMAIL || $porIp >= self::MAX_POR_IP;
    }

    public function limpar(string $email, string $ip): void
    {
        $this->executar('DELETE FROM tentativas_login WHERE email = ? AND ip = ?', [$email, $ip]);
    }

    public static function janelaMinutos(): int
    {
        return self::JANELA_MINUTOS;
    }
}
