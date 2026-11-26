<?php
$maxGrafico = 1;
foreach ($grafico as $g) {
    $maxGrafico = max($maxGrafico, (int) $g['pontos']);
}
$perdas = max(0, 100 - $stats['taxa']);
?>
<section class="hero">
    <div class="hero-texto">
        <span class="auth-etiqueta">Painel do explorador</span>
        <h1>Olá, <span class="texto-ouro"><?= e($usuario['apelido']) ?></span>!</h1>
        <p>Pronto para descobrir novas palavras? Escolha um mundo e mostre do que você é capaz.</p>
        <div class="hero-botoes">
            <a href="<?= e(url('/mundos')) ?>" class="btn btn-primario">Começar a jogar</a>
            <a href="<?= e(url('/ranking')) ?>" class="btn btn-secundario">Ver ranking</a>
            <a href="<?= e(url('/perfil')) ?>" class="btn btn-fantasma">Meu perfil</a>
        </div>
    </div>
    <div class="hero-perfil card">
        <?php $avatarUsuario = $usuario; $avatarClasse = 'avatar-lg'; require __DIR__ . '/../partials/avatar.php'; ?>
        <strong><?= e($usuario['apelido']) ?></strong>
        <small>Explorador desde <?= e(formatar_data($usuario['criado_em'], false)) ?></small>
    </div>
</section>

<section class="stats">
    <div class="stat card"><span class="stat-rotulo">Partidas</span><strong class="stat-valor"><?= (int) $stats['total'] ?></strong></div>
    <div class="stat card"><span class="stat-rotulo">Vitórias</span><strong class="stat-valor texto-ok"><?= (int) $stats['vitorias'] ?></strong></div>
    <div class="stat card"><span class="stat-rotulo">Derrotas</span><strong class="stat-valor texto-perigo"><?= (int) $stats['derrotas'] ?></strong></div>
    <div class="stat card"><span class="stat-rotulo">Pontos totais</span><strong class="stat-valor texto-ouro"><?= (int) $stats['pontos'] ?></strong></div>
    <div class="stat card"><span class="stat-rotulo">Maior pontuação</span><strong class="stat-valor"><?= (int) $stats['maior'] ?></strong></div>
</section>

<section class="grade-2">
    <div class="card">
        <h2 class="card-titulo">Taxa de vitórias</h2>
        <div class="rosca-area">
            <div class="rosca" style="--pct: <?= (int) $stats['taxa'] ?>">
                <div class="rosca-centro"><strong><?= (int) $stats['taxa'] ?>%</strong><small>vitórias</small></div>
            </div>
            <ul class="legenda">
                <li><i class="ponto ponto-ok"></i> <?= (int) $stats['vitorias'] ?> vitórias</li>
                <li><i class="ponto ponto-perigo"></i> <?= (int) $stats['derrotas'] ?> derrotas</li>
            </ul>
        </div>
    </div>
    <div class="card">
        <h2 class="card-titulo">Pontos nas últimas partidas</h2>
        <?php if (!$grafico): ?>
            <p class="vazio-texto">Jogue sua primeira partida para ver o gráfico.</p>
        <?php else: ?>
            <div class="barras">
                <?php foreach ($grafico as $g): ?>
                    <div class="barra-coluna" title="<?= (int) $g['pontos'] ?> pontos">
                        <span class="barra-valor"><?= (int) $g['pontos'] ?></span>
                        <div class="barra <?= $g['resultado'] === 'vitoria' ? 'barra-ok' : 'barra-perigo' ?>" style="--altura: <?= max(4, (int) round($g['pontos'] / $maxGrafico * 100)) ?>%"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section>
    <div class="secao-titulo">
        <h2>Explore os Mundos</h2>
        <a href="<?= e(url('/mundos')) ?>" class="link">Ver todos</a>
    </div>
    <?php if (!$categorias): ?>
        <div class="vazio card"><p>Nenhum mundo disponível no momento.</p></div>
    <?php else: ?>
        <div class="mundos-grade">
            <?php foreach ($categorias as $cat): require __DIR__ . '/../partials/card_mundo.php'; endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <div class="secao-titulo"><h2>Últimas partidas</h2></div>
    <?php require __DIR__ . '/../partials/lista_partidas.php'; ?>
</section>
