<?php
$restantes = $maxErros - $erros;
$percentual = $progresso['total'] > 0 ? (int) round($progresso['reveladas'] / $progresso['total'] * 100) : 0;
$forcaErros = $erros;
?>
<section class="jogo" id="jogo"
    data-url-letra="<?= e(url('/jogo/letra')) ?>"
    data-max-erros="<?= (int) $maxErros ?>">

    <div class="jogo-topo">
        <div class="chip-categoria">
            <span class="chip-icone"><?= icone_svg($categoria['icone'] ?? 'planeta') ?></span>
            <div>
                <small>Mundo</small>
                <strong><?= e($categoria['nome'] ?? 'Mundo') ?></strong>
            </div>
        </div>
        <button type="button" class="btn btn-fantasma btn-sm" data-modal-open="modal-abandonar">Abandonar partida</button>
    </div>

    <div class="jogo-grade">
        <!-- Forca -->
        <div class="card forca-card">
            <?php require __DIR__ . '/../partials/forca_svg.php'; ?>
            <div class="vidas" aria-live="polite">
                <div class="vidas-orbes" id="vidas">
                    <?php for ($i = 1; $i <= $maxErros; $i++): ?>
                        <i class="vida <?= $i <= $erros ? 'perdida' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="vidas-texto">Erros restantes: <strong id="restantes"><?= (int) $restantes ?></strong></span>
            </div>
        </div>

        <!-- Palavra, dica e teclado -->
        <div class="jogo-centro">
            <div class="card palavra-card">
                <div class="dica">
                    <span class="dica-selo">Dica</span>
                    <p><?= e($dica) ?></p>
                </div>

                <div class="palavra" id="palavra" aria-label="Palavra secreta">
                    <?php foreach ($mascara as $i => $letra): ?>
                        <span class="slot <?= $letra !== null ? 'revelada' : '' ?>" data-i="<?= $i ?>"><?= $letra !== null ? e($letra) : '' ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="progresso">
                    <div class="progresso-trilho"><div class="progresso-barra" id="progresso-barra" style="width: <?= $percentual ?>%"></div></div>
                    <small>Progresso: <span id="progresso-texto"><?= (int) $progresso['reveladas'] ?>/<?= (int) $progresso['total'] ?></span> letras diferentes</small>
                </div>
            </div>

            <!-- Teclado virtual (funciona com JS via fetch e também sem JS via formulário) -->
            <form method="post" action="<?= e(url('/jogo/letra')) ?>" class="teclado" id="teclado" aria-label="Teclado virtual">
                <?= csrf_field() ?>
                <?php foreach (range('A', 'Z') as $letra):
                    $classe = in_array($letra, $acertos, true) ? 'acertou' : (in_array($letra, $erradas, true) ? 'errou' : '');
                    $usada = $classe !== '';
                ?>
                    <button type="submit" name="letra" value="<?= $letra ?>" class="tecla <?= $classe ?>" <?= $usada ? 'disabled' : '' ?>><?= $letra ?></button>
                <?php endforeach; ?>
            </form>
        </div>
    </div>
</section>

<!-- Modal: abandonar -->
<div class="modal" id="modal-abandonar" hidden>
    <div class="modal-fundo" data-modal-close></div>
    <div class="modal-caixa modal-pequeno" role="dialog" aria-modal="true">
        <h3>Abandonar a partida?</h3>
        <p>A partida será encerrada como <strong>derrota</strong> (0 pontos) e a palavra será revelada.</p>
        <form method="post" action="<?= e(url('/jogo/abandonar')) ?>" class="modal-acoes">
            <?= csrf_field() ?>
            <button type="button" class="btn btn-fantasma" data-modal-close>Continuar jogando</button>
            <button type="submit" class="btn btn-perigo" data-carregando>Abandonar</button>
        </form>
    </div>
</div>
