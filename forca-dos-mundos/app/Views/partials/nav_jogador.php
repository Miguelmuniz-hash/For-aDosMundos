<?php
/** Menu lateral do jogador. Requer $usuario e $pagina. */
$itens = [
    'inicio'  => ['/inicio', 'Início', '<path d="M3 11l9-8 9 8v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>'],
    'mundos'  => ['/mundos', 'Jogar', '<circle cx="12" cy="12" r="5"/><path d="M2 14c4 3 14 1 20-4"/>'],
    'ranking' => ['/ranking', 'Ranking', '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>'],
    'perfil'  => ['/perfil', 'Meu perfil', '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
    'config'  => ['/configuracoes', 'Configurações', '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/>'],
];
?>
<nav class="menu" aria-label="Menu principal">
    <?php foreach ($itens as $chave => [$rota, $rotulo, $icone]): ?>
        <a href="<?= e(url($rota)) ?>" class="menu-item <?= ($pagina ?? '') === $chave ? 'ativo' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $icone ?></svg>
            <span><?= e($rotulo) ?></span>
        </a>
    <?php endforeach; ?>
    <?php if (($usuario['perfil'] ?? '') === 'admin'): ?>
        <a href="<?= e(url('/admin')) ?>" class="menu-item menu-admin">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/></svg>
            <span>Painel admin</span>
        </a>
    <?php endif; ?>
</nav>
