<header class="admin-cabecalho">
    <div>
        <h1>Usuários</h1>
        <p>Jogadores e administradores. O sistema sempre mantém pelo menos um administrador ativo.</p>
    </div>
</header>

<section class="resumo-faixa">
    <div class="resumo-item tom-violeta"><strong><?= (int) $resumo['total'] ?></strong><span>usuários</span></div>
    <div class="resumo-item tom-ciano"><strong><?= (int) $resumo['jogadores'] ?></strong><span>jogadores</span></div>
    <div class="resumo-item tom-ouro"><strong><?= (int) $resumo['admins'] ?></strong><span>administradores</span></div>
    <div class="resumo-item tom-ok"><strong><?= (int) $resumo['ativos'] ?></strong><span>ativos</span></div>
    <div class="resumo-item"><strong><?= (int) $resumo['inativos'] ?></strong><span>desativados</span></div>
    <a href="<?= e(url('/admin/usuarios?status=pendente')) ?>" class="resumo-item <?= (int) $resumo['pendentes'] > 0 ? 'tom-ouro' : 'tom-ok' ?>"><strong><?= (int) $resumo['pendentes'] ?></strong><span>e-mails pendentes</span></a>
</section>

<div class="admin-barra">
    <form method="get" class="busca" role="search">
        <span class="busca-campo">
            <?= icone_admin('busca') ?>
            <input class="input" type="search" name="busca" value="<?= e($busca) ?>" placeholder="Nome, apelido ou e-mail..." aria-label="Pesquisar usuário">
        </span>
        <select class="input" name="perfil" aria-label="Filtrar por perfil">
            <option value="">Todos os perfis</option>
            <option value="jogador" <?= $perfil === 'jogador' ? 'selected' : '' ?>>Jogadores</option>
            <option value="admin" <?= $perfil === 'admin' ? 'selected' : '' ?>>Administradores</option>
        </select>
        <select class="input" name="status" aria-label="Filtrar por situação">
            <option value="">Qualquer situação</option>
            <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativos</option>
            <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Desativados</option>
            <option value="pendente" <?= $status === 'pendente' ? 'selected' : '' ?>>E-mail pendente</option>
        </select>
        <button class="btn btn-secundario btn-sm" type="submit">Filtrar</button>
        <?php if ($busca !== '' || $perfil !== '' || $status !== ''): ?><a href="<?= e(url('/admin/usuarios')) ?>" class="btn btn-fantasma btn-sm">Limpar</a><?php endif; ?>
    </form>
    <span class="texto-suave"><?= e(plural((int) $pag['total'], 'usuário encontrado', 'usuários encontrados')) ?></span>
</div>

<?php if (!$lista): ?>
    <div class="vazio card"><p>Nenhum usuário encontrado com esses filtros.</p></div>
