<?php
$vitoria = $resultado['vitoria'];
$erros = (int) $resultado['erros'];
$forcaErros = $erros; // usado pelo partial do boneco da forca (derrota)

if ($vitoria) {
    if ($erros === 0) {
        $mensagem = 'Perfeito! Você desvendou o segredo sem nenhum erro.';
    } elseif ($erros <= 2) {
        $mensagem = 'Brilhante! Poucos exploradores chegam tão longe com tão poucos erros.';
    } elseif ($erros <= 4) {
        $mensagem = 'Muito bem! Você encontrou a palavra com sabedoria.';
    } else {
        $mensagem = 'Por um fio! A palavra foi sua no último instante.';
    }
} else {
    $mensagem = !empty($resultado['abandono'])
        ? 'Você abandonou esta missão. Os mundos esperam sua volta.'
        : 'Os mundos guardaram seu segredo desta vez. Tente de novo!';
}

// Estrelas só enfeitam a vitória (a pontuação em si vem do servidor).
$estrelas = !$vitoria ? 0 : ($erros <= 1 ? 3 : ($erros <= 3 ? 2 : 1));
?>
<section class="resultado <?= $vitoria ? 'resultado-vitoria' : 'resultado-derrota' ?>" id="resultado" data-vitoria="<?= $vitoria ? '1' : '0' ?>">
    <div class="resultado-efeitos" id="efeitos" aria-hidden="true"></div>

    <div class="card resultado-card">
        <div class="resultado-emblema">
            <?php if ($vitoria): ?>
                <svg class="trofeu-svg" viewBox="0 0 120 120" role="img" aria-label="Troféu dourado">
                    <defs>
                        <linearGradient id="gOuro" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#ffe9b0"/>
                            <stop offset=".5" stop-color="#f5c76b"/>
                            <stop offset="1" stop-color="#b8862b"/>
                        </linearGradient>
                    </defs>
                    <circle class="trofeu-aureola" cx="60" cy="60" r="55" fill="rgba(245,199,107,.07)" stroke="rgba(245,199,107,.4)" stroke-width="1.5"/>
                    <path d="M34 34H24a2 2 0 0 0-2 2c0 14 8 24 22 26" fill="none" stroke="url(#gOuro)" stroke-width="5" stroke-linecap="round"/>
                    <path d="M86 34h10a2 2 0 0 1 2 2c0 14-8 24-22 26" fill="none" stroke="url(#gOuro)" stroke-width="5" stroke-linecap="round"/>
                    <path d="M34 26h52v16c0 17-11 28-26 28S34 59 34 42z" fill="url(#gOuro)"/>
                    <rect x="55" y="70" width="10" height="14" fill="url(#gOuro)"/>
                    <rect x="47" y="84" width="26" height="8" rx="2" fill="url(#gOuro)"/>
                    <path d="M42 96a4 4 0 0 1 4-4h28a4 4 0 0 1 4 4v4H42z" fill="url(#gOuro)"/>
                    <path d="M60 33l3.6 7.4 8.1 1.1-5.9 5.7 1.4 8.1-7.2-3.9-7.2 3.9 1.4-8.1-5.9-5.7 8.1-1.1z" fill="#7a5410" opacity=".55"/>
                </svg>
                <div class="resultado-estrelas" role="img" aria-label="<?= $estrelas ?> de 3 estrelas">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <svg class="estrela <?= $i <= $estrelas ? 'acesa' : '' ?>" style="animation-delay: <?= 0.5 + $i * 0.25 ?>s" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.9 6.6 7.1.7-5.3 4.8 1.5 7-6.2-3.6-6.2 3.6 1.5-7L2 9.3l7.1-.7z"/></svg>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <div class="resultado-forca" aria-hidden="true">
                    <?php require __DIR__ . '/../partials/forca_svg.php'; ?>
                </div>
            <?php endif; ?>
        </div>

        <span class="resultado-selo"><?= $vitoria ? 'Vitória' : 'Fim de jogo' ?></span>
        <h1><?= $vitoria ? 'Você descobriu a palavra!' : 'A palavra escapou...' ?></h1>
        <p class="resultado-msg"><?= e($mensagem) ?></p>

        <div class="resultado-palavra" aria-label="Palavra secreta">
            <?php foreach (str_split($resultado['palavra']) as $i => $letra): ?>
                <span class="slot revelada" style="animation-delay: <?= $i * 0.07 ?>s"><?= e($letra) ?></span>
            <?php endforeach; ?>
        </div>

        <div class="resultado-dados">
            <div><small>Pontuação</small><strong class="texto-ouro" data-contar="<?= (int) $resultado['pontos'] ?>"><?= (int) $resultado['pontos'] ?></strong></div>
            <div><small>Erros</small><strong><?= $erros ?>/<?= (int) $maxErros ?></strong></div>
            <div><small>Mundo</small><strong><?= e($resultado['categoria']) ?></strong></div>
        </div>

        <?php if ($vitoria): ?>
            <p class="resultado-formula">100 + (<?= (int) $maxErros - $erros ?> × 20) = <?= (int) $resultado['pontos'] ?> pontos</p>
        <?php endif; ?>

        <div class="resultado-botoes">
            <form method="post" action="<?= e(url('/jogo/iniciar')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="categoria_id" value="<?= (int) $resultado['categoria_id'] ?>">
                <button type="submit" class="btn btn-primario" data-carregando><?= $vitoria ? 'Jogar novamente' : 'Tentar novamente' ?></button>
            </form>
            <a href="<?= e(url('/mundos')) ?>" class="btn btn-secundario">Outro mundo</a>
            <a href="<?= e(url('/ranking')) ?>" class="btn btn-fantasma">Ver ranking</a>
        </div>
    </div>
</section>
