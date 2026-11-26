<header class="admin-cabecalho">
    <div>
        <h1>Categorias</h1>
        <p>Cada categoria é um mundo que o jogador pode escolher. Um mundo só aparece para jogar se estiver ativo e tiver palavras.</p>
    </div>
    <div class="admin-cabecalho-acoes">
        <button type="button" class="btn btn-primario" data-novo data-modal-open="modal-categoria" data-titulo="Nova categoria" data-item="{}">+ Nova categoria</button>
    </div>
</header>

<section class="resumo-faixa">
    <div class="resumo-item tom-violeta"><strong><?= (int) $resumo['total'] ?></strong><span>categorias</span></div>
    <div class="resumo-item tom-ok"><strong><?= (int) $resumo['ativas'] ?></strong><span>ativas</span></div>
    <div class="resumo-item"><strong><?= (int) $resumo['inativas'] ?></strong><span>inativas</span></div>
    <div class="resumo-item <?= (int) $resumo['sem_palavras'] > 0 ? 'tom-ouro' : 'tom-ok' ?>"><strong><?= (int) $resumo['sem_palavras'] ?></strong><span>sem palavras</span></div>
</section>

<div class="admin-barra">
    <form method="get" class="busca" role="search">
        <span class="busca-campo">
            <?= icone_admin('busca') ?>
            <input class="input" type="search" name="busca" value="<?= e($busca) ?>" placeholder="Pesquisar categoria..." aria-label="Pesquisar categoria">
        </span>
        <button class="btn btn-secundario btn-sm" type="submit">Buscar</button>
        <?php if ($busca !== ''): ?><a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-fantasma btn-sm">Limpar</a><?php endif; ?>
    </form>
    <span class="texto-suave"><?= e(plural((int) $pag['total'], 'categoria encontrada', 'categorias encontradas')) ?></span>
</div>

<?php if (!$categorias): ?>
    <div class="vazio card">
        <svg viewBox="0 0 64 64" class="vazio-arte" fill="none" stroke="currentColor" stroke-width="2"><circle cx="32" cy="32" r="12"/><ellipse cx="32" cy="34" rx="26" ry="7" transform="rotate(-18 32 34)"/></svg>
        <p><?= $busca !== '' ? 'Nenhuma categoria encontrada para essa busca.' : 'Nenhuma categoria cadastrada ainda. Crie o primeiro mundo!' ?></p>
    </div>
