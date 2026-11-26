<?php
namespace App\Core;

/**
 * Roteador simples: associa método HTTP + caminho a "Controller@metodo".
 * Toda requisição POST passa pela verificação do token CSRF.
 */
class Router
{
    private array $rotas = ['GET' => [], 'POST' => []];

    public function get(string $caminho, string $destino): void
    {
        $this->rotas['GET'][$caminho] = $destino;
    }

    public function post(string $caminho, string $destino): void
    {
        $this->rotas['POST'][$caminho] = $destino;
    }

    /** Descobre o caminho da requisição, descontando a pasta do projeto. */
    private function caminhoAtual(): string
    {
        $caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $caminho = rawurldecode($caminho);
        $raiz = rtrim(str_replace('\\', '/', dirname(BASE_URL)), '/');

        if (BASE_URL !== '' && strpos($caminho, BASE_URL) === 0) {
            $caminho = substr($caminho, strlen(BASE_URL));
        } elseif ($raiz !== '' && strpos($caminho, $raiz) === 0) {
            $caminho = substr($caminho, strlen($raiz));
        }

        $caminho = '/' . trim($caminho, '/');
        return $caminho === '/index.php' ? '/' : $caminho;
    }

    public function dispatch(): void
    {
        $metodo = $_SERVER['REQUEST_METHOD'] === 'POST' ? 'POST' : 'GET';
        $caminho = $this->caminhoAtual();
        $destino = $this->rotas[$metodo][$caminho] ?? null;

        if ($destino === null) {
            http_response_code(404);
            (new Controller())->renderizarErro(404);
            return;
        }

        if ($metodo === 'POST') {
            $enviado = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if (!hash_equals($_SESSION['_csrf'] ?? '', (string) $enviado)) {
                http_response_code(419);
                if (Controller::querJson()) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => false, 'mensagem' => 'Sessão expirada. Recarregue a página.']);
                    return;
                }
                flash('erro', 'Sua sessão expirou. Tente novamente.');
                header('Location: ' . url('/'));
                return;
            }
        }

        [$classe, $acao] = explode('@', $destino);
        $classe = 'App\\Controllers\\' . $classe;
        $controller = new $classe();
        $controller->$acao();
    }
}
