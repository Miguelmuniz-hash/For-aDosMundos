<?php
namespace App\Models;

use App\Core\Model;

/**
 * Acesso à tabela "categorias" (os mundos do jogo).
 */
class Categoria extends Model
{
    public function buscarPorId(int $id): ?array
    {
        return $this->buscarUm('SELECT * FROM categorias WHERE id = ?', [$id]);
    }

    /** Categorias ativas que possuem palavras, com a contagem (tela do jogador). */
    public function ativasComContagem(): array
    {
        return $this->buscarTodos(
            "SELECT c.*, COUNT(p.id) AS total_palavras
             FROM categorias c
             LEFT JOIN palavras p ON p.categoria_id = c.id
             WHERE c.status = 'ativo'
             GROUP BY c.id
             ORDER BY c.nome"
        );
    }

    public function nomeExiste(string $nome, int $ignorarId = 0): bool
    {
        return (int) $this->valor('SELECT COUNT(*) FROM categorias WHERE nome = ? AND id <> ?', [$nome, $ignorarId]) > 0;
    }

    public function criar(string $nome, string $descricao, string $icone, string $status, ?string $imagem): int
    {
        $this->executar(
            'INSERT INTO categorias (nome, descricao, icone, status, imagem) VALUES (?, ?, ?, ?, ?)',
            [$nome, $descricao, $icone, $status, $imagem]
        );
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, string $nome, string $descricao, string $icone, string $status): void
    {
        $this->executar(
            'UPDATE categorias SET nome = ?, descricao = ?, icone = ?, status = ? WHERE id = ?',
            [$nome, $descricao, $icone, $status, $id]
        );
    }

    public function atualizarImagem(int $id, ?string $imagem): void
    {
        $this->executar('UPDATE categorias SET imagem = ? WHERE id = ?', [$imagem, $id]);
    }

    public function definirStatus(int $id, string $status): void
    {
        $this->executar('UPDATE categorias SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function contarPalavras(int $id): int
    {
        return (int) $this->valor('SELECT COUNT(*) FROM palavras WHERE categoria_id = ?', [$id]);
    }

    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM categorias WHERE id = ?', [$id]);
    }

    public function contarFiltradas(string $busca): int
    {
        return (int) $this->valor('SELECT COUNT(*) FROM categorias WHERE nome LIKE ?', ['%' . $busca . '%']);
    }

    public function listarFiltradas(string $busca, int $limite, int $offset): array
    {
        return $this->buscarTodos(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM palavras p WHERE p.categoria_id = c.id) AS total_palavras,
                    (SELECT COUNT(*) FROM partidas pa WHERE pa.categoria_id = c.id) AS total_partidas,
                    (SELECT COUNT(*) FROM partidas pa WHERE pa.categoria_id = c.id AND pa.resultado = 'vitoria') AS total_vitorias
             FROM categorias c
             WHERE c.nome LIKE ?
             ORDER BY c.nome
             LIMIT " . (int) $limite . ' OFFSET ' . (int) $offset,
            ['%' . $busca . '%']
        );
    }

    /** Contagens para a faixa de resumo da tela de categorias. */
    public function resumo(): array
    {
        $dados = $this->buscarUm(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(t.status = 'ativo'), 0) AS ativas,
                    COALESCE(SUM(t.status = 'inativo'), 0) AS inativas,
                    COALESCE(SUM(t.qtd = 0), 0) AS sem_palavras
             FROM (
                 SELECT c.status,
                        (SELECT COUNT(*) FROM palavras p WHERE p.categoria_id = c.id) AS qtd
                 FROM categorias c
             ) t"
        );
        return $dados ?: ['total' => 0, 'ativas' => 0, 'inativas' => 0, 'sem_palavras' => 0];
    }

    /** Categorias ATIVAS que ainda não têm nenhuma palavra (o jogador não consegue jogar nelas). */
    public function ativasSemPalavras(): array
    {
        return $this->buscarTodos(
            "SELECT c.id, c.nome
             FROM categorias c
             WHERE c.status = 'ativo'
               AND NOT EXISTS (SELECT 1 FROM palavras p WHERE p.categoria_id = c.id)
             ORDER BY c.nome"
        );
    }

    /** Lista simples (id, nome) para selects. */
    public function todasParaSelect(): array
    {
        return $this->buscarTodos('SELECT id, nome, status FROM categorias ORDER BY nome');
    }

    public function total(): int
    {
        return (int) $this->valor('SELECT COUNT(*) FROM categorias');
    }
}
