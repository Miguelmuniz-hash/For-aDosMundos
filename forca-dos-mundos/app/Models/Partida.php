<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Partida extends Model
{
    protected static string $table = 'partidas';

    public static function registrar(
        int $usuarioId,
        int $categoriaId,
        int $palavraId,
        string $palavraJogada,
        int $erros,
        int $pontos,
        string $resultado,
        bool $usouRevelar
    ): int {
        self::execute(
            'INSERT INTO partidas (usuario_id, categoria_id, palavra_id, palavra_jogada, erros, pontos, resultado, usou_revelar)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $usuarioId,
                $categoriaId,
                $palavraId,
                $palavraJogada,
                $erros,
                $pontos,
                $resultado,
                $usouRevelar ? 1 : 0
            ]
        );
        return self::lastId();
    }

    public static function estatisticasUsuario(int $usuarioId): array
    {
        $row = self::fetchOne(
            "SELECT 
                COUNT(*) AS total_partidas,
                SUM(CASE WHEN resultado = 'vitoria' THEN 1 ELSE 0 END) AS vitorias,
                SUM(CASE WHEN resultado = 'derrota' THEN 1 ELSE 0 END) AS derrotas,
                COALESCE(SUM(pontos), 0) AS total_pontos,
                COALESCE(MAX(pontos), 0) AS maior_pontuacao
             FROM partidas
             WHERE usuario_id = ?",
            [$usuarioId]
        );

        $total = (int) ($row['total_partidas'] ?? 0);
        $vitorias = (int) ($row['vitorias'] ?? 0);

        return [
            'total_partidas'  => $total,
            'vitorias'        => $vitorias,
            'derrotas'        => (int) ($row['derrotas'] ?? 0),
            'total_pontos'    => (int) ($row['total_pontos'] ?? 0),
            'maior_pontuacao' => (int) ($row['maior_pontuacao'] ?? 0),
            'taxa_vitorias'   => $total > 0 ? round(($vitorias / $total) * 100, 1) : 0.0,
        ];
    }

    public static function ultimasDoUsuario(int $usuarioId, int $limite = 5): array
    {
        return self::fetchAll(
            "SELECT p.*, c.nome AS categoria_nome
             FROM partidas p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.usuario_id = ?
             ORDER BY p.data_hora DESC, p.id DESC
             LIMIT " . (int) $limite,
            [$usuarioId]
        );
    }

    public static function topRanking(int $limite = 10): array
    {
        return self::fetchAll(
            "SELECT p.*, u.apelido, u.foto, c.nome AS categoria_nome
             FROM partidas p
             INNER JOIN usuarios u ON u.id = p.usuario_id
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.pontos > 0
             ORDER BY p.pontos DESC, p.data_hora ASC
             LIMIT " . (int) $limite
        );
    }
}