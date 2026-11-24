<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/** Base dos Models: tudo via PDO com prepared statements. */
abstract class Model
{
    protected static string $table = '';

    protected static function db(): PDO
    {
        return Database::pdo();
    }

    protected static function run(string $sql, array $params = []): PDOStatement
    {
        $st = self::db()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    protected static function fetchAll(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    protected static function fetchOne(string $sql, array $params = []): ?array
    {
        $linha = self::run($sql, $params)->fetch();
        return $linha === false ? null : $linha;
    }

    protected static function fetchValue(string $sql, array $params = []): mixed
    {
        $v = self::run($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    protected static function execute(string $sql, array $params = []): int
    {
        return self::run($sql, $params)->rowCount();
    }

    protected static function lastId(): int
    {
        return (int) self::db()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        return self::fetchOne('SELECT * FROM ' . static::$table . ' WHERE id = ? LIMIT 1', [$id]);
    }

    public static function count(): int
    {
        return (int) self::fetchValue('SELECT COUNT(*) FROM ' . static::$table);
    }

    public static function delete(int $id): bool
    {
        return self::execute('DELETE FROM ' . static::$table . ' WHERE id = ?', [$id]) > 0;
    }
}
