<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private static ?array $usuario = null;
    private static bool $carregado = false;

    public static function id(): ?int
    {
        return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
    }

    /** Usuario logado (recarregado do banco a cada requisicao; conta inativa/removida derruba a sessao). */
    public static function user(): ?array
    {
        if (self::$carregado) {
            return self::$usuario;
        }
        self::$carregado = true;
        $id = self::id();
        if ($id === null) {
            return self::$usuario = null;
        }
        $st = Database::pdo()->prepare(
            'SELECT id, nome, apelido, email, foto, perfil, status, criado_em FROM usuarios WHERE id = ? LIMIT 1'
        );
        $st->execute([$id]);
        $u = $st->fetch();
        if (!$u || $u['status'] !== 'ativo') {
            unset($_SESSION['usuario_id']);
            return self::$usuario = null;
        }
        return self::$usuario = $u;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['perfil'] ?? null) === 'admin';
    }

    public static function login(array $usuario): void
    {
        session_regenerate_id(true);          // evita fixacao de sessao
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        Csrf::refresh();
        self::$carregado = false;
        self::$usuario = null;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$carregado = false;
        self::$usuario = null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Session::flash('erro', 'Faca login para continuar.');
            redirect('login');
        }
    }

    /** Bloqueio no back-end: so admin ativo acessa rotas administrativas. */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            Response::abort(403, 'Voce nao tem permissao para acessar a area administrativa.');
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            redirect('dashboard');
        }
    }
}
