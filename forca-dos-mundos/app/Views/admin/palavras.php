<header class="admin-cabecalho">
    <div>
        <h1>Palavras</h1>
        <p>As palavras secretas do jogo. Cada palavra é única em todo o jogo e usa só letras de A a Z, sem acentos.</p>
    </div>
    <div class="admin-cabecalho-acoes">
        <button type="button" class="btn btn-primario" data-novo data-modal-open="modal-palavra" data-titulo="Nova palavra" data-item="<?= e(json_encode(['categoria_id' => $categoriaId ?: ''])) ?>">+ Nova palavra</button>
    </div>
</header>

<section class="resumo-faixa">
    <div class="resumo-item tom-ciano"><strong><?= (int) $resumo['total'] ?></strong><span>palavras</span></div>
    <div class="resumo-item tom-violeta"><strong><?= (int) $resumo['categorias'] ?></strong><span>mundos com palavras</span></div>
    <div class="resumo-item"><strong><?= number_format((float) $resumo['media'], 1, ',', '.') ?></strong><span>letras em média</span></div>
    <div class="resumo-item"><strong><?= (int) $resumo['menor'] ?> a <?= (int) $resumo['maior'] ?></strong><span>variação de letras</span></div>
</section>

<div class="admin-barra">
    <form method="get" class="busca" role="search">
        <span class="busca-campo">
            <?= icone_admin('busca') ?>
            <input class="input" type="search" name="busca" value="<?= e($busca) ?>" placeholder="Pesquisar palavra ou dica..." aria-label="Pesquisar palavra ou dica">
        </span>
        <select class="input" name="categoria" aria-label="Filtrar por categoria">
            <option value="0">Todas as categorias</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === $categoriaId ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-secundario btn-sm" type="submit">Filtrar</button>
        <?php if ($busca !== '' || $categoriaId > 0): ?><a href="<?= e(url('/admin/palavras')) ?>" class="btn btn-fantasma btn-sm">Limpar</a><?php endif; ?>
    </form>
    <span class="texto-suave"><?= e(plural((int) $pag['total'], 'palavra encontrada', 'palavras encontradas')) ?></span>
</div>

<?php if (!$palavras): ?>
    <div class="vazio card"><p><?= ($busca !== '' || $categoriaId > 0) ? 'Nenhuma palavra encontrada com esses filtros.' : 'Nenhuma palavra cadastrada ainda.' ?></p></div>
<?php else: ?>
<div class="card tabela-caixa">
    <table class="tabela">
        <thead><tr><th>Palavra</th><th>Categoria</th><th>Dica</th><th>Jogadas</th><th>Taxa de acerto</th><th class="col-acoes">Ações</th></tr></thead>
        <tbody>
            <?php foreach ($palavras as $p): $taxa = pct($p['total_vitorias'], $p['total_jogadas']); ?>
                <tr>
                    <td data-rotulo="Palavra">
                        <span class="celula-dupla">
                            <strong class="palavra-admin"><?= e($p['palavra']) ?></strong>
                            <small><?= e(plural(strlen($p['palavra']), 'letra', 'letras')) ?></small>
                        </span>
                    </td>
                    <td data-rotulo="Categoria"><span class="badge badge-neutro"><?= e($p['categoria_nome']) ?></span></td>
                    <td data-rotulo="Dica"><?= e($p['dica']) ?></td>
                    <td data-rotulo="Jogadas"><span class="num-forte"><?= (int) $p['total_jogadas'] ?></span></td>
                    <td data-rotulo="Taxa de acerto">
                        <?php if ((int) $p['total_jogadas'] === 0): ?>
                            <span class="sem-dado">nunca sorteada</span>
                        <?php else: ?>
                            <div class="taxa"><div class="taxa-trilho"><i style="width: <?= $taxa ?>%"></i></div><span><?= $taxa ?>%</span></div>
                        <?php endif; ?>
                    </td>
                    <td class="col-acoes">
                        <div class="acoes">
                            <button type="button" class="btn-acao ouro" title="Editar" aria-label="Editar <?= e($p['palavra']) ?>" data-modal-open="modal-palavra" data-titulo="Editar palavra" data-item="<?= e(json_encode($p, JSON_UNESCAPED_UNICODE)) ?>"><?= icone_admin('lapis') ?></button>
                            <button type="button" class="btn-acao perigo" title="Excluir" aria-label="Excluir <?= e($p['palavra']) ?>" data-confirmar data-acao="<?= e(url('/admin/palavras/excluir')) ?>" data-id="<?= (int) $p['id'] ?>" data-texto="Excluir a palavra &quot;<?= e($p['palavra']) ?>&quot;? As partidas antigas continuam no histórico."><?= icone_admin('lixeira') ?></button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../partials/paginacao.php'; ?>

<div class="modal" id="modal-palavra" hidden>
    <div class="modal-fundo" data-modal-close></div>
    <div class="modal-caixa" role="dialog" aria-modal="true">
        <h3 class="modal-titulo">Palavra</h3>
        <p class="modal-sub">De 3 a 30 letras, sem acentos, espaços ou símbolos. A dica aparece para o jogador durante a partida.</p>
        <form method="post" action="<?= e(url('/admin/palavras/salvar')) ?>" class="form" data-validar novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <div class="campo">
                <label for="pal-categoria">Categoria</label>
                <select class="input" id="pal-categoria" name="categoria_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e($c['nome']) ?><?= $c['status'] === 'inativo' ? ' (inativa)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="campo-erro"></small>
            </div>
            <div class="campo">
                <label for="pal-palavra">Palavra (somente A-Z, sem acentos ou espaços)</label>
                <input class="input input-maiusculo" id="pal-palavra" name="palavra" required minlength="3" maxlength="30" pattern="[A-Za-z]+" autocomplete="off" data-so-letras>
                <small class="campo-erro"></small>
            </div>
            <div class="campo">
                <label for="pal-dica">Dica</label>
                <input class="input" id="pal-dica" name="dica" required maxlength="200">
                <small class="campo-erro"></small>
            </div>
            <div class="modal-acoes">
                <button type="button" class="btn btn-fantasma" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primario" data-carregando>Salvar</button>
            </div>
        </form>
    </div>
</div>
