<?php
namespace App\Core;

use App\Models\Usuario;

/**
 * Controller base: renderização de views, redirecionamentos,
 * proteção de rotas e controle de acesso por perfil.
 */
class Controller
{
    private ?array $usuarioCache = null;

    /** Renderiza uma view dentro de um layout. */
    protected function render(string $view, array $dados = [], string $layout = 'app'): void
    {
        $dados['usuario'] = $dados['usuario'] ?? $this->usuarioLogado();
        $dados['flashes'] = pull_flash();
        extract($dados, EXTR_SKIP);

        ob_start();
        require BASE_PATH . '/app/Views/' . $view . '.php';
        $conteudo = ob_get_clean();

        require BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
    }

    public function renderizarErro(int $codigo): void
    {
        $this->render('errors/erro', ['codigo' => $codigo], 'auth');
    }

    protected function redirect(string $caminho): void
    {
        header('Location: ' . url($caminho));
        exit;
    }

    protected function json(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** A requisição espera JSON? (usada pelo JavaScript via fetch). */
    public static function querJson(): bool
    {
        return stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
    }

    /** Lê um campo POST como texto aparado. */
    protected function post(string $campo, string $padrao = ''): string
    {
        $valor = $_POST[$campo] ?? $padrao;
        return is_string($valor) ? trim($valor) : $padrao;
    }

    /** Lê um campo GET como texto aparado. */
    protected function get(string $campo, string $padrao = ''): string
    {
        $valor = $_GET[$campo] ?? $padrao;
        return is_string($valor) ? trim($valor) : $padrao;
    }

    /** Usuário logado (sempre relido do banco para refletir bloqueios e mudanças de perfil). */
    protected function usuarioLogado(): ?array
    {
        if ($this->usuarioCache !== null) {
            return $this->usuarioCache;
        }
        $id = $_SESSION['usuario_id'] ?? null;
        if (!$id) {
            return null;
        }
        $usuario = (new Usuario())->buscarPorId((int) $id);
        if (!$usuario || $usuario['status'] !== 'ativo') {
            return null;
        }
        unset($usuario['senha']);
        return $this->usuarioCache = $usuario;
    }

    /** Exige login. Sem sessão válida, manda para a tela de login. */
    protected function exigirLogin(): array
    {
        $usuario = $this->usuarioLogado();
        if (!$usuario) {
            if (!empty($_SESSION['usuario_id'])) {
                unset($_SESSION['usuario_id']);
                flash('erro', 'Sua conta está desativada ou não existe mais.');
            }
            if (self::querJson() && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->json(['ok' => false, 'mensagem' => 'Faça login novamente.'], 401);
            }
            $this->redirect('/login');
        }
        return $usuario;
    }

    /** Exige perfil de administrador (validado no servidor a cada requisição). */
    protected function exigirAdmin(): array
    {
        $usuario = $this->exigirLogin();
        if ($usuario['perfil'] !== 'admin') {
            http_response_code(403);
            $this->render('errors/erro', ['codigo' => 403], 'auth');
            exit;
        }
        return $usuario;
    }

    /** Impede usuários logados de verem telas de visitante. */
    protected function apenasVisitante(): void
    {
        if ($this->usuarioLogado()) {
            $this->redirect('/inicio');
        }
    }
}
