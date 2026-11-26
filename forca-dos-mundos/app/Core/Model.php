<?php
namespace App\Core;

use PDO;

/**
 * Classe base dos Models: dá acesso ao PDO e atalhos de consulta.
 * Todas as consultas usam prepared statements.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** Executa uma consulta e devolve todas as linhas. */
    protected function buscarTodos(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Executa uma consulta e devolve uma linha (ou null). */
    protected function buscarUm(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $linha = $stmt->fetch();
        return $linha ?: null;
    }

    /** Executa uma consulta e devolve o primeiro valor da primeira linha. */
    protected function valor(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /** Executa INSERT/UPDATE/DELETE e devolve as linhas afetadas. */
    protected function executar(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
