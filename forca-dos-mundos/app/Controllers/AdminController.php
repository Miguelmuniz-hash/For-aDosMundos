<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Upload;
use App\Models\Categoria;
use App\Models\Palavra;
use App\Models\Partida;
use App\Models\Usuario;
use App\Services\ForcaJogo;

/**
 * Painel administrativo. Todas as ações chamam exigirAdmin(),
 * ou seja, a autorização é validada no servidor (não depende de botões escondidos).
 */
class AdminController extends Controller
{
    // ------------------------------------------------------------ DASHBOARD
    public function dashboard(): void
    {
        $this->exigirAdmin();
        $partidas = new Partida();
        $categorias = new Categoria();

        $this->render('admin/dashboard', [
            'titulo'         => 'Visão geral',
            'pagina'         => 'admin',
            'usuarios'       => (new Usuario())->totais(),
            'partidas'       => $partidas->totais(),
            'catResumo'      => $categorias->resumo(),
            'palResumo'      => (new Palavra())->resumo(),
            'semPalavras'    => $categorias->ativasSemPalavras(),
            'ultimas'        => $partidas->ultimasGeral(6),
            'topJogadores'   => $partidas->topJogadores(5),
            'mundos'         => $partidas->desempenhoPorCategoria(6),
            'porDia'         => $partidas->partidasPorDia(7),
            'porErros'       => $partidas->vitoriasPorErros(ForcaJogo::MAX_ERROS),
            'novosUsuarios'  => (new Usuario())->recentes(5),
        ], 'admin');
    }

    // ----------------------------------------------------------- CATEGORIAS
    public function categorias(): void
    {
        $this->exigirAdmin();
        $busca = $this->get('busca');
        $modelo = new Categoria();
        $pag = paginar($modelo->contarFiltradas($busca), (int) $this->get('pagina', '1'), 8);

        $this->render('admin/categorias', [
            'titulo'     => 'Categorias',
            'pagina'     => 'categorias',
            'busca'      => $busca,
            'pag'        => $pag,
            'resumo'     => $modelo->resumo(),
            'categorias' => $modelo->listarFiltradas($busca, $pag['por_pagina'], $pag['offset']),
        ], 'admin');
    }

    public function salvarCategoria(): void
    {
        $this->exigirAdmin();
        $id = (int) $this->post('id');
        $nome = $this->post('nome');
        $descricao = $this->post('descricao');
        $icone = $this->post('icone', 'planeta');
        $status = $this->post('status') === 'inativo' ? 'inativo' : 'ativo';
        $modelo = new Categoria();
        $atual = $id > 0 ? $modelo->buscarPorId($id) : null;

        if ($id > 0 && !$atual) {
            flash('erro', 'Categoria não encontrada.');
            $this->redirect('/admin/categorias');
        }

        $erros = [];
        if ($nome === '' || mb_strlen($nome) > 60) {
            $erros[] = 'O nome da categoria é obrigatório (até 60 caracteres).';
        } elseif ($modelo->nomeExiste($nome, $id)) {
            $erros[] = 'Já existe uma categoria com esse nome.';
        }
        if (mb_strlen($descricao) > 255) {
            $erros[] = 'A descrição deve ter até 255 caracteres.';
        }
        if (!array_key_exists($icone, icones_disponiveis())) {
            $icone = 'planeta';
        }

        // Imagem opcional (se enviada, precisa ser válida).
        $novaImagem = null;
        $enviou = isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE;
        if (!$erros && $enviou) {
            [$novaImagem, $erroUpload] = Upload::imagem('imagem', 'categorias');
            if ($erroUpload) {
                $erros[] = $erroUpload;
            }
        }

        if ($erros) {
            flash('erro', implode(' ', $erros));
            $this->redirect('/admin/categorias');
        }

        if ($id > 0) {
            $modelo->atualizar($id, $nome, $descricao, $icone, $status);
            if ($novaImagem) {
                Upload::remover($atual['imagem'], 'categorias');
                $modelo->atualizarImagem($id, $novaImagem);
            } elseif ($this->post('remover_imagem') === '1') {
                Upload::remover($atual['imagem'], 'categorias');
                $modelo->atualizarImagem($id, null);
            }
            flash('sucesso', 'Categoria atualizada!');
        } else {
            $modelo->criar($nome, $descricao, $icone, $status, $novaImagem);
            flash('sucesso', 'Categoria cadastrada!');
        }
        $this->redirect('/admin/categorias');
    }

    public function statusCategoria(): void
    {
        $this->exigirAdmin();
        $modelo = new Categoria();
        $categoria = $modelo->buscarPorId((int) $this->post('id'));
        if (!$categoria) {
            flash('erro', 'Categoria não encontrada.');
            $this->redirect('/admin/categorias');
        }
        $novo = $categoria['status'] === 'ativo' ? 'inativo' : 'ativo';
        $modelo->definirStatus((int) $categoria['id'], $novo);
        flash('sucesso', 'Categoria ' . ($novo === 'ativo' ? 'ativada' : 'desativada') . '.');
        $this->redirect('/admin/categorias');
    }

