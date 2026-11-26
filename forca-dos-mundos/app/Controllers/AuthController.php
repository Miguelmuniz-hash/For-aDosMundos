<?php
namespace App\Controllers;

use App\Models\VerificacaoEmail;
use App\Services\Email;
use App\Core\Controller;
use App\Core\Database;
use App\Models\RecuperacaoSenha;
use App\Models\TentativaLogin;
use App\Models\Usuario;

/**
 * Cadastro, login, logout e recuperação de senha.
 */
class AuthController extends Controller
{
    /** Página raiz: manda cada um para o lugar certo. */
    public function inicio(): void
    {
        $this->redirect($this->usuarioLogado() ? '/inicio' : '/login');
    }

    // ---------------------------------------------------------------- LOGIN
    public function login(): void
    {
        $this->apenasVisitante();
        $this->render('auth/login', ['titulo' => 'Entrar', 'linkDev' => $this->linkDev()], 'auth');
    }

    public function autenticar(): void
    {
        $this->apenasVisitante();
        $email = mb_strtolower($this->post('email'));
        $senha = $_POST['senha'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $tentativas = new TentativaLogin();
        if ($tentativas->bloqueado($email, $ip)) {
            flash_form(['geral' => 'Muitas tentativas de acesso. Aguarde ' . TentativaLogin::janelaMinutos() . ' minutos e tente de novo.'], ['email' => $email]);
            $this->redirect('/login');
        }

        $modelo = new Usuario();
        $usuario = $modelo->buscarPorEmail($email);

        // Hash falso: mantém o tempo de resposta parecido quando o e-mail não existe.
        $hash = $usuario['senha'] ?? '$2y$10$usesomesillystringforsaltuMQ0HLOCOFbT1c7tZxlqPhIuQGTWi';
        $senhaConfere = password_verify((string) $senha, $hash);

        if (!$usuario || !$senhaConfere) {
            $tentativas->registrar($email, $ip);
            flash_form(['geral' => 'E-mail ou senha incorretos.'], ['email' => $email]);
            $this->redirect('/login');
        }

        if ($usuario['status'] !== 'ativo') {
            flash_form(['geral' => 'Esta conta está desativada. Procure um administrador.'], ['email' => $email]);
            $this->redirect('/login');
        }

        if (empty($usuario['email_verificado_em'])) {
            flash_form(['geral' => 'Confirme seu e-mail antes de entrar. Procure a mensagem na sua caixa de entrada (ou no spam). Não recebeu? Peça um novo e-mail em "Reenviar confirmação" abaixo.'], ['email' => $email]);
            $this->redirect('/login');
        }

        // Atualiza o hash se o algoritmo padrão do PHP mudou.
        if (password_needs_rehash($usuario['senha'], PASSWORD_DEFAULT)) {
            $modelo->atualizarSenha((int) $usuario['id'], password_hash((string) $senha, PASSWORD_DEFAULT));
        }

        $tentativas->limpar($email, $ip);
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        unset($_SESSION['jogo'], $_SESSION['resultado']);

        flash('sucesso', 'Bem-vindo de volta, ' . $usuario['apelido'] . '!');
        $this->redirect('/inicio');
    }

    public function sair(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        // Nova sessão apenas para exibir a mensagem de despedida.
        session_start();
        session_regenerate_id(true);
        flash('info', 'Você saiu da sua conta. Até a próxima exploração!');
        $this->redirect('/login');
    }

    // ------------------------------------------------------------- CADASTRO
    public function cadastro(): void
    {
        $this->apenasVisitante();
        $this->render('auth/cadastro', ['titulo' => 'Criar conta'], 'auth');
    }

    public function registrar(): void
    {
        $this->apenasVisitante();
        $dados = [
            'nome'    => $this->post('nome'),
            'apelido' => $this->post('apelido'),
            'email'   => mb_strtolower($this->post('email')),
        ];
        $senha = (string) ($_POST['senha'] ?? '');
        $confirmacao = (string) ($_POST['senha_confirmacao'] ?? '');

        $modelo = new Usuario();
        $erros = $this->validarPerfil($dados, $modelo, 0);

        if (strlen($senha) < 6) {
            $erros['senha'] = 'A senha deve ter no mínimo 6 caracteres.';
        }
        if ($senha !== $confirmacao) {
            $erros['senha_confirmacao'] = 'As senhas não conferem.';
        }

        if ($erros) {
            flash_form($erros, $dados);
            $this->redirect('/cadastro');
        }

        $id = $modelo->criar($dados['nome'], $dados['apelido'], $dados['email'], password_hash($senha, PASSWORD_DEFAULT));
        $enviado = $this->enviarConfirmacao(['id' => $id, 'apelido' => $dados['apelido'], 'email' => $dados['email']]);

        if ($enviado) {
            flash('sucesso', 'Conta criada! Enviamos um e-mail de confirmação para ' . $dados['email'] . '. Clique no link para ativar sua conta.');
        } else {
            flash('erro', 'Conta criada, mas não conseguimos enviar o e-mail de confirmação agora. Use "Reenviar confirmação" para tentar de novo.');
        }
        $this->redirect('/login');
    }

    /** Validação compartilhada (nome, apelido e e-mail). Retorna os erros por campo. */
    public static function validarPerfil(array $dados, Usuario $modelo, int $idAtual): array
    {
        $erros = [];

        if (mb_strlen($dados['nome']) < 3 || mb_strlen($dados['nome']) > 120) {
            $erros['nome'] = 'Informe o nome completo (3 a 120 caracteres).';
        }

        if ($dados['apelido'] === '') {
            $erros['apelido'] = 'Informe um apelido.';
        } elseif (!preg_match('/^[\p{L}\p{N}_.-]{3,30}$/u', $dados['apelido'])) {
            $erros['apelido'] = 'O apelido deve ter de 3 a 30 caracteres (letras, números, _ . -).';
        } elseif ($modelo->apelidoExiste($dados['apelido'], $idAtual)) {
            $erros['apelido'] = 'Este apelido já está em uso.';
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($dados['email']) > 160) {
            $erros['email'] = 'Informe um e-mail válido.';
        } elseif ($modelo->emailExiste($dados['email'], $idAtual)) {
            $erros['email'] = 'Este e-mail já está cadastrado.';
        }

        return $erros;
    }

    // ------------------------------------------------- CONFIRMAR CADASTRO

    /**
     * Pega (e apaga) o link de desenvolvimento guardado na sessão.
     * Só existe com 'ambiente' => 'local' e quando o e-mail não pôde ser enviado.
     */
    private function linkDev(): ?string
    {
        if (Database::config('ambiente') !== 'local' || empty($_SESSION['dev_link'])) {
            return null;
        }
        $link = (string) $_SESSION['dev_link'];
        unset($_SESSION['dev_link']);
        return $link;
    }

    /** Guarda o link na sessão para exibi-lo na tela (somente em ambiente local). */
    private function guardarLinkDev(string $link): void
    {
        if (Database::config('ambiente') === 'local') {
            $_SESSION['dev_link'] = $link;
        }
    }

    private function enviarConfirmacao(array $usuario): bool
    {
        $token = (new VerificacaoEmail())->criar((int) $usuario['id'], 1440);
        $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $link = $esquema . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('/confirmar-email') . '?token=' . $token;

        $apelido = htmlspecialchars($usuario['apelido'], ENT_QUOTES, 'UTF-8');
        $linkSeguro = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        $html = "<p>Olá, <strong>{$apelido}</strong>! Bem-vindo à Forca dos Mundos.</p>
                 <p>Para ativar sua conta, confirme seu e-mail (o link vale por 24 horas):</p>
                 <p><a href=\"{$linkSeguro}\" style=\"background:#7c5cff;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;display:inline-block\">Confirmar cadastro</a></p>
                 <p>Se o botão não funcionar, copie este endereço:<br>{$linkSeguro}</p>
                 <p>Se você não criou esta conta, ignore este e-mail.</p>";

        $texto = "Olá, {$usuario['apelido']}! Bem-vindo à Forca dos Mundos.\n\nPara ativar sua conta, acesse (válido por 24 horas):\n$link\n\nSe você não criou esta conta, ignore este e-mail.";

        $enviado = Email::enviar($usuario['email'], $usuario['apelido'], 'Forca dos Mundos - Confirme seu cadastro', $html, $texto);
        if (!$enviado) {
            $this->guardarLinkDev($link);
        }
        return $enviado;
    }

    public function confirmarEmail(): void
    {
        $usuarioId = (new VerificacaoEmail())->confirmar($this->get('token'));

        if ($usuarioId === null) {
            flash('erro', 'Link inválido ou vencido. Peça um novo e-mail de confirmação.');
            $this->redirect('/reenviar-confirmacao');
        }

        // Entra direto na conta, igual ao login.
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuarioId;
        unset($_SESSION['jogo'], $_SESSION['resultado']);

        flash('sucesso', 'E-mail confirmado! Bem-vindo à Forca dos Mundos.');
        $this->redirect('/inicio');
    }

    public function reenviar(): void
    {
        $this->apenasVisitante();
        $this->render('auth/reenviar', ['titulo' => 'Reenviar confirmação', 'linkDev' => $this->linkDev()], 'auth');
    }

    public function enviarReenvio(): void
    {
        $this->apenasVisitante();
        $email = mb_strtolower($this->post('email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_form(['email' => 'Informe um e-mail válido.'], ['email' => $email]);
            $this->redirect('/reenviar-confirmacao');
        }

        $usuario = (new Usuario())->buscarPorEmail($email);
        if ($usuario && $usuario['status'] === 'ativo' && empty($usuario['email_verificado_em'])) {
            $this->enviarConfirmacao($usuario);
        }

        // Mesma resposta exista ou não a conta (não revela e-mails cadastrados).
        flash('info', 'Se este e-mail estiver cadastrado e ainda não confirmado, enviamos um novo link.');
        $this->redirect('/reenviar-confirmacao');
    }

    public function esqueci(): void
    {
        $this->apenasVisitante();
        $this->render('auth/esqueci', ['titulo' => 'Recuperar senha', 'linkDev' => $this->linkDev()], 'auth');
    }

    public function enviarRecuperacao(): void
    {
        $this->apenasVisitante();
        $email = mb_strtolower($this->post('email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_form(['email' => 'Informe um e-mail válido.'], ['email' => $email]);
            $this->redirect('/esqueci-senha');
        }

        $usuario = (new Usuario())->buscarPorEmail($email);
        if ($usuario && $usuario['status'] === 'ativo') {
            $token = (new RecuperacaoSenha())->criar((int) $usuario['id'], 30);
            $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $link = $esquema . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('/redefinir-senha') . '?token=' . $token;
            $this->entregarLink($usuario, $link);
        }

        // Mesma resposta exista ou não a conta (evita descobrir e-mails cadastrados).
        flash('info', 'Se este e-mail estiver cadastrado, enviaremos um link para redefinir a senha (válido por 30 minutos).');
        $this->redirect('/esqueci-senha');
    }

    private function entregarLink(array $usuario, string $link): void
    {
        $apelido = htmlspecialchars($usuario['apelido'], ENT_QUOTES, 'UTF-8');
        $linkSeguro = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        $html = "<p>Olá, <strong>{$apelido}</strong>!</p>
                 <p>Clique no botão para criar uma nova senha (válido por 30 minutos):</p>
                 <p><a href=\"{$linkSeguro}\" style=\"background:#7c5cff;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;display:inline-block\">Redefinir senha</a></p>
                 <p>Se o botão não funcionar, copie este endereço:<br>{$linkSeguro}</p>
                 <p>Se não foi você, ignore este e-mail.</p>";

        $texto = "Olá, {$usuario['apelido']}!\n\nPara criar uma nova senha acesse (válido por 30 minutos):\n$link\n\nSe não foi você, ignore este e-mail.";

        $enviado = Email::enviar($usuario['email'], $usuario['apelido'], 'Forca dos Mundos - Recuperação de senha', $html, $texto);
        if (!$enviado) {
            $this->guardarLinkDev($link);
        }
    }

    public function redefinir(): void
    {
        $this->apenasVisitante();
        $token = $this->get('token');
        $valido = (new RecuperacaoSenha())->buscarValido($token) !== null;
        $this->render('auth/redefinir', ['titulo' => 'Nova senha', 'token' => $token, 'valido' => $valido], 'auth');
    }

    public function salvarRedefinicao(): void
    {
        $this->apenasVisitante();
        $token = $this->post('token');
        $senha = (string) ($_POST['senha'] ?? '');
        $confirmacao = (string) ($_POST['senha_confirmacao'] ?? '');

        $modelo = new RecuperacaoSenha();
        $registro = $modelo->buscarValido($token);
        if (!$registro) {
            flash('erro', 'Este link é inválido ou expirou. Solicite um novo.');
            $this->redirect('/esqueci-senha');
        }

        $erros = [];
        if (strlen($senha) < 6) {
            $erros['senha'] = 'A senha deve ter no mínimo 6 caracteres.';
        }
        if ($senha !== $confirmacao) {
            $erros['senha_confirmacao'] = 'As senhas não conferem.';
        }
        if ($erros) {
            flash_form($erros);
            $this->redirect('/redefinir-senha?token=' . urlencode($token));
        }

        if (!$modelo->redefinirSenha($registro, password_hash($senha, PASSWORD_DEFAULT))) {
            flash('erro', 'Este link já foi utilizado.');
            $this->redirect('/esqueci-senha');
        }

        flash('sucesso', 'Senha alterada! Entre com a nova senha.');
        $this->redirect('/login');
    }
}
