<?php
namespace App\Models;

use App\Core\Model;

/**
 * Acesso à tabela "partidas" e consultas de estatísticas/ranking.
 */
class Partida extends Model
{
    /** Grava uma partida finalizada. */
    public function registrar(int $usuarioId, int $categoriaId, int $palavraId, string $palavra, int $erros, int $pontos, string $resultado): int
    {
        $this->executar(
            'INSERT INTO partidas (usuario_id, categoria_id, palavra_id, palavra_jogada, erros, pontos, resultado)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$usuarioId, $categoriaId, $palavraId, $palavra, $erros, $pontos, $resultado]
        );
        return (int) $this->db->lastInsertId();
    }

    /** Estatísticas de um jogador. */
    public function estatisticasUsuario(int $usuarioId): array
    {
        $dados = $this->buscarUm(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(resultado = 'vitoria'), 0) AS vitorias,
                    COALESCE(SUM(resultado = 'derrota'), 0) AS derrotas,
                    COALESCE(SUM(pontos), 0) AS pontos,
                    COALESCE(MAX(pontos), 0) AS maior
             FROM partidas WHERE usuario_id = ?",
            [$usuarioId]
        );
        $dados['taxa'] = $dados['total'] > 0 ? (int) round($dados['vitorias'] / $dados['total'] * 100) : 0;
        return $dados;
    }

    public function ultimasDoUsuario(int $usuarioId, int $limite = 5): array
    {
        return $this->buscarTodos(
            'SELECT p.*, c.nome AS categoria_nome
             FROM partidas p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.usuario_id = ?
             ORDER BY p.data_hora DESC, p.id DESC
             LIMIT ' . (int) $limite,
            [$usuarioId]
        );
    }

    /** Pontos das últimas partidas (para o gráfico), da mais antiga para a mais nova. */
    public function pontosRecentes(int $usuarioId, int $limite = 10): array
    {
        $linhas = $this->buscarTodos(
            'SELECT pontos, resultado FROM partidas WHERE usuario_id = ? ORDER BY data_hora DESC, id DESC LIMIT ' . (int) $limite,
            [$usuarioId]
        );
        return array_reverse($linhas);
    }

    /** Top 10 do ranking geral: maior pontuação primeiro; empate = quem fez antes. */
    public function top10(): array
    {
        return $this->buscarTodos(
            "SELECT p.id, p.pontos, p.erros, p.data_hora, u.apelido, u.foto, c.nome AS categoria_nome
             FROM partidas p
             INNER JOIN usuarios u ON u.id = p.usuario_id
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.resultado = 'vitoria'
             ORDER BY p.pontos DESC, p.data_hora ASC, p.id ASC
             LIMIT 10"
        );
    }

    private function filtroAdmin(string $busca, int $usuarioId, string $de, string $ate, string $resultado): array
    {
        $condicoes = ['1 = 1'];
        $params = [];
        if ($busca !== '') {
            $condicoes[] = '(u.apelido LIKE ? OR p.palavra_jogada LIKE ?)';
            $params[] = '%' . $busca . '%';
            $params[] = '%' . $busca . '%';
        }
        if ($usuarioId > 0) {
            $condicoes[] = 'p.usuario_id = ?';
            $params[] = $usuarioId;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $de)) {
            $condicoes[] = 'p.data_hora >= ?';
            $params[] = $de . ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $ate)) {
            $condicoes[] = 'p.data_hora <= ?';
            $params[] = $ate . ' 23:59:59';
        }
        if (in_array($resultado, ['vitoria', 'derrota'], true)) {
            $condicoes[] = 'p.resultado = ?';
            $params[] = $resultado;
        }
        return [implode(' AND ', $condicoes), $params];
    }

    public function contarAdmin(string $busca, int $usuarioId, string $de, string $ate, string $resultado): int
    {
        [$where, $params] = $this->filtroAdmin($busca, $usuarioId, $de, $ate, $resultado);
        return (int) $this->valor(
            'SELECT COUNT(*) FROM partidas p LEFT JOIN usuarios u ON u.id = p.usuario_id WHERE ' . $where,
            $params
        );
    }

    public function listarAdmin(string $busca, int $usuarioId, string $de, string $ate, string $resultado, int $limite, int $offset): array
    {
        [$where, $params] = $this->filtroAdmin($busca, $usuarioId, $de, $ate, $resultado);
        return $this->buscarTodos(
            'SELECT p.*, u.apelido, u.foto, c.nome AS categoria_nome
             FROM partidas p
             LEFT JOIN usuarios u ON u.id = p.usuario_id
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE ' . $where . '
             ORDER BY p.data_hora DESC, p.id DESC
             LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    /** Totais gerais para o dashboard administrativo. */
    public function totais(): array
    {
        $vazio = ['total' => 0, 'vitorias' => 0, 'derrotas' => 0, 'pontos' => 0, 'maior' => 0, 'media_erros' => 0, 'hoje' => 0, 'jogadores' => 0];
        $dados = $this->buscarUm(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(resultado = 'vitoria'), 0) AS vitorias,
                    COALESCE(SUM(resultado = 'derrota'), 0) AS derrotas,
                    COALESCE(SUM(pontos), 0) AS pontos,
                    COALESCE(MAX(pontos), 0) AS maior,
                    COALESCE(AVG(erros), 0) AS media_erros,
                    COALESCE(SUM(DATE(data_hora) = CURDATE()), 0) AS hoje,
                    COUNT(DISTINCT usuario_id) AS jogadores
             FROM partidas"
        );
        return $dados ?: $vazio;
    }

    /** Totais do conjunto filtrado na tela de partidas do admin. */
    public function resumoAdmin(string $busca, string $de, string $ate, string $resultado): array
    {
        [$where, $params] = $this->filtroAdmin($busca, 0, $de, $ate, $resultado);
        $vazio = ['total' => 0, 'vitorias' => 0, 'derrotas' => 0, 'pontos' => 0, 'media_erros' => 0];
        $dados = $this->buscarUm(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(p.resultado = 'vitoria'), 0) AS vitorias,
                    COALESCE(SUM(p.resultado = 'derrota'), 0) AS derrotas,
                    COALESCE(SUM(p.pontos), 0) AS pontos,
                    COALESCE(AVG(p.erros), 0) AS media_erros
             FROM partidas p
             LEFT JOIN usuarios u ON u.id = p.usuario_id
             WHERE " . $where,
            $params
        );
        return $dados ?: $vazio;
    }

    public function ultimasGeral(int $limite = 6): array
    {
        return $this->listarAdmin('', 0, '', '', '', $limite, 0);
    }

    /** Jogadores com mais pontos acumulados. */
    public function topJogadores(int $limite = 5): array
    {
        return $this->buscarTodos(
            'SELECT u.id, u.apelido, u.foto, SUM(p.pontos) AS total_pontos, MAX(p.pontos) AS maior, COUNT(*) AS partidas
             FROM partidas p
             INNER JOIN usuarios u ON u.id = p.usuario_id
             GROUP BY u.id, u.apelido, u.foto
             ORDER BY total_pontos DESC
             LIMIT ' . (int) $limite
        );
    }

    public function categoriasMaisJogadas(int $limite = 5): array
    {
        return $this->buscarTodos(
            'SELECT c.nome, COUNT(*) AS total
             FROM partidas p
             INNER JOIN categorias c ON c.id = p.categoria_id
             GROUP BY c.id, c.nome
             ORDER BY total DESC
             LIMIT ' . (int) $limite
        );
    }

    /** Desempenho de cada mundo: palavras, partidas e taxa de vitória (dashboard do admin). */
    public function desempenhoPorCategoria(int $limite = 8): array
    {
        return $this->buscarTodos(
            "SELECT c.id, c.nome, c.icone, c.status,
                    (SELECT COUNT(*) FROM palavras pl WHERE pl.categoria_id = c.id) AS palavras,
                    COUNT(pa.id) AS partidas,
                    COALESCE(SUM(pa.resultado = 'vitoria'), 0) AS vitorias
             FROM categorias c
             LEFT JOIN partidas pa ON pa.categoria_id = c.id
             GROUP BY c.id, c.nome, c.icone, c.status
             ORDER BY partidas DESC, c.nome ASC
             LIMIT " . (int) $limite
        );
    }

    /**
     * Quantas vitórias aconteceram com 0, 1, 2... erros.
     * Devolve um vetor com uma posição para cada quantidade de erros possível (0 a MAX-1).
     */
    public function vitoriasPorErros(int $maxErros = 6): array
    {
        $linhas = $this->buscarTodos(
            "SELECT erros, COUNT(*) AS total FROM partidas WHERE resultado = 'vitoria' GROUP BY erros"
        );
        $mapa = array_column($linhas, 'total', 'erros');
        $resultado = [];
        for ($e = 0; $e < $maxErros; $e++) {
            $resultado[$e] = (int) ($mapa[$e] ?? 0);
        }
        return $resultado;
    }

    /** Partidas (e vitórias) por dia nos últimos dias, incluindo dias sem jogo (gráfico do admin). */
    public function partidasPorDia(int $dias = 7): array
    {
        $linhas = $this->buscarTodos(
            "SELECT DATE(data_hora) AS dia, COUNT(*) AS total,
                    COALESCE(SUM(resultado = 'vitoria'), 0) AS vitorias
             FROM partidas
             WHERE data_hora >= DATE_SUB(CURDATE(), INTERVAL " . (int) ($dias - 1) . " DAY)
             GROUP BY DATE(data_hora)"
        );
        $mapa = [];
        foreach ($linhas as $linha) {
            $mapa[$linha['dia']] = $linha;
        }
        $resultado = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $dia = date('Y-m-d', strtotime("-$i day"));
            $total = (int) ($mapa[$dia]['total'] ?? 0);
            $vitorias = (int) ($mapa[$dia]['vitorias'] ?? 0);
            $resultado[] = ['dia' => $dia, 'total' => $total, 'vitorias' => $vitorias, 'derrotas' => $total - $vitorias];
        }
        return $resultado;
    }
}
