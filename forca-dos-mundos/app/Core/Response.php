<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $dados, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function abort(int $codigo, string $mensagem = '', ?string $detalhe = null): never
    {
        $titulos = [
            403 => 'Acesso negado', 404 => 'Pagina nao encontrada', 405 => 'Metodo nao permitido',
            419 => 'Sessao expirada', 500 => 'Erro interno',
        ];
        $titulo = $titulos[$codigo] ?? 'Erro';

        if (Request::isAjax() && Request::method() !== 'GET') {
            self::json(['ok' => false, 'erro' => $mensagem ?: $titulo], $codigo);
        }

        http_response_code($codigo);
        $view = VIEW_PATH . '/errors/error.php';
        if (is_file($view)) {
            require $view;   // usa $codigo, $titulo, $mensagem, $detalhe
        } else {
            echo '<h1>' . e($codigo . ' - ' . $titulo) . '</h1><p>' . e($mensagem) . '</p>';
        }
        exit;
    }
}
