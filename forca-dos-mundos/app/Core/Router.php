<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $rotas = [];
    private array $middlewareGrupo = [];

    public function get(string $caminho, string $acao, array $middleware = []): void
    {
        $this->add('GET', $caminho, $acao, $middleware);
    }

    public function post(string $caminho, string $acao, array $middleware = []): void
    {
        $this->add('POST', $caminho, $acao, $middleware);
    }

    /** Agrupa rotas sob middlewares comuns: 'guest', 'auth' ou 'admin'. */
    public function group(array $middleware, callable $definir): void
    {
        $anterior = $this->middlewareGrupo;
        $this->middlewareGrupo = array_merge($anterior, $middleware);
        $definir($this);
        $this->middlewareGrupo = $anterior;
    }

    private function add(string $metodo, string $caminho, string $acao, array $middleware): void
    {
        // Parametros terminados em "id" (id, categoriaId...) aceitam so digitos; os demais (ex.: token) qualquer segmento.
        $regex = preg_replace_callback('#\{(\w+)\}#', static function (array $m): string {
            $padrao = preg_match('/id$/i', $m[1]) ? '\d+' : '[^/]+';
            return '(?P<' . $m[1] . '>' . $padrao . ')';
        }, rtrim($caminho, '/') ?: '/');
        $this->rotas[] = [
            'metodo'     => $metodo,
            'regex'      => '#^' . $regex . '$#',
            'acao'       => $acao,
            'middleware' => array_merge($this->middlewareGrupo, $middleware),
        ];
    }

    public function dispatch(): void
    {
        $metodo  = Request::method();
        $caminho = Request::path();
        $caminhoExiste = false;

        foreach ($this->rotas as $rota) {
            if (!preg_match($rota['regex'], $caminho, $m)) {
                continue;
            }
            $caminhoExiste = true;
            if ($rota['metodo'] !== $metodo) {
                continue;
            }

            // Toda requisicao que altera estado exige token CSRF valido.
            if ($metodo === 'POST') {
                Csrf::verifyOrFail();
            }
            foreach ($rota['middleware'] as $mw) {
                $this->executarMiddleware($mw);
            }

            $params = [];
            foreach ($m as $chave => $valor) {
                if (is_string($chave)) {
                    $valor = urldecode($valor);
                    $params[] = preg_match('/id$/i', $chave) ? (int) $valor : $valor;
                }
            }
            $this->executarAcao($rota['acao'], $params);
            return;
        }

        if ($caminhoExiste) {
            Response::abort(405, 'Metodo nao permitido para esta rota.');
        }
        Response::abort(404, 'A pagina que voce procura nao existe.');
    }

    private function executarMiddleware(string $nome): void
    {
        match ($nome) {
            'guest' => Auth::requireGuest(),
            'auth'  => Auth::requireLogin(),
            'admin' => Auth::requireAdmin(),
            default => throw new \InvalidArgumentException("Middleware desconhecido: $nome"),
        };
    }

    private function executarAcao(string $acao, array $params): void
    {
        [$classe, $metodo] = explode('@', $acao);
        $fqcn = 'App\\Controllers\\' . $classe;
        if (!class_exists($fqcn) || !method_exists($fqcn, $metodo)) {
            Response::abort(500, 'Controller ou metodo nao encontrado.', Config::get('app.debug') ? $acao : null);
        }
        (new $fqcn())->$metodo(...$params);
    }
}
