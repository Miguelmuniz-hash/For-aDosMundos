<section class="hero hero-compacto">
    <div class="hero-texto">
        <span class="auth-etiqueta">Seleção de mundos</span>
        <h1>Escolha onde jogar</h1>
        <p>Cada mundo guarda palavras secretas. Você tem 6 erros antes de perder. Vitória vale 100 pontos + 20 por erro que sobrou.</p>
    </div>
</section>
<?php if (!$categorias): ?>
    <div class="vazio card"><p>Nenhum mundo disponível no momento. Volte mais tarde!</p></div>
<?php else: ?>
    <div class="mundos-grade">
        <?php foreach ($categorias as $cat): require __DIR__ . '/../partials/card_mundo.php'; endforeach; ?>
    </div>
<?php endif; ?>
