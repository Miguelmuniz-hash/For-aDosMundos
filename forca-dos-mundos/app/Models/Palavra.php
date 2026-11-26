<?php
namespace App\Models;

use App\Core\Model;

/**
 * Acesso à tabela "palavras".
 */
class Palavra extends Model
{
    public function buscarPorId(int $id): ?array
    {
        return $this->buscarUm('SELECT * FROM palavras WHERE id = ?', [$id]);
    }

    /** Sorteia uma palavra da categoria (evitando repetir a anterior, se possível). */
    public function sortear(int $categoriaId, int $evitarId = 0): ?array
    {
        $palavra = $this->buscarUm(
            'SELECT * FROM palavras WHERE categoria_id = ? AND id <> ? ORDER BY RAND() LIMIT 1',
            [$categoriaId, $evitarId]
        );
        if ($palavra === null) {
            $palavra = $this->buscarUm('SELECT * FROM palavras WHERE categoria_id = ? ORDER BY RAND() LIMIT 1', [$categoriaId]);
        }
        return $palavra;
    }

    /** A palavra não pode se repetir (a regra vale para todo o jogo). */
    public function existe(string $palavra, int $ignorarId = 0): bool
    {
        return (int) $this->valor('SELECT COUNT(*) FROM palavras WHERE palavra = ? AND id <> ?', [$palavra, $ignorarId]) > 0;
    }

    public function criar(int $categoriaId, string $palavra, string $dica): int
    {
        $this->executar('INSERT INTO palavras (categoria_id, palavra, dica) VALUES (?, ?, ?)', [$categoriaId, $palavra, $dica]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, int $categoriaId, string $palavra, string $dica): void
    {
        $this->executar(
            'UPDATE palavras SET categoria_id = ?, palavra = ?, dica = ? WHERE id = ?',
            [$categoriaId, $palavra, $dica, $id]
        );
    }

    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM palavras WHERE id = ?', [$id]);
    }

    private function filtro(string $busca, int $categoriaId): array
    {
        $condicoes = ['1 = 1'];
        $params = [];
        if ($busca !== '') {
            $condicoes[] = '(p.palavra LIKE ? OR p.dica LIKE ?)';
            $params[] = '%' . $busca . '%';
            $params[] = '%' . $busca . '%';
        }
        if ($categoriaId > 0) {
            $condicoes[] = 'p.categoria_id = ?';
            $params[] = $categoriaId;
        }
        return [implode(' AND ', $condicoes), $params];
    }

    public function contarFiltradas(string $busca, int $categoriaId): int
    {
        [$where, $params] = $this->filtro($busca, $categoriaId);
        return (int) $this->valor('SELECT COUNT(*) FROM palavras p WHERE ' . $where, $params);
    }

    public function listarFiltradas(string $busca, int $categoriaId, int $limite, int $offset): array
    {
        [$where, $params] = $this->filtro($busca, $categoriaId);
        return $this->buscarTodos(
            "SELECT p.*, c.nome AS categoria_nome,
                    (SELECT COUNT(*) FROM partidas pa WHERE pa.palavra_id = p.id) AS total_jogadas,
                    (SELECT COUNT(*) FROM partidas pa WHERE pa.palavra_id = p.id AND pa.resultado = 'vitoria') AS total_vitorias
             FROM palavras p
             INNER JOIN categorias c ON c.id = p.categoria_id
             WHERE " . $where . '
             ORDER BY p.palavra
             LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    /** Números gerais para a faixa de resumo da tela de palavras. */
    public function resumo(): array
    {
        $dados = $this->buscarUm(
            'SELECT COUNT(*) AS total,
                    COALESCE(AVG(CHAR_LENGTH(palavra)), 0) AS media,
                    COALESCE(MAX(CHAR_LENGTH(palavra)), 0) AS maior,
                    COALESCE(MIN(CHAR_LENGTH(palavra)), 0) AS menor,
                    COUNT(DISTINCT categoria_id) AS categorias
             FROM palavras'
        );
        return $dados ?: ['total' => 0, 'media' => 0, 'maior' => 0, 'menor' => 0, 'categorias' => 0];
    }

    public function total(): int
    {
        return (int) $this->valor('SELECT COUNT(*) FROM palavras');
    }
}