    public function excluirCategoria(): void
    {
        $this->exigirAdmin();
        $modelo = new Categoria();
        $categoria = $modelo->buscarPorId((int) $this->post('id'));
        if (!$categoria) {
            flash('erro', 'Categoria não encontrada.');
            $this->redirect('/admin/categorias');
        }
        // Integridade: não exclui se ainda existem palavras nela.
        if ($modelo->contarPalavras((int) $categoria['id']) > 0) {
            flash('erro', 'Não é possível excluir: a categoria ainda possui palavras. Remova-as ou desative a categoria.');
            $this->redirect('/admin/categorias');
        }
        Upload::remover($categoria['imagem'], 'categorias');
        $modelo->excluir((int) $categoria['id']);
        flash('sucesso', 'Categoria excluída.');
        $this->redirect('/admin/categorias');
    }

    // -------------------------------------------------------------- PALAVRAS
    public function palavras(): void
    {
        $this->exigirAdmin();
        $busca = $this->get('busca');
        $categoriaId = (int) $this->get('categoria');
        $modelo = new Palavra();
        $pag = paginar($modelo->contarFiltradas($busca, $categoriaId), (int) $this->get('pagina', '1'), 10);

        $this->render('admin/palavras', [
            'titulo'      => 'Palavras',
            'pagina'      => 'palavras',
            'busca'       => $busca,
            'categoriaId' => $categoriaId,
            'pag'         => $pag,
            'palavras'    => $modelo->listarFiltradas($busca, $categoriaId, $pag['por_pagina'], $pag['offset']),
            'resumo'      => $modelo->resumo(),
            'categorias'  => (new Categoria())->todasParaSelect(),
        ], 'admin');
    }

    public function salvarPalavra(): void
    {
        $this->exigirAdmin();
        $id = (int) $this->post('id');
        $categoriaId = (int) $this->post('categoria_id');
        $palavra = strtoupper($this->post('palavra'));
        $dica = $this->post('dica');
        $modelo = new Palavra();

        $erros = [];
        if (!(new Categoria())->buscarPorId($categoriaId)) {
            $erros[] = 'Selecione uma categoria válida.';
        }
        if ($palavra === '') {
            $erros[] = 'A palavra é obrigatória.';
        } elseif (!preg_match('/^[A-Z]{3,30}$/', $palavra)) {
            $erros[] = 'A palavra deve ter de 3 a 30 letras de A a Z, sem acentos, espaços ou símbolos.';
        } elseif ($modelo->existe($palavra, $id)) {
            $erros[] = 'Essa palavra já está cadastrada.';
        }
        if ($dica === '' || mb_strlen($dica) > 200) {
            $erros[] = 'A dica é obrigatória (até 200 caracteres).';
        }
        if ($id > 0 && !$modelo->buscarPorId($id)) {
            $erros[] = 'Palavra não encontrada.';
        }

        if ($erros) {
            flash('erro', implode(' ', $erros));
            $this->redirect('/admin/palavras');
        }

        if ($id > 0) {
            $modelo->atualizar($id, $categoriaId, $palavra, $dica);
            flash('sucesso', 'Palavra atualizada!');
        } else {
            $modelo->criar($categoriaId, $palavra, $dica);
            flash('sucesso', 'Palavra cadastrada!');
        }
        $this->redirect('/admin/palavras?categoria=' . $categoriaId);
    }

    public function excluirPalavra(): void
    {
        $this->exigirAdmin();
        $modelo = new Palavra();
        $id = (int) $this->post('id');
        if (!$modelo->buscarPorId($id)) {
            flash('erro', 'Palavra não encontrada.');
            $this->redirect('/admin/palavras');
        }
        $modelo->excluir($id);
        flash('sucesso', 'Palavra excluída.');
        $this->redirect('/admin/palavras');
    }

    // -------------------------------------------------------------- USUÁRIOS
    public function usuarios(): void
    {
        $admin = $this->exigirAdmin();
        $busca = $this->get('busca');
        $perfil = $this->get('perfil');
        $status = $this->get('status');
        $modelo = new Usuario();
        $pag = paginar($modelo->contarFiltrados($busca, $perfil, $status), (int) $this->get('pagina', '1'), 10);

        $this->render('admin/usuarios', [
            'titulo'   => 'Usuários',
            'pagina'   => 'usuarios',
            'busca'    => $busca,
            'perfil'   => $perfil,
            'status'   => $status,
            'pag'      => $pag,
            'resumo'   => $modelo->totais(),
            'lista'    => $modelo->listarFiltrados($busca, $perfil, $status, $pag['por_pagina'], $pag['offset']),
            'adminId'  => (int) $admin['id'],
        ], 'admin');
    }

