<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Palavra extends Model
{
    protected static string $table = 'palavras';

    public static function sortearPorCategoria(int $categoriaId): ?array
    {
        return self::fetchOne(
            'SELECT * FROM palavras WHERE categoria_id = ? ORDER BY RAND() LIMIT 1',
            [$categoriaId]
        );
    }

    public static function listar(?int $categoriaId = null, string $busca = ''): array
    {
        $sql = 'SELECT p.*, c.nome AS categoria_nome 
                FROM palavras p 
                INNER JOIN categorias c ON c.id = p.categoria_id 
                WHERE 1=1';
        $params = [];

        if ($categoriaId !== null && $categoriaId > 0) {
            $sql .= ' AND p.categoria_id = ?';
            $params[] = $categoriaId;
        }
        if ($busca !== '') {
            $sql .= ' AND (p.palavra LIKE ? OR p.dica LIKE ?)';
            $params[] = '%' . mb_strtoupper($busca) . '%';
            $params[] = '%' . $busca . '%';
        }

        $sql .= ' ORDER BY c.nome ASC, p.palavra ASC';
        return self::fetchAll($sql, $params);
    }
}