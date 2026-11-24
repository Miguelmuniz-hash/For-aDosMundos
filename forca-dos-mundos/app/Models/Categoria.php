<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Categoria extends Model
{
    protected static string $table = 'categorias';

    public static function ativasComContagem(): array
    {
        return self::fetchAll(
            "SELECT c.*, COUNT(p.id) AS total_palavras
             FROM categorias c
             LEFT JOIN palavras p ON p.categoria_id = c.id
             WHERE c.status = 'ativa'
             GROUP BY c.id
             ORDER BY c.nome ASC"
        );
    }

    public static function todasComContagem(): array
    {
        return self::fetchAll(
            "SELECT c.*, COUNT(p.id) AS total_palavras
             FROM categorias c
             LEFT JOIN palavras p ON p.categoria_id = c.id
             GROUP BY c.id
             ORDER BY c.nome ASC"
        );
    }

    public static function findByNome(string $nome, ?int $ignorarId = null): ?array
    {
        if ($ignorarId !== null) {
            return self::fetchOne(
                'SELECT * FROM categorias WHERE nome = ? AND id != ? LIMIT 1',
                [trim($nome), $ignorarId]
            );
        }
        return self::fetchOne('SELECT * FROM categorias WHERE nome = ? LIMIT 1', [trim($nome)]);
    }
}