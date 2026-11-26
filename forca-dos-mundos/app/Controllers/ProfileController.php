<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Upload;
use App\Models\Partida;
use App\Models\Usuario;

/**
 * Meu perfil: dados, foto, senha, exclusão de conta e configurações.
 * Sempre atua somente sobre o usuário da sessão (nunca sobre um id vindo do formulário).
 */
class ProfileController extends Controller
{
    public function index(): void
    {
        $usuario = $this->exigirLogin();
        $this->render('profile/index', [
            'titulo' => 'Meu perfil',
            'pagina' => 'perfil',
            'stats'  => (new Partida())->estatisticasUsuario((int) $usuario['id']),
        ]);
    }

    public function salvarDados(): void
    {
        $usuario = $this->exigirLogin();
        $dados = [
            'nome'    => $this->post('nome'),
            'apelido' => $this->post('apelido'),
            'email'   => mb_strtolower($this->post('email')),
        ];
        $modelo = new Usuario();
        $erros = AuthController::validarPerfil($dados, $modelo, (int) $usuario['id']);

        if ($erros) {
            flash_form($erros, $dados);
            flash('erro', 'Corrija os campos destacados.');
            $this->redirect('/perfil');
        }

        $modelo->atualizarDados((int) $usuario['id'], $dados['nome'], $dados['apelido'], $dados['email']);
        flash('sucesso', 'Dados atualizados com sucesso!');
        $this->redirect('/perfil');
    }

    public function salvarFoto(): void
    {
        $usuario = $this->exigirLogin();
        [$nome, $erro] = Upload::imagem('foto', 'perfis');

        if ($erro) {
            flash('erro', $erro);
            $this->redirect('/perfil');
        }

        Upload::remover($usuario['foto'], 'perfis');
        (new Usuario())->atualizarFoto((int) $usuario['id'], $nome);
        flash('sucesso', 'Foto de perfil atualizada!');
        $this->redirect('/perfil');
    }

    public function removerFoto(): void
    {
        $usuario = $this->exigirLogin();
        Upload::remover($usuario['foto'], 'perfis');
        (new Usuario())->atualizarFoto((int) $usuario['id'], null);
        flash('info', 'Foto removida.');
        $this->redirect('/perfil');
    }

    public function senha(): void
    {
        $this->exigirLogin();
        $this->render('profile/senha', ['titulo' => 'Alterar senha', 'pagina' => 'perfil']);
    }

    public function salvarSenha(): void
    {
        $usuario = $this->exigirLogin();
        $atual = (string) ($_POST['senha_atual'] ?? '');
        $nova = (string) ($_POST['nova_senha'] ?? '');
        $confirmacao = (string) ($_POST['nova_senha_confirmacao'] ?? '');

        $modelo = new Usuario();
        $registro = $modelo->buscarPorId((int) $usuario['id']);
        $erros = [];

        if (!password_verify($atual, $registro['senha'])) {
            $erros['senha_atual'] = 'A senha atual está incorreta.';
        }
        if (strlen($nova) < 6) {
            $erros['nova_senha'] = 'A nova senha deve ter no mínimo 6 caracteres.';
        }
        if ($nova !== $confirmacao) {
            $erros['nova_senha_confirmacao'] = 'As senhas não conferem.';
        }
        if ($erros) {
            flash_form($erros);
            $this->redirect('/perfil/senha');
        }

        $modelo->atualizarSenha((int) $usuario['id'], password_hash($nova, PASSWORD_DEFAULT));
        session_regenerate_id(true);
        flash('sucesso', 'Senha alterada com sucesso!');
        $this->redirect('/perfil');
    }

    public function excluir(): void
    {
        $usuario = $this->exigirLogin();
        $modelo = new Usuario();
        $registro = $modelo->buscarPorId((int) $usuario['id']);

        if (!password_verify((string) ($_POST['senha'] ?? ''), $registro['senha'])) {
            flash('erro', 'Senha incorreta. A conta não foi excluída.');
            $this->redirect('/perfil');
        }

        // O sistema nunca pode ficar sem um administrador ativo.
        if ($usuario['perfil'] === 'admin' && $modelo->adminsAtivosExceto((int) $usuario['id']) === 0) {
            flash('erro', 'Você é o único administrador ativo. Promova outra conta antes de excluir esta.');
            $this->redirect('/perfil');
        }

        Upload::remover($usuario['foto'], 'perfis');
        $modelo->excluir((int) $usuario['id']); // partidas ficam anonimizadas (FK SET NULL)

        $_SESSION = [];
        session_destroy();
        session_start();
        session_regenerate_id(true);
        flash('info', 'Sua conta foi excluída. Sentiremos sua falta entre as estrelas.');
        $this->redirect('/login');
    }

    public function configuracoes(): void
    {
        $this->exigirLogin();
        $this->render('profile/configuracoes', ['titulo' => 'Configurações', 'pagina' => 'config']);
    }
}
