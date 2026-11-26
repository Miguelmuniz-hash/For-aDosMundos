<?php
namespace App\Models;

use App\Core\Model;

/**
 * Tokens de recuperação de senha. Somente o HASH do token é guardado.
 */
class RecuperacaoSenha extends Model
{
    /** Cria um token novo (invalida os anteriores) e devolve o token em texto puro. */
    public function criar(int $usuarioId, int $minutosValidade = 30): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expira = date('Y-m-d H:i:s', time() + $minutosValidade * 60);

        $this->db->beginTransaction();
        try {
            $this->executar(
                'UPDATE recuperacao_senhas SET utilizado_em = NOW() WHERE usuario_id = ? AND utilizado_em IS NULL',
                [$usuarioId]
            );
            $this->executar(
                'INSERT INTO recuperacao_senhas (usuario_id, token_hash, expira_em) VALUES (?, ?, ?)',
                [$usuarioId, $hash, $expira]
            );
            $this->db->commit();
        } catch (\Throwable $erro) {
            $this->db->rollBack();
            throw $erro;
        }
        return $token;
    }

    /** Busca um token válido (não usado e não vencido). */
    public function buscarValido(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        return $this->buscarUm(
            'SELECT * FROM recuperacao_senhas WHERE token_hash = ? AND utilizado_em IS NULL AND expira_em > NOW()',
            [hash('sha256', $token)]
        );
    }

    /** Troca a senha e invalida o token numa única transação. */
    public function redefinirSenha(array $registro, string $senhaHash): bool
    {
        $this->db->beginTransaction();
        try {
            // Marca como usado só se ainda estiver livre (evita uso duplo).
            $afetadas = $this->executar(
                'UPDATE recuperacao_senhas SET utilizado_em = NOW() WHERE id = ? AND utilizado_em IS NULL',
                [$registro['id']]
            );
            if ($afetadas !== 1) {
                $this->db->rollBack();
                return false;
            }
            $this->executar('UPDATE usuarios SET senha = ? WHERE id = ?', [$senhaHash, $registro['usuario_id']]);
            $this->executar(
                'UPDATE recuperacao_senhas SET utilizado_em = NOW() WHERE usuario_id = ? AND utilizado_em IS NULL',
                [$registro['usuario_id']]
            );
            $this->db->commit();
            return true;
        } catch (\Throwable $erro) {
            $this->db->rollBack();
            throw $erro;
        }
    }
}
