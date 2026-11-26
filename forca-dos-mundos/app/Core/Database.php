<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Conexão única (singleton) com o MySQL usando PDO.
 */
class Database
{
    private static ?PDO $conexao = null;

    public static function connection(): PDO
    {
        if (self::$conexao === null) {
            $config = require BASE_PATH . '/config/database.php';
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['porta'],
                $config['banco'],
                $config['charset']
            );
            try {
                self::$conexao = new PDO($dsn, $config['usuario'], $config['senha'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $erro) {
                http_response_code(500);
                exit('Não foi possível conectar ao banco de dados. Confira o arquivo config/database.php e se o MySQL está ligado no XAMPP.');
            }
        }
        return self::$conexao;
    }

    /** Configuração geral (ambiente etc.). */
    public static function config(string $chave, $padrao = null)
    {
        static $config = null;
        if ($config === null) {
            $config = require BASE_PATH . '/config/database.php';
        }
        return $config[$chave] ?? $padrao;
    }
}
