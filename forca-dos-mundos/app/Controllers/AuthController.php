<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\RecuperacaoSenha;
use App\Models\Usuario;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->view('auth/login', ['titulo' => 'Entrar - Força dos Mundos']);
    }

    public function login(): void
    {
        Csrf::verifyOrFail();

        $email = $this->post('email');
        $senha = $_POST['senha'] ?? '';

        if ($email === '' || $senha === '') {
            $this->erro('Preencha o e-mail e a senha.');
            $this->voltar('login');
        }

        $usuario = Usuario::findByEmail($email);

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            $this->erro('E-mail ou senha inválidos.');
            $this->voltar('login');
        }

        if ($usuario['status'] !== 'ativo') {
            $this->erro('Sua conta está inativa. Entre em contato com o administrador.');
            $this->voltar('login');
        }

        Auth::login($usuario);
        $this->sucesso('Bem-vindo de volta, ' . $usuario['apelido'] . '!');
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        Csrf::verifyOrFail();
        Auth::logout();
        redirect('login');
    }

    public function registerForm(): void
    {
        $this->view('auth/cadastro', ['titulo' => 'Criar Conta - Força dos Mundos']);
    }

    public function register(): void
    {
        Csrf::verifyOrFail();

        $nome           = $this->post('nome');
        $apelido        = $this->post('apelido');
        $email          = $this->post('email');
        $senha          = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        $erros = [];

        if (mb_strlen($nome) < 3) {
            $erros['nome'] = 'O nome deve ter pelo menos 3 caracteres.';
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $apelido)) {
            $erros['apelido'] = 'O apelido deve ter de 3 a 30 caracteres (letras, números ou _).';
        } elseif (Usuario::findByApelido($apelido)) {
            $erros['apelido'] = 'Este apelido já está em uso.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido.';
        } elseif (Usuario::findByEmail($email)) {
            $erros['email'] = 'Este e-mail já está cadastrado.';
        }

        if (strlen($senha) < 6) {
            $erros['senha'] = 'A senha deve ter no mínimo 6 caracteres.';
        } elseif ($senha !== $confirmarSenha) {
            $erros['confirmar_senha'] = 'As senhas não coincidem.';
        }

        if (!empty($erros)) {
            $this->erro('Corrija os erros abaixo para concluir o cadastro.');
            $this->voltar('cadastro', $erros);
        }

        $novoId = Usuario::criar($nome, $apelido, $email, $senha);
        $usuario = Usuario::find($novoId);

        if ($usuario) {
            Auth::login($usuario);
            $this->sucesso('Conta criada com sucesso! Bom jogo!');
            $this->redirect('dashboard');
        }

        $this->sucesso('Cadastro realizado! Faça seu login.');
        $this->redirect('login');
    }

    public function forgotForm(): void
    {
        $this->view('auth/esqueci-senha', ['titulo' => 'Recuperar Senha - Força dos Mundos']);
    }

    public function forgot(): void
    {
        Csrf::verifyOrFail();

        $email = $this->post('email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->erro('Informe um e-mail válido.');
            $this->voltar('esqueci-senha');
        }

        $usuario = Usuario::findByEmail($email);

        if ($usuario && $usuario['status'] === 'ativo') {
            $token = RecuperacaoSenha::criarToken((int) $usuario['id']);
            $linkRecuperacao = url('redefinir-senha/' . $token);
            Session::flash('link_recuperacao', $linkRecuperacao);
        }

        $this->sucesso('Se o e-mail estiver cadastrado, um link de redefinição foi gerado.');
        $this->redirect('esqueci-senha');
    }

    public function resetForm(string $token): void
    {
        $registro = RecuperacaoSenha::validarToken($token);

        if (!$registro) {
            $this->erro('Link de recuperação inválido ou expirado. Solicite um novo.');
            $this->redirect('esqueci-senha');
        }

        $this->view('auth/redefinir-senha', [
            'titulo' => 'Redefinir Senha - Força dos Mundos',
            'token'  => $token
        ]);
    }

    public function reset(): void
    {
        Csrf::verifyOrFail();

        $token          = $this->post('token');
        $senha          = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        $registro = RecuperacaoSenha::validarToken($token);

        if (!$registro) {
            $this->erro('Link de recuperação inválido ou expirado.');
            $this->redirect('esqueci-senha');
        }

        if (strlen($senha) < 6) {
            $this->erro('A nova senha deve ter no mínimo 6 caracteres.');
            $this->voltar('redefinir-senha/' . $token);
        }

        if ($senha !== $confirmarSenha) {
            $this->erro('As senhas não coincidem.');
            $this->voltar('redefinir-senha/' . $token);
        }

        Usuario::atualizarSenha((int) $registro['usuario_id'], $senha);
        RecuperacaoSenha::marcarComoUsado((int) $registro['id']);

        $this->sucesso('Senha redefinida com sucesso! Faça login com sua nova senha.');
        $this->redirect('login');
    }
}