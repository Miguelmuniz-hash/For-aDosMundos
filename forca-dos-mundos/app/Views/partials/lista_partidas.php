<?php /** Lista das últimas partidas do jogador. Requer $ultimas. */ ?>
<?php if (!$ultimas): ?>
    <div class="vazio card">
        <svg viewBox="0 0 64 64" class="vazio-arte" fill="none" stroke="currentColor" stroke-width="2"><circle cx="32" cy="32" r="12"/><ellipse cx="32" cy="34" rx="26" ry="7" transform="rotate(-18 32 34)"/></svg>
        <p>Você ainda não jogou nenhuma partida.</p>
        <a href="<?= e(url('/mundos')) ?>" class="btn btn-primario btn-sm">Jogar agora</a>
    </div>
<?php else: ?>
    <div class="card tabela-caixa">
        <table class="tabela">
            <thead>
                <tr><th>Palavra</th><th>Mundo</th><th>Erros</th><th>Pontos</th><th>Resultado</th><th>Data</th></tr>
            </thead>
            <tbody>
                <?php foreach ($ultimas as $p): ?>
                    <tr>
                        <td data-rotulo="Palavra"><strong><?= e($p['palavra_jogada']) ?></strong></td>
                        <td data-rotulo="Mundo"><?= e($p['categoria_nome'] ?? 'Removido') ?></td>
                        <td data-rotulo="Erros"><?= (int) $p['erros'] ?>/6</td>
                        <td data-rotulo="Pontos" class="texto-ouro"><?= (int) $p['pontos'] ?></td>
                        <td data-rotulo="Resultado"><span class="badge <?= $p['resultado'] === 'vitoria' ? 'badge-ok' : 'badge-erro' ?>"><?= $p['resultado'] === 'vitoria' ? 'Vitória' : 'Derrota' ?></span></td>
                        <td data-rotulo="Data"><?= e(formatar_data($p['data_hora'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
