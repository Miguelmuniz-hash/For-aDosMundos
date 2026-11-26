<?php
$totalPartidas = (int) $partidas['total'];
$pctVitorias = pct($partidas['vitorias'], $totalPartidas);
$pendentes = (int) $usuarios['pendentes'];
$fmt = static function ($n): string {
    return number_format((int) $n, 0, ',', '.');
};

$cartoes = [
    ['Usuários', $fmt($usuarios['total']), (int) $usuarios['ativos'] . ' ativos, ' . (int) $usuarios['novos7'] . ' novos na semana', 'tom-violeta', 'usuarios'],
    ['Partidas', $fmt($totalPartidas), plural((int) $partidas['hoje'], 'partida', 'partidas') . ' hoje', 'tom-ciano', 'partidas'],
    ['Vitórias', $fmt($partidas['vitorias']), $pctVitorias . '% das partidas', 'tom-ok', 'vitorias'],
    ['Derrotas', $fmt($partidas['derrotas']), 'média de ' . number_format((float) $partidas['media_erros'], 1, ',', '.') . ' erros por partida', 'tom-perigo', 'derrotas'],
    ['Pontos distribuídos', $fmt($partidas['pontos']), 'maior pontuação: ' . $fmt($partidas['maior']), 'tom-ouro', 'pontos'],
    ['Categorias', $fmt($catResumo['total']), (int) $catResumo['ativas'] . ' ativas, ' . (int) $catResumo['inativas'] . ' inativas', 'tom-violeta', 'categorias'],
    ['Palavras', $fmt($palResumo['total']), 'média de ' . number_format((float) $palResumo['media'], 1, ',', '.') . ' letras', 'tom-ciano', 'palavras'],
    ['E-mails pendentes', $fmt($pendentes), $pendentes > 0 ? 'aguardando confirmação' : 'todos confirmados', $pendentes > 0 ? 'tom-ouro' : 'tom-ok', 'email'],
];

// Gráfico por dia
$maxDia = 1;
foreach ($porDia as $d) {
    $maxDia = max($maxDia, (int) $d['total']);
}
$hoje = date('Y-m-d');

// Vitórias por número de erros
$maxErrosBarra = max(1, max($porErros));
$totalVitorias = array_sum($porErros);

// Pontos de atenção
$alertas = [];
foreach ($semPalavras as $c) {
    $alertas[] = ['aviso', 'O mundo "' . $c['nome'] . '" está ativo, mas ainda não tem palavras. Os jogadores veem "Sem palavras".', url('/admin/palavras?novo=1&categoria=' . (int) $c['id']), 'Adicionar palavras'];
}
if ($pendentes > 0) {
    $alertas[] = ['info', plural($pendentes, 'conta ainda não confirmou', 'contas ainda não confirmaram') . ' o e-mail e não ' . ($pendentes === 1 ? 'consegue' : 'conseguem') . ' entrar.', url('/admin/usuarios?status=pendente'), 'Ver contas pendentes'];
}
if ((int) $usuarios['inativos'] > 0) {
    $alertas[] = ['info', plural((int) $usuarios['inativos'], 'conta está desativada', 'contas estão desativadas') . '.', url('/admin/usuarios?status=inativo'), 'Ver contas desativadas'];
}
?>
<section class="admin-hero card">
    <div>
        <span class="admin-data"><?= e(data_extensa()) ?></span>
        <h1>Olá, <?= e($usuario['apelido']) ?></h1>
        <p>
            <?= e(plural((int) $partidas['hoje'], 'partida', 'partidas')) ?> hoje e
            <?= e(plural((int) $usuarios['novos7'], 'novo explorador', 'novos exploradores')) ?> nos últimos 7 dias.
        </p>
    </div>
    <div class="admin-hero-acoes">
        <a href="<?= e(url('/admin/palavras?novo=1')) ?>" class="btn btn-primario">Adicionar palavra</a>
        <a href="<?= e(url('/admin/categorias?novo=1')) ?>" class="btn btn-secundario">Nova categoria</a>
        <a href="<?= e(url('/admin/partidas')) ?>" class="btn btn-fantasma">Ver partidas</a>
    </div>
</section>

