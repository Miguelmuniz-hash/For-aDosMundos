<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class RecuperacaoSenha extends Model
{
    protected static string $table = 'recuperacao_senhas';

    /** Gera token seguro, salva o hash SHA-256 no banco e retorna o token puro para o link. */
    public static function criarToken(int $usuarioId): string
    {
        // Invalida tokens anteriores não utilizados desse usuário
        self::execute(
            'UPDATE recuperacao_senhas SET utilizado_em = NOW() WHERE usuario_id = ? AND utilizado_em IS NULL',
            [$usuarioId]
        );

        $tokenPuro = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $tokenPuro);

        // Expira em 1 hora
        self::execute(
            'INSERT INTO recuperacao_senhas (usuario_id, token_hash, expira_em) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))',
            [$usuarioId, $tokenHash]
        );

        return $tokenPuro;
    }

    /** Busca registro válido (não usado e não expirado) pelo token puro da URL. */
    public static function validarToken(string $tokenPuro): ?array
    {
        $tokenHash = hash('sha256', $tokenPuro);

        return self::fetchOne(
            'SELECT * FROM recuperacao_senhas 
             WHERE token_hash = ? 
               AND utilizado_em IS NULL 
               AND expira_em > NOW() 
             LIMIT 1',
            [$tokenHash]
        );
    }

    public static function marcarComoUsado(int $id): void
    {
        self::execute('UPDATE recuperacao_senhas SET utilizado_em = NOW() WHERE id = ?', [$id]);
    }
}