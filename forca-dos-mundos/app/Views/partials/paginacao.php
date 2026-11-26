<?php
/** Paginação. Requer $pag (veja helpers.php: paginar). */
if (($pag['total_paginas'] ?? 1) <= 1) {
    return;
}
$inicio = max(1, $pag['pagina'] - 2);
$fim = min($pag['total_paginas'], $pag['pagina'] + 2);
?>
<nav class="paginacao" aria-label="Paginação">
    <?php if ($pag['pagina'] > 1): ?>
        <a href="<?= e(query_com(['pagina' => $pag['pagina'] - 1])) ?>" aria-label="Anterior">&lsaquo;</a>
    <?php endif; ?>
    <?php for ($i = $inicio; $i <= $fim; $i++): ?>
        <a href="<?= e(query_com(['pagina' => $i])) ?>" class="<?= $i === $pag['pagina'] ? 'ativa' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($pag['pagina'] < $pag['total_paginas']): ?>
        <a href="<?= e(query_com(['pagina' => $pag['pagina'] + 1])) ?>" aria-label="Próxima">&rsaquo;</a>
    <?php endif; ?>
    <span class="paginacao-info"><?= (int) $pag['total'] ?> registros</span>
</nav>
