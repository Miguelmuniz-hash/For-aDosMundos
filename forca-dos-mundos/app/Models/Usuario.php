<?php
namespace App\Models;

use App\Core\Model;

/**
 * Acesso à tabela "usuarios".
 */
class Usuario extends Model
{
    public function buscarPorId(int $id): ?array
    {
        return $this->buscarUm('SELECT * FROM usuarios WHERE id = ?', [$id]);
    }

    public function buscarPorEmail(string $email): ?array
    {
        return $this->buscarUm('SELECT * FROM usuarios WHERE email = ?', [$email]);
    }

    public function emailExiste(string $email, int $ignorarId = 0): bool
    {
        return (int) $this->valor('SELECT COUNT(*) FROM usuarios WHERE email = ? AND id <> ?', [$email, $ignorarId]) > 0;
    }

    public function apelidoExiste(string $apelido, int $ignorarId = 0): bool
    {
        return (int) $this->valor('SELECT COUNT(*) FROM usuarios WHERE apelido = ? AND id <> ?', [$apelido, $ignorarId]) > 0;
    }

    /** Cria um jogador. A senha deve chegar já protegida com password_hash(). */
    public function criar(string $nome, string $apelido, string $email, string $senhaHash, string $perfil = 'jogador'): int
    {
        $this->executar(
            'INSERT INTO usuarios (nome, apelido, email, senha, perfil) VALUES (?, ?, ?, ?, ?)',
            [$nome, $apelido, $email, $senhaHash, $perfil]
        );
        return (int) $this->db->lastInsertId();
    }

    public function atualizarDados(int $id, string $nome, string $apelido, string $email): void
    {
        $this->executar('UPDATE usuarios SET nome = ?, apelido = ?, email = ? WHERE id = ?', [$nome, $apelido, $email, $id]);
    }

    public function atualizarSenha(int $id, string $senhaHash): void
    {
        $this->executar('UPDATE usuarios SET senha = ? WHERE id = ?', [$senhaHash, $id]);
    }

    public function atualizarFoto(int $id, ?string $foto): void
    {
        $this->executar('UPDATE usuarios SET foto = ? WHERE id = ?', [$foto, $id]);
    }

    /** Edição feita pelo administrador. */
    public function atualizarPeloAdmin(int $id, string $nome, string $apelido, string $email, string $perfil): void
    {
        $this->executar(
            'UPDATE usuarios SET nome = ?, apelido = ?, email = ?, perfil = ? WHERE id = ?',
            [$nome, $apelido, $email, $perfil, $id]
        );
    }

    public function definirStatus(int $id, string $status): void
    {
        $this->executar('UPDATE usuarios SET status = ? WHERE id = ?', [$status, $id]);
    }

    /** Exclui a conta. As partidas ficam anonimizadas (FK ON DELETE SET NULL). */
    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM usuarios WHERE id = ?', [$id]);
    }

    /** Quantos administradores ATIVOS existem além do usuário informado. */
    public function adminsAtivosExceto(int $id): int
    {
        return (int) $this->valor(
            "SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin' AND status = 'ativo' AND id <> ?",
            [$id]
        );
    }

    /** Confirma o e-mail manualmente (ação do administrador). Devolve true se algo mudou. */
    public function verificarEmail(int $id): bool
    {
        return $this->executar(
            'UPDATE usuarios SET email_verificado_em = NOW() WHERE id = ? AND email_verificado_em IS NULL',
            [$id]
        ) > 0;
    }

    /** Monta o WHERE da listagem do admin (a tabela sempre usa o alias "u"). */
    private function filtroAdmin(string $busca, string $perfil, string $status): array
    {
        $condicoes = ['1 = 1'];
        $params = [];
        if ($busca !== '') {
            $condicoes[] = '(u.nome LIKE ? OR u.apelido LIKE ? OR u.email LIKE ?)';
            $termo = '%' . $busca . '%';
            array_push($params, $termo, $termo, $termo);
        }
        if (in_array($perfil, ['admin', 'jogador'], true)) {
            $condicoes[] = 'u.perfil = ?';
            $params[] = $perfil;
        }
        if (in_array($status, ['ativo', 'inativo'], true)) {
            $condicoes[] = 'u.status = ?';
            $params[] = $status;
        } elseif ($status === 'pendente') {
            // E-mail ainda não confirmado.
            $condicoes[] = 'u.email_verificado_em IS NULL';
        }
        return [implode(' AND ', $condicoes), $params];
    }

    public function contarFiltrados(string $busca, string $perfil, string $status): int
    {
        [$where, $params] = $this->filtroAdmin($busca, $perfil, $status);
        return (int) $this->valor('SELECT COUNT(*) FROM usuarios u WHERE ' . $where, $params);
    }

    public function listarFiltrados(string $busca, string $perfil, string $status, int $limite, int $offset): array
    {
        [$where, $params] = $this->filtroAdmin($busca, $perfil, $status);
        $sql = "SELECT u.*,
                       (SELECT COUNT(*) FROM partidas p WHERE p.usuario_id = u.id) AS total_partidas,
                       (SELECT COUNT(*) FROM partidas p WHERE p.usuario_id = u.id AND p.resultado = 'vitoria') AS total_vitorias,
                       (SELECT COALESCE(SUM(p.pontos), 0) FROM partidas p WHERE p.usuario_id = u.id) AS total_pontos,
                       (SELECT MAX(p.data_hora) FROM partidas p WHERE p.usuario_id = u.id) AS ultima_partida
                FROM usuarios u
                WHERE " . $where . '
                ORDER BY u.criado_em DESC, u.id DESC
                LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset;
        return $this->buscarTodos($sql, $params);
    }

    /** Cadastros mais recentes (dashboard administrativo). */
    public function recentes(int $limite = 5): array
    {
        return $this->buscarTodos(
            'SELECT id, apelido, nome, foto, perfil, status, email_verificado_em, criado_em
             FROM usuarios
             ORDER BY criado_em DESC, id DESC
             LIMIT ' . (int) $limite
        );
    }

    /** Totais para o dashboard administrativo. */
    public function totais(): array
    {
        $vazio = ['total' => 0, 'jogadores' => 0, 'admins' => 0, 'ativos' => 0, 'inativos' => 0, 'pendentes' => 0, 'novos7' => 0];
        $dados = $this->buscarUm(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(perfil = 'jogador'), 0) AS jogadores,
                    COALESCE(SUM(perfil = 'admin'), 0) AS admins,
                    COALESCE(SUM(status = 'ativo'), 0) AS ativos,
                    COALESCE(SUM(status = 'inativo'), 0) AS inativos,
                    COALESCE(SUM(email_verificado_em IS NULL), 0) AS pendentes,
                    COALESCE(SUM(criado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)), 0) AS novos7
             FROM usuarios"
        );
        return $dados ?: $vazio;
    }
}