<?php else: ?>
<div class="card tabela-caixa">
    <table class="tabela">
        <thead><tr><th>Usuário</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th>Atividade</th><th>Datas</th><th class="col-acoes">Ações</th></tr></thead>
        <tbody>
            <?php foreach ($lista as $u):
                $confirmado = !empty($u['email_verificado_em']);
                $item = $u;
                unset($item['senha']);
                $item['iniciais'] = iniciais($u['apelido']);
                $item['foto_url'] = foto_url($u);
                $item['perfil_rotulo'] = $u['perfil'] === 'admin' ? 'Administrador' : 'Jogador';
                $item['status_rotulo'] = $u['status'] === 'ativo' ? 'Ativo' : 'Desativado';
                $item['email_rotulo'] = $confirmado ? 'Confirmado em ' . formatar_data($u['email_verificado_em']) : 'Ainda não confirmado';
                $item['partidas_rotulo'] = plural((int) $u['total_partidas'], 'partida', 'partidas') . ', ' . plural((int) $u['total_vitorias'], 'vitória', 'vitórias');
                $item['pontos_rotulo'] = number_format((int) $u['total_pontos'], 0, ',', '.');
                $item['ultima_rotulo'] = $u['ultima_partida'] ? formatar_data($u['ultima_partida']) : 'Nunca jogou';
                $item['criado_em_fmt'] = formatar_data($u['criado_em']);
            ?>
                <tr>
                    <td data-rotulo="Usuário">
                        <span class="jogador-celula">
                            <?php $avatarUsuario = $u; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                            <span><strong><?= e($u['apelido']) ?></strong><small class="texto-suave"><?= e($u['nome']) ?></small></span>
                        </span>
                    </td>
                    <td data-rotulo="E-mail">
                        <span class="celula-dupla">
                            <span><?= e($u['email']) ?></span>
                            <small><?= $confirmado ? 'confirmado' : 'não confirmado' ?></small>
                        </span>
                    </td>
                    <td data-rotulo="Perfil"><span class="badge <?= $u['perfil'] === 'admin' ? 'badge-ouro' : 'badge-neutro' ?>"><?= $u['perfil'] === 'admin' ? 'Admin' : 'Jogador' ?></span></td>
                    <td data-rotulo="Situação">
                        <span class="badge <?= $u['status'] === 'ativo' ? 'badge-ok' : 'badge-erro' ?>"><?= $u['status'] === 'ativo' ? 'Ativo' : 'Desativado' ?></span>
                        <?php if (!$confirmado): ?><span class="badge badge-ouro">E-mail pendente</span><?php endif; ?>
                    </td>
                    <td data-rotulo="Atividade">
                        <span class="celula-dupla nowrap">
                            <strong><?= e(plural((int) $u['total_partidas'], 'partida', 'partidas')) ?></strong>
                            <small><?= e(plural((int) $u['total_vitorias'], 'vitória', 'vitórias')) ?>, <?= e(number_format((int) $u['total_pontos'], 0, ',', '.')) ?> pts</small>
                        </span>
                    </td>
                    <td data-rotulo="Datas">
                        <span class="celula-dupla nowrap">
                            <span>Desde <?= e(formatar_data($u['criado_em'], false)) ?></span>
                            <small>Jogou: <?= $u['ultima_partida'] ? e(tempo_atras($u['ultima_partida'])) : 'nunca' ?></small>
                        </span>
                    </td>
                    <td class="col-acoes">
                        <div class="acoes">
                            <button type="button" class="btn-acao ciano" title="Ver detalhes" aria-label="Ver detalhes de <?= e($u['apelido']) ?>" data-modal-open="modal-usuario-ver" data-item="<?= e(json_encode($item, JSON_UNESCAPED_UNICODE)) ?>"><?= icone_admin('olho') ?></button>
                            <button type="button" class="btn-acao ouro" title="Editar" aria-label="Editar <?= e($u['apelido']) ?>" data-modal-open="modal-usuario" data-titulo="Editar usuário" data-item="<?= e(json_encode($item, JSON_UNESCAPED_UNICODE)) ?>"><?= icone_admin('lapis') ?></button>
                            <?php if (!$confirmado): ?>
                                <button type="button" class="btn-acao ok" title="Confirmar e-mail manualmente" aria-label="Confirmar e-mail de <?= e($u['apelido']) ?>" data-confirmar data-neutro="1" data-botao="Confirmar e-mail" data-acao="<?= e(url('/admin/usuarios/verificar')) ?>" data-id="<?= (int) $u['id'] ?>" data-texto="Confirmar o e-mail de &quot;<?= e($u['apelido']) ?>&quot; sem precisar do link? A pessoa poderá entrar na hora."><?= icone_admin('email-ok') ?></button>
                            <?php endif; ?>
                            <button type="button" class="btn-acao ok" title="<?= $u['status'] === 'ativo' ? 'Desativar' : 'Ativar' ?> conta" aria-label="<?= $u['status'] === 'ativo' ? 'Desativar' : 'Ativar' ?> conta de <?= e($u['apelido']) ?>" data-confirmar data-neutro="1" data-botao="<?= $u['status'] === 'ativo' ? 'Desativar' : 'Ativar' ?>" data-acao="<?= e(url('/admin/usuarios/status')) ?>" data-id="<?= (int) $u['id'] ?>" data-texto="<?= $u['status'] === 'ativo' ? 'Desativar' : 'Ativar' ?> a conta de &quot;<?= e($u['apelido']) ?>&quot;?"><?= icone_admin('energia') ?></button>
                            <?php if ((int) $u['id'] !== $adminId): ?>
                                <button type="button" class="btn-acao perigo" title="Excluir" aria-label="Excluir <?= e($u['apelido']) ?>" data-confirmar data-acao="<?= e(url('/admin/usuarios/excluir')) ?>" data-id="<?= (int) $u['id'] ?>" data-texto="Excluir permanentemente a conta de &quot;<?= e($u['apelido']) ?>&quot;? As partidas dela ficam anônimas."><?= icone_admin('lixeira') ?></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../partials/paginacao.php'; ?>