    public function salvarUsuario(): void
    {
        $this->exigirAdmin();
        $id = (int) $this->post('id');
        $modelo = new Usuario();
        $alvo = $modelo->buscarPorId($id);
        if (!$alvo) {
            flash('erro', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
        }

        $dados = [
            'nome'    => $this->post('nome'),
            'apelido' => $this->post('apelido'),
            'email'   => mb_strtolower($this->post('email')),
        ];
        $perfil = $this->post('perfil') === 'admin' ? 'admin' : 'jogador';
        $erros = AuthController::validarPerfil($dados, $modelo, $id);

        // Proteção: nunca ficar sem administrador ativo.
        if ($alvo['perfil'] === 'admin' && $perfil !== 'admin' && $alvo['status'] === 'ativo' && $modelo->adminsAtivosExceto($id) === 0) {
            $erros['perfil'] = 'Este é o único administrador ativo e não pode virar jogador.';
        }

        if ($erros) {
            flash('erro', implode(' ', $erros));
            $this->redirect('/admin/usuarios');
        }

        $modelo->atualizarPeloAdmin($id, $dados['nome'], $dados['apelido'], $dados['email'], $perfil);
        flash('sucesso', 'Usuário atualizado!');
        $this->redirect('/admin/usuarios');
    }

    public function statusUsuario(): void
    {
        $this->exigirAdmin();
        $modelo = new Usuario();
        $alvo = $modelo->buscarPorId((int) $this->post('id'));
        if (!$alvo) {
            flash('erro', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
        }

        $novo = $alvo['status'] === 'ativo' ? 'inativo' : 'ativo';
        if ($novo === 'inativo' && $alvo['perfil'] === 'admin' && $modelo->adminsAtivosExceto((int) $alvo['id']) === 0) {
            flash('erro', 'Não é possível desativar o único administrador ativo.');
            $this->redirect('/admin/usuarios');
        }

        $modelo->definirStatus((int) $alvo['id'], $novo);
        flash('sucesso', 'Conta ' . ($novo === 'ativo' ? 'ativada' : 'desativada') . '.');
        $this->redirect('/admin/usuarios');
    }

    /** Confirma o e-mail de uma conta manualmente (útil quando o e-mail de confirmação não chegou). */
    public function verificarUsuario(): void
    {
        $this->exigirAdmin();
        $modelo = new Usuario();
        $alvo = $modelo->buscarPorId((int) $this->post('id'));
        if (!$alvo) {
            flash('erro', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
        }
        if (!empty($alvo['email_verificado_em'])) {
            flash('info', 'O e-mail de "' . $alvo['apelido'] . '" já estava confirmado.');
            $this->redirect('/admin/usuarios');
        }

        $modelo->verificarEmail((int) $alvo['id']);
        flash('sucesso', 'E-mail de "' . $alvo['apelido'] . '" confirmado. A pessoa já pode entrar.');
        $this->redirect('/admin/usuarios');
    }

    public function excluirUsuario(): void
    {
        $admin = $this->exigirAdmin();
        $modelo = new Usuario();
        $alvo = $modelo->buscarPorId((int) $this->post('id'));
        if (!$alvo) {
            flash('erro', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
        }
        if ((int) $alvo['id'] === (int) $admin['id']) {
            flash('erro', 'Para excluir a sua própria conta use a página "Meu perfil".');
            $this->redirect('/admin/usuarios');
        }
        if ($alvo['perfil'] === 'admin' && $alvo['status'] === 'ativo' && $modelo->adminsAtivosExceto((int) $alvo['id']) === 0) {
            flash('erro', 'Não é possível excluir o único administrador ativo.');
            $this->redirect('/admin/usuarios');
        }

        Upload::remover($alvo['foto'], 'perfis');
        $modelo->excluir((int) $alvo['id']);
        flash('sucesso', 'Conta excluída. As partidas dela foram mantidas de forma anônima.');
        $this->redirect('/admin/usuarios');
    }

    // --------------------------------------------------------------- PARTIDAS
    public function partidas(): void
    {
        $this->exigirAdmin();
        $busca = $this->get('busca');
        $de = $this->get('de');
        $ate = $this->get('ate');
        $resultado = $this->get('resultado');
        $modelo = new Partida();
        $pag = paginar($modelo->contarAdmin($busca, 0, $de, $ate, $resultado), (int) $this->get('pagina', '1'), 12);

        $this->render('admin/partidas', [
            'titulo'    => 'Partidas',
            'pagina'    => 'partidas',
            'busca'     => $busca,
            'de'        => $de,
            'ate'       => $ate,
            'resultado' => $resultado,
            'pag'       => $pag,
            'resumo'    => $modelo->resumoAdmin($busca, $de, $ate, $resultado),
            'maxErros'  => ForcaJogo::MAX_ERROS,
            'lista'     => $modelo->listarAdmin($busca, 0, $de, $ate, $resultado, $pag['por_pagina'], $pag['offset']),
        ], 'admin');
    }
}
