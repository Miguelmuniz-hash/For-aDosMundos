<?php $vitoria = $partida['resultado'] === 'vitoria'; ?>

<div class="card" style="max-width:520px; margin:0 auto; text-align:center;">
    <div style="font-size:3.5rem; margin-bottom:0.5rem;">
        <?= $vitoria ? '🏆' : '💀' ?>
    </div>

    <h1 style="font-size:2rem; font-weight:800; color:<?= $vitoria ? 'var(--cor-acerto)' : 'var(--cor-erro)' ?>;">
        <?= $vitoria ? 'Você Venceu!' : 'Fim de Jogo!' ?>
    </h1>

    <p style="color:var(--cor-cinza-texto); margin:0.5rem 0 1.5rem 0;">
        <?= $vitoria ? 'Excelente! Você descobriu a palavra secreta deste mundo.' : 'Você atingiu o limite de 6 erros desta vez.' ?>
    </p>

    <div style="background:var(--bg-dica); border:2px solid var(--cor-preto); border-radius:10px; padding:1rem; margin-bottom:1.5rem;">
        <div style="font-size:0.85rem; font-weight:700; color:var(--cor-cinza-texto);">A PALAVRA ERA</div>
        <div style="font-size:2rem; font-weight:800; letter-spacing:4px; margin-top:0.25rem;">
            <?= e($partida['palavra_jogada']) ?>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.75rem;">
        <div style="border:2px solid var(--cor-preto); border-radius:8px; padding:0.75rem;">
            <div style="font-size:0.8rem; font-weight:700;">PONTUAÇÃO</div>
            <div style="font-size:1.5rem; font-weight:800; color:var(--cor-revelar);"><?= (int) $partida['pontos'] ?> pts</div>
        </div>
        <div style="border:2px solid var(--cor-preto); border-radius:8px; padding:0.75rem;">
            <div style="font-size:0.8rem; font-weight:700;">ERROS COMETIDOS</div>
            <div style="font-size:1.5rem; font-weight:800;"><?= (int) $partida['erros'] ?> / 6</div>
        </div>
    </div>

    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <?php if ($categoria && $categoria['status'] === 'ativa'): ?>
            <a href="<?= url('jogo/' . $categoria['id']) ?>" class="btn btn-success" style="flex:1;">Jogar Novamente</a>
        <?php endif; ?>
        <a href="<?= url('dashboard') ?>" class="btn btn-primary" style="flex:1;">Voltar ao Início</a>
    </div>
</div>