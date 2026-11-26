<?php
/** Menu lateral do painel administrativo. */
$itens = [
    'admin'      => ['/admin', 'Visão geral', '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>'],
    'categorias' => ['/admin/categorias', 'Categorias', '<circle cx="12" cy="12" r="4"/><ellipse cx="12" cy="13" rx="10" ry="3" transform="rotate(-15 12 13)"/>'],
    'palavras'   => ['/admin/palavras', 'Palavras', '<path d="M4 7V5h16v2M12 5v14M9 19h6"/>'],
    'usuarios'   => ['/admin/usuarios', 'Usuários', '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-5.5 7-5.5s7 2 7 5.5M17 11a3 3 0 1 0 0-6M22 19c0-2.5-2-4-5-4.5"/>'],
    'partidas'   => ['/admin/partidas', 'Partidas', '<path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/>'],
];
?>
<nav class="menu" aria-label="Menu administrativo">
    <?php foreach ($itens as $chave => [$rota, $rotulo, $icone]): ?>
        <a href="<?= e(url($rota)) ?>" class="menu-item <?= ($pagina ?? '') === $chave ? 'ativo' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $icone ?></svg>
            <span><?= e($rotulo) ?></span>
        </a>
    <?php endforeach; ?>
    <a href="<?= e(url('/inicio')) ?>" class="menu-item menu-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
        <span>Voltar ao jogo</span>
    </a>
</nav>
