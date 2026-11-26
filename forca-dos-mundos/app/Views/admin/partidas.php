<header class="admin-cabecalho">
    <div>
        <h1>Partidas</h1>
        <p>Histórico de todas as partidas jogadas. Os números abaixo refletem os filtros aplicados.</p>
    </div>
</header>

<section class="resumo-faixa">
    <div class="resumo-item tom-violeta"><strong><?= (int) $resumo['total'] ?></strong><span>partidas</span></div>
    <div class="resumo-item tom-ok"><strong><?= (int) $resumo['vitorias'] ?></strong><span>vitórias (<?= pct($resumo['vitorias'], $resumo['total']) ?>%)</span></div>
    <div class="resumo-item tom-perigo"><strong><?= (int) $resumo['derrotas'] ?></strong><span>derrotas</span></div>
    <div class="resumo-item tom-ouro"><strong><?= e(number_format((int) $resumo['pontos'], 0, ',', '.')) ?></strong><span>pontos somados</span></div>
    <div class="resumo-item"><strong><?= e(number_format((float) $resumo['media_erros'], 1, ',', '.')) ?></strong><span>erros em média</span></div>
</section>

<div class="admin-barra">
    <form method="get" class="busca busca-larga" role="search">
        <span class="busca-campo">
            <?= icone_admin('busca') ?>
            <input class="input" type="search" name="busca" value="<?= e($busca) ?>" placeholder="Jogador ou palavra..." aria-label="Pesquisar por jogador ou palavra">
        </span>
        <label class="campo-data">De <input class="input" type="date" name="de" value="<?= e($de) ?>"></label>
        <label class="campo-data">Até <input class="input" type="date" name="ate" value="<?= e($ate) ?>"></label>
        <select class="input" name="resultado" aria-label="Filtrar por resultado">
            <option value="">Todos os resultados</option>
            <option value="vitoria" <?= $resultado === 'vitoria' ? 'selected' : '' ?>>Vitórias</option>
            <option value="derrota" <?= $resultado === 'derrota' ? 'selected' : '' ?>>Derrotas</option>
        </select>
        <button class="btn btn-secundario btn-sm" type="submit">Filtrar</button>
        <a href="<?= e(url('/admin/partidas')) ?>" class="btn btn-fantasma btn-sm">Limpar</a>
    </form>
</div>

<?php if (!$lista): ?>
    <div class="vazio card"><p>Nenhuma partida encontrada com esses filtros.</p></div>
<?php else: ?>
<div class="card tabela-caixa">
    <table class="tabela">
        <thead><tr><th>#</th><th>Jogador</th><th>Palavra</th><th>Mundo</th><th>Erros</th><th>Pontos</th><th>Resultado</th><th>Data</th></tr></thead>
        <tbody>
            <?php foreach ($lista as $p): ?>
                <tr>
                    <td data-rotulo="#"><span class="texto-suave"><?= (int) $p['id'] ?></span></td>
                    <td data-rotulo="Jogador">
                        <span class="jogador-celula">
                            <?php $avatarUsuario = $p; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                            <span><?= e($p['apelido'] ?? 'Jogador removido') ?></span>
                        </span>
                    </td>
                    <td data-rotulo="Palavra"><strong class="palavra-admin"><?= e($p['palavra_jogada']) ?></strong></td>
                    <td data-rotulo="Mundo"><?= e($p['categoria_nome'] ?? 'Removido') ?></td>
                    <td data-rotulo="Erros">
                        <span class="orbes-linha" title="<?= (int) $p['erros'] ?> de <?= (int) $maxErros ?> erros">
                            <span class="orbes">
                                <?php for ($i = 1; $i <= (int) $maxErros; $i++): ?><i class="<?= $i <= (int) $p['erros'] ? 'cheio' : '' ?>"></i><?php endfor; ?>
                            </span>
                            <span class="orbes-num"><?= (int) $p['erros'] ?>/<?= (int) $maxErros ?></span>
                        </span>
                    </td>
                    <td data-rotulo="Pontos"><strong class="texto-ouro"><?= (int) $p['pontos'] ?></strong></td>
                    <td data-rotulo="Resultado"><span class="badge <?= $p['resultado'] === 'vitoria' ? 'badge-ok' : 'badge-erro' ?>"><?= $p['resultado'] === 'vitoria' ? 'Vitória' : 'Derrota' ?></span></td>
                    <td data-rotulo="Data">
                        <span class="celula-dupla">
                            <span><?= e(formatar_data($p['data_hora'])) ?></span>
                            <small><?= e(tempo_atras($p['data_hora'])) ?></small>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../partials/paginacao.php'; ?>