<section class="stats-admin">
    <?php foreach ($cartoes as [$rotulo, $valor, $detalhe, $tom, $icone]): ?>
        <div class="stat-admin card <?= e($tom) ?>">
            <span class="stat-icone"><?= icone_admin($icone) ?></span>
            <div>
                <span class="stat-rotulo"><?= e($rotulo) ?></span>
                <strong class="stat-valor"><?= e($valor) ?></strong>
                <small class="stat-detalhe"><?= e($detalhe) ?></small>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="adm-grade-a">
    <div class="card">
        <h2 class="card-titulo">Partidas nos últimos 7 dias</h2>
        <div class="dias">
            <?php foreach ($porDia as $d):
                $hVit = $d['vitorias'] / $maxDia * 100;
                $hDer = $d['derrotas'] / $maxDia * 100;
            ?>
                <div class="dia-coluna <?= $d['dia'] === $hoje ? 'hoje' : '' ?>" title="<?= e(date('d/m', strtotime($d['dia']))) ?>: <?= (int) $d['total'] ?> partidas (<?= (int) $d['vitorias'] ?> vitórias, <?= (int) $d['derrotas'] ?> derrotas)">
                    <span class="dia-total"><?= (int) $d['total'] ?></span>
                    <div class="dia-pilha">
                        <?php if ($d['derrotas'] > 0): ?><i class="seg seg-der" style="height: <?= round($hDer, 1) ?>%"></i><?php endif; ?>
                        <?php if ($d['vitorias'] > 0): ?><i class="seg seg-vit" style="height: <?= round($hVit, 1) ?>%"></i><?php endif; ?>
                    </div>
                    <small><?= $d['dia'] === $hoje ? 'hoje' : e(date('d/m', strtotime($d['dia']))) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="legenda-linha">
            <span><i class="ponto ponto-ok"></i>Vitórias</span>
            <span><i class="ponto ponto-perigo"></i>Derrotas</span>
        </div>
    </div>

    <div class="card">
        <h2 class="card-titulo">Vitórias × derrotas</h2>
        <div class="rosca-area">
            <div class="rosca" style="--pct: <?= $pctVitorias ?>">
                <div class="rosca-centro"><strong><?= $pctVitorias ?>%</strong><small>vitórias</small></div>
            </div>
            <ul class="legenda">
                <li><i class="ponto ponto-ok"></i> <?= e(plural((int) $partidas['vitorias'], 'vitória', 'vitórias')) ?></li>
                <li><i class="ponto ponto-perigo"></i> <?= e(plural((int) $partidas['derrotas'], 'derrota', 'derrotas')) ?></li>
                <li class="texto-suave"><?= e(plural((int) $partidas['jogadores'], 'jogador ativo', 'jogadores ativos')) ?></li>
            </ul>
        </div>
    </div>

    <div class="card">
        <h2 class="card-titulo">Erros nas vitórias</h2>
        <?php if ($totalVitorias === 0): ?>
            <p class="vazio-texto">Ainda não há vitórias registradas.</p>
        <?php else: ?>
            <ul class="hbarras">
                <?php foreach ($porErros as $erros => $qtd): ?>
                    <li>
                        <span class="rotulo"><?= $erros === 0 ? 'Sem erros' : e(plural((int) $erros, 'erro', 'erros')) ?></span>
                        <strong><?= (int) $qtd ?></strong>
                        <div class="hbarra <?= $erros === 0 ? 'ouro' : '' ?>"><i style="width: <?= pct($qtd, $maxErrosBarra) ?>%"></i></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<section class="adm-grade-b">
    <div class="card">
        <div class="card-titulo-linha">
            <h2 class="card-titulo">Desempenho por mundo</h2>
            <a href="<?= e(url('/admin/categorias')) ?>" class="link">Gerenciar</a>
        </div>
        <?php if (!$mundos): ?>
            <p class="vazio-texto">Nenhuma categoria cadastrada.</p>
        <?php else: ?>
            <div class="tabela-caixa">
                <table class="tabela">
                    <thead><tr><th>Mundo</th><th>Palavras</th><th>Partidas</th><th>Taxa de vitória</th></tr></thead>
                    <tbody>
                        <?php foreach ($mundos as $m): $taxa = pct($m['vitorias'], $m['partidas']); ?>
                            <tr>
                                <td data-rotulo="Mundo">
                                    <span class="mundo-mini">
                                        <span class="mundo-mini-icone"><?= icone_svg($m['icone']) ?></span>
                                        <span class="celula-dupla"><strong><?= e($m['nome']) ?></strong><?php if ($m['status'] !== 'ativo'): ?><small>desativado</small><?php endif; ?></span>
                                    </span>
                                </td>
                                <td data-rotulo="Palavras"><span class="<?= (int) $m['palavras'] === 0 ? 'num-zero' : 'num-forte' ?>"><?= (int) $m['palavras'] ?></span></td>
                                <td data-rotulo="Partidas"><span class="num-forte"><?= (int) $m['partidas'] ?></span></td>
                                <td data-rotulo="Taxa de vitória">
                                    <?php if ((int) $m['partidas'] === 0): ?>
                                        <span class="sem-dado">sem partidas</span>
                                    <?php else: ?>
                                        <div class="taxa"><div class="taxa-trilho"><i style="width: <?= $taxa ?>%"></i></div><span><?= $taxa ?>%</span></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 class="card-titulo">Pontos de atenção</h2>
        <?php if (!$alertas): ?>
            <div class="atencao-ok"><?= icone_admin('check') ?><span>Tudo certo por aqui. Todos os mundos ativos têm palavras e não há contas pendentes.</span></div>
        <?php else: ?>
            <ul class="atencao">
                <?php foreach ($alertas as [$tipo, $texto, $link, $rotuloLink]): ?>
                    <li class="<?= e($tipo) ?>">
                        <span><?= e($texto) ?></span>
                        <a href="<?= e($link) ?>"><?= e($rotuloLink) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<section class="adm-grade-c">
    <div class="card">
        <h2 class="card-titulo">Jogadores com mais pontos</h2>
        <?php if (!$topJogadores): ?>
            <p class="vazio-texto">Sem jogadores pontuando ainda.</p>
        <?php else: ?>
            <ol class="lista-pessoas">
                <?php foreach ($topJogadores as $i => $j): ?>
                    <li>
                        <span class="posicao"><?= $i + 1 ?>º</span>
                        <?php $avatarUsuario = $j; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                        <span class="meio"><strong><?= e($j['apelido']) ?></strong><small><?= e(plural((int) $j['partidas'], 'partida', 'partidas')) ?>, melhor <?= (int) $j['maior'] ?></small></span>
                        <strong class="texto-ouro"><?= $fmt($j['total_pontos']) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-titulo-linha">
            <h2 class="card-titulo">Últimas partidas</h2>
            <a href="<?= e(url('/admin/partidas')) ?>" class="link">Ver todas</a>
        </div>
        <?php if (!$ultimas): ?>
            <p class="vazio-texto">Nenhuma partida registrada ainda.</p>
        <?php else: ?>
            <ul class="lista-pessoas">
                <?php foreach ($ultimas as $p): ?>
                    <li>
                        <?php $avatarUsuario = $p; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                        <span class="meio"><strong><?= e($p['apelido'] ?? 'Jogador removido') ?></strong><small><?= e($p['palavra_jogada']) ?>, <?= e($p['categoria_nome'] ?? 'mundo removido') ?></small></span>
                        <span class="fim">
                            <span class="badge <?= $p['resultado'] === 'vitoria' ? 'badge-ok' : 'badge-erro' ?>"><?= (int) $p['pontos'] ?> pts</span>
                            <small><?= e(tempo_atras($p['data_hora'])) ?></small>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-titulo-linha">
            <h2 class="card-titulo">Novos exploradores</h2>
            <a href="<?= e(url('/admin/usuarios')) ?>" class="link">Ver todos</a>
        </div>
        <?php if (!$novosUsuarios): ?>
            <p class="vazio-texto">Nenhum usuário cadastrado.</p>
        <?php else: ?>
            <ul class="lista-pessoas">
                <?php foreach ($novosUsuarios as $n): ?>
                    <li>
                        <?php $avatarUsuario = $n; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                        <span class="meio"><strong><?= e($n['apelido']) ?></strong><small>cadastro <?= e(tempo_atras($n['criado_em'])) ?></small></span>
                        <span class="badge <?= !empty($n['email_verificado_em']) ? 'badge-ok' : 'badge-ouro' ?>"><?= !empty($n['email_verificado_em']) ? 'Confirmado' : 'Pendente' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
