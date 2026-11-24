<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /** Renderiza uma view dentro de um layout. $view e relativo a app/Views (sem .php). */
    protected function view(string $view, array $dados = [], ?string $layout = 'layouts/main'): void
    {
        $arquivo = VIEW_PATH . '/' . $view . '.php';
        if (!is_file($arquivo)) {
            Response::abort(500, 'View nao encontrada.', Config::get('app.debug') ? $view : null);
        }

        $render = static function (string $__arquivo, array $__dados): string {
            extract($__dados, EXTR_SKIP);
            ob_start();
            require $__arquivo;
            return (string) ob_get_clean();
        };

        header('Content-Type: text/html; charset=utf-8');
        $conteudo = $render($arquivo, $dados);
        if ($layout === null) {
            echo $conteudo;
            return;
        }
        $arquivoLayout = VIEW_PATH . '/' . $layout . '.php';
        if (!is_file($arquivoLayout)) {
            Response::abort(500, 'Layout nao encontrado.', Config::get('app.debug') ? $layout : null);
        }
        echo $render($arquivoLayout, $dados + ['conteudo' => $conteudo]);
    }

    protected function redirect(string $caminho): never
    {
        redirect($caminho);
    }

    protected function json(array $dados, int $status = 200): never
    {
        Response::json($dados, $status);
    }

    /** Valor de $_POST (texto aparado) ou padrao. */
    protected function post(string $campo, string $padrao = ''): string
    {
        $v = $_POST[$campo] ?? $padrao;
        return is_string($v) ? trim($v) : $padrao;
    }

    protected function query(string $campo, string $padrao = ''): string
    {
        $v = $_GET[$campo] ?? $padrao;
        return is_string($v) ? trim($v) : $padrao;
    }

    protected function sucesso(string $mensagem): void
    {
        Session::flash('sucesso', $mensagem);
    }

    protected function erro(string $mensagem): void
    {
        Session::flash('erro', $mensagem);
    }

    /** Volta ao formulario com erros e valores antigos (senhas nunca sao reenviadas). */
    protected function voltar(string $caminho, array $erros = [], bool $manterCampos = true): never
    {
        Session::flash('_errors', $erros);
        if ($manterCampos) {
            $old = $_POST;
            foreach (['senha', 'senha_atual', 'nova_senha', 'confirmar_senha', 'senha_confirmacao', 'confirmar_nova_senha'] as $campoSensivel) {
                unset($old[$campoSensivel]);
            }
            Session::flash('_old', $old);
        }
        redirect($caminho);
    }

    protected function abort(int $codigo, string $mensagem = ''): never
    {
        Response::abort($codigo, $mensagem);
    }
}