<?php else: ?>
<div class="card tabela-caixa">
    <table class="tabela">
        <thead><tr><th>Mundo</th><th>Palavras</th><th>Partidas</th><th>Taxa de vitória</th><th>Status</th><th>Criada em</th><th class="col-acoes">Ações</th></tr></thead>
        <tbody>
            <?php foreach ($categorias as $c): $taxa = pct($c['total_vitorias'], $c['total_partidas']); ?>
                <tr>
                    <td data-rotulo="Mundo">
                        <span class="mundo-mini">
                            <?php if ($c['imagem']): ?>
                                <img src="<?= e(upload_url($c['imagem'], 'categorias')) ?>" alt="">
                            <?php else: ?>
                                <span class="mundo-mini-icone"><?= icone_svg($c['icone']) ?></span>
                            <?php endif; ?>
                            <span class="celula-dupla">
                                <strong><?= e($c['nome']) ?></strong>
                                <small class="limite"><?= e($c['descricao'] !== '' ? $c['descricao'] : 'Sem descrição') ?></small>
                            </span>
                        </span>
                    </td>
                    <td data-rotulo="Palavras">
                        <?php if ((int) $c['total_palavras'] === 0): ?>
                            <span class="badge badge-ouro">Sem palavras</span>
                        <?php else: ?>
                            <span class="num-forte"><?= (int) $c['total_palavras'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td data-rotulo="Partidas"><span class="num-forte"><?= (int) $c['total_partidas'] ?></span></td>
                    <td data-rotulo="Taxa de vitória">
                        <?php if ((int) $c['total_partidas'] === 0): ?>
                            <span class="sem-dado">sem partidas</span>
                        <?php else: ?>
                            <div class="taxa"><div class="taxa-trilho"><i style="width: <?= $taxa ?>%"></i></div><span><?= $taxa ?>%</span></div>
                        <?php endif; ?>
                    </td>
                    <td data-rotulo="Status"><span class="badge <?= $c['status'] === 'ativo' ? 'badge-ok' : 'badge-neutro' ?>"><?= $c['status'] === 'ativo' ? 'Ativa' : 'Inativa' ?></span></td>
                    <td data-rotulo="Criada em"><?= e(formatar_data($c['criado_em'], false)) ?></td>
                    <td class="col-acoes">
                        <div class="acoes">
                            <a href="<?= e(url('/admin/palavras?categoria=' . (int) $c['id'])) ?>" class="btn-acao ciano" title="Ver palavras" aria-label="Ver palavras de <?= e($c['nome']) ?>"><?= icone_admin('palavras') ?></a>
                            <button type="button" class="btn-acao ouro" title="Editar" aria-label="Editar <?= e($c['nome']) ?>" data-modal-open="modal-categoria" data-titulo="Editar categoria" data-item="<?= e(json_encode($c, JSON_UNESCAPED_UNICODE)) ?>"><?= icone_admin('lapis') ?></button>
                            <form method="post" action="<?= e(url('/admin/categorias/status')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="btn-acao ok" title="<?= $c['status'] === 'ativo' ? 'Desativar' : 'Ativar' ?>" aria-label="<?= $c['status'] === 'ativo' ? 'Desativar' : 'Ativar' ?> <?= e($c['nome']) ?>"><?= icone_admin('energia') ?></button>
                            </form>
                            <button type="button" class="btn-acao perigo" title="Excluir" aria-label="Excluir <?= e($c['nome']) ?>" data-confirmar data-acao="<?= e(url('/admin/categorias/excluir')) ?>" data-id="<?= (int) $c['id'] ?>" data-texto="Excluir a categoria &quot;<?= e($c['nome']) ?>&quot;? Só é possível se ela não tiver palavras."><?= icone_admin('lixeira') ?></button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../partials/paginacao.php'; ?>

<!-- Modal: criar/editar categoria -->
<div class="modal" id="modal-categoria" hidden>
    <div class="modal-fundo" data-modal-close></div>
    <div class="modal-caixa" role="dialog" aria-modal="true">
        <h3 class="modal-titulo">Categoria</h3>
        <p class="modal-sub">O nome aparece para os jogadores. A imagem é opcional: sem ela, o mundo usa o ícone escolhido.</p>
        <form method="post" action="<?= e(url('/admin/categorias/salvar')) ?>" enctype="multipart/form-data" class="form" data-validar novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <div class="campo">
                <label for="cat-nome">Nome</label>
                <input class="input" id="cat-nome" name="nome" required maxlength="60">
                <small class="campo-erro"></small>
            </div>
            <div class="campo">
                <label for="cat-descricao">Descrição</label>
                <textarea class="input" id="cat-descricao" name="descricao" rows="2" maxlength="255"></textarea>
            </div>
            <div class="form-duas-colunas">
                <div class="campo">
                    <label for="cat-icone">Ícone</label>
                    <select class="input" id="cat-icone" name="icone">
                        <?php foreach (icones_disponiveis() as $chave => $rotulo): ?>
                            <option value="<?= e($chave) ?>"><?= e($rotulo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="cat-status">Status</label>
                    <select class="input" id="cat-status" name="status">
                        <option value="ativo">Ativa</option>
                        <option value="inativo">Inativa</option>
                    </select>
                </div>
            </div>
            <div class="campo">
                <label for="cat-imagem">Imagem (opcional, JPG/PNG/WEBP até 2 MB)</label>
                <input class="input" type="file" id="cat-imagem" name="imagem" accept="image/jpeg,image/png,image/webp">
                <label class="check"><input type="checkbox" name="remover_imagem" value="1"> Remover imagem atual</label>
            </div>
            <div class="modal-acoes">
                <button type="button" class="btn btn-fantasma" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primario" data-carregando>Salvar</button>
            </div>
        </form>
    </div>
</div>
