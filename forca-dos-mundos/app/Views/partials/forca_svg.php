<?php
/**
 * Boneco da forca em SVG com 7 estados (0 a 6 erros).
 * Requer $forcaErros. As partes aparecem com uma transição suave de "desenho".
 */
$forcaErros = (int) ($forcaErros ?? 0);
$partes = [
    1 => '<circle class="parte-cabeca" cx="150" cy="82" r="22" pathLength="100"/>',
    2 => '<line x1="150" y1="104" x2="150" y2="172" pathLength="100"/>',
    3 => '<line x1="150" y1="122" x2="118" y2="152" pathLength="100"/>',
    4 => '<line x1="150" y1="122" x2="182" y2="152" pathLength="100"/>',
    5 => '<line x1="150" y1="172" x2="124" y2="218" pathLength="100"/>',
    6 => '<line x1="150" y1="172" x2="176" y2="218" pathLength="100"/>',
];
?>
<svg class="forca-svg" id="forca-svg" viewBox="0 0 240 270" role="img" aria-label="Boneco da forca com <?= $forcaErros ?> erros" data-erros="<?= $forcaErros ?>">
    <defs>
        <linearGradient id="gMadeira" x1="0" x2="1">
            <stop offset="0" stop-color="#f5c76b"/>
            <stop offset="1" stop-color="#b8862b"/>
        </linearGradient>
        <linearGradient id="gCorpo" x1="0" x2="1">
            <stop offset="0" stop-color="#7ee7ff"/>
            <stop offset="1" stop-color="#a98bff"/>
        </linearGradient>
    </defs>
    <ellipse cx="110" cy="252" rx="92" ry="9" fill="rgba(124,92,255,.25)"/>
    <g class="forca-estrutura" stroke="url(#gMadeira)" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" fill="none">
        <path d="M30 250h160"/>
        <path d="M70 250V28h80"/>
        <path d="M70 62l32-34"/>
    </g>
    <line class="forca-corda" x1="150" y1="28" x2="150" y2="60" stroke="#e8ecff" stroke-width="3" stroke-linecap="round"/>
    <g class="forca-boneco" stroke="url(#gCorpo)" stroke-width="6" stroke-linecap="round" fill="none">
        <?php foreach ($partes as $numero => $desenho): ?>
            <g class="parte <?= $forcaErros >= $numero ? 'on' : '' ?>" data-parte="<?= $numero ?>"><?= $desenho ?></g>
        <?php endforeach; ?>
    </g>
</svg>