<!-- Modal: visualizar -->
<div class="modal" id="modal-usuario-ver" hidden>
    <div class="modal-fundo" data-modal-close></div>
    <div class="modal-caixa modal-pequeno" role="dialog" aria-modal="true">
        <h3>Informações do usuário</h3>
        <div class="usuario-ficha">
            <span class="avatar avatar-iniciais avatar-xl" data-campo="iniciais" data-campo-iniciais></span>
            <img class="avatar avatar-xl" data-campo-img="foto_url" alt="Foto do usuário" hidden>
            <div><strong data-campo="apelido"></strong><small data-campo="nome"></small></div>
        </div>
        <dl class="detalhes">
            <dt>E-mail</dt><dd data-campo="email"></dd>
            <dt>Confirmação</dt><dd data-campo="email_rotulo"></dd>
            <dt>Perfil</dt><dd data-campo="perfil_rotulo"></dd>
            <dt>Situação</dt><dd data-campo="status_rotulo"></dd>
            <dt>Partidas</dt><dd data-campo="partidas_rotulo"></dd>
            <dt>Pontos</dt><dd data-campo="pontos_rotulo"></dd>
            <dt>Último jogo</dt><dd data-campo="ultima_rotulo"></dd>
            <dt>Cadastro</dt><dd data-campo="criado_em_fmt"></dd>
        </dl>
        <div class="modal-acoes"><button type="button" class="btn btn-primario" data-modal-close>Fechar</button></div>
    </div>
</div>

<!-- Modal: editar -->
<div class="modal" id="modal-usuario" hidden>
    <div class="modal-fundo" data-modal-close></div>
    <div class="modal-caixa" role="dialog" aria-modal="true">
        <h3 class="modal-titulo">Editar usuário</h3>
        <form method="post" action="<?= e(url('/admin/usuarios/salvar')) ?>" class="form" data-validar novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <div class="campo"><label for="usr-nome">Nome</label><input class="input" id="usr-nome" name="nome" required minlength="3" maxlength="120"><small class="campo-erro"></small></div>
            <div class="campo"><label for="usr-apelido">Apelido</label><input class="input" id="usr-apelido" name="apelido" required minlength="3" maxlength="30"><small class="campo-erro"></small></div>
            <div class="campo"><label for="usr-email">E-mail</label><input class="input" type="email" id="usr-email" name="email" required maxlength="160"><small class="campo-erro"></small></div>
            <div class="campo">
                <label for="usr-perfil">Perfil</label>
                <select class="input" id="usr-perfil" name="perfil">
                    <option value="jogador">Jogador</option>
                    <option value="admin">Administrador</option>
                </select>
                <small class="texto-suave">O sistema sempre mantém pelo menos um administrador ativo.</small>
            </div>
            <div class="modal-acoes">
                <button type="button" class="btn btn-fantasma" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primario" data-carregando>Salvar</button>
            </div>
        </form>
    </div>
</div>
