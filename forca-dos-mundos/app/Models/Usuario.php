<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Usuario extends Model
{
    protected static string $table = 'usuarios';

    public static function findByEmail(string $email): ?array
    {
        return self::fetchOne(
            'SELECT * FROM usuarios WHERE email = ? LIMIT 1',
            [mb_strtolower(trim($email))]
        );
    }

    public static function findByApelido(string $apelido): ?array
    {
        return self::fetchOne(
            'SELECT * FROM usuarios WHERE apelido = ? LIMIT 1',
            [trim($apelido)]
        );
    }

    public static function criar(string $nome, string $apelido, string $email, string $senhaPlana): int
    {
        $hash = password_hash($senhaPlana, PASSWORD_DEFAULT);

        self::execute(
            'INSERT INTO usuarios (nome, apelido, email, senha, perfil, status) VALUES (?, ?, ?, ?, ?, ?)',
            [
                trim($nome),
                trim($apelido),
                mb_strtolower(trim($email)),
                $hash,
                'jogador',
                'ativo'
            ]
        );

        return self::lastId();
    }

    public static function atualizarSenha(int $id, string $novaSenhaPlana): bool
    {
        $hash = password_hash($novaSenhaPlana, PASSWORD_DEFAULT);
        return self::execute('UPDATE usuarios SET senha = ? WHERE id = ?', [$hash, $id]) > 0;
    }
}