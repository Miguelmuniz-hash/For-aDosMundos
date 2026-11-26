<?php
namespace App\Models;

use App\Core\Model;

/**
 * Tokens de confirmação de e-mail. Somente o HASH do token é guardado.
 */
class VerificacaoEmail extends Model
{
    /** Cria um token novo (invalida os anteriores) e devolve o token em texto puro. */
    public function criar(int $usuarioId, int $minutosValidade = 1440): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expira = date('Y-m-d H:i:s', time() + $minutosValidade * 60);

        $this->executar(
            'UPDATE verificacoes_email SET utilizado_em = NOW() WHERE usuario_id = ? AND utilizado_em IS NULL',
            [$usuarioId]
        );
        $this->executar(
            'INSERT INTO verificacoes_email (usuario_id, token_hash, expira_em) VALUES (?, ?, ?)',
            [$usuarioId, $hash, $expira]
        );
        return $token;
    }

    /** Confirma o e-mail se o token for válido. Devolve o id do usuário ou null. */
    public function confirmar(string $token): ?int
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        $registro = $this->buscarUm(
            "SELECT v.* FROM verificacoes_email v
             JOIN usuarios u ON u.id = v.usuario_id
             WHERE v.token_hash = ? AND v.utilizado_em IS NULL AND v.expira_em > NOW() AND u.status = 'ativo'",
            [hash('sha256', $token)]
        );
        if (!$registro) {
            return null;
        }

        $this->db->beginTransaction();
        try {
            $afetadas = $this->executar(
                'UPDATE verificacoes_email SET utilizado_em = NOW() WHERE id = ? AND utilizado_em IS NULL',
                [$registro['id']]
            );
            if ($afetadas !== 1) {
                $this->db->rollBack();
                return null;
            }
            $this->executar(
                'UPDATE usuarios SET email_verificado_em = NOW() WHERE id = ? AND email_verificado_em IS NULL',
                [$registro['usuario_id']]
            );
            $this->db->commit();
            return (int) $registro['usuario_id'];
        } catch (\Throwable $erro) {
            $this->db->rollBack();
            throw $erro;
        }
    }
}