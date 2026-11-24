<h1 style="font-size:1.8rem; font-weight:800; margin-bottom:1.5rem;">🏆 Ranking Geral — Top 10 Partidas</h1>

<?php if (empty($top10)): ?>
    <div class="card"><p>Nenhuma partida pontuada registrada ainda.</p></div>
<?php else: ?>
    <div style="overflow-x:auto; margin-bottom:2.5rem;">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Posição</th>
                    <th>Jogador</th>
                    <th>Mundo</th>
                    <th>Palavra</th>
                    <th>Pontuação</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top10 as $idx => $item): ?>
                    <?php
                        $pos = $idx + 1;
                        $medalha = $pos === 1 ? '🥇' : ($pos === 2 ? '🥈' : ($pos === 3 ? '🥉' : $pos . 'º'));
                        $destaque = $pos <= 3 ? 'background-color:#FEF9C3;' : '';
                    ?>
                    <tr style="<?= $destaque ?>">
                        <td style="font-weight:800; font-size:1.1rem;"><?= $medalha ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:0.65rem;">
                                <?php if ($foto = foto_url($item)): ?>
                                    <img src="<?= e($foto) ?>" alt="" style="width:34px; height:34px; border-radius:50%; object-fit:cover; border:1px solid #0A0A0A;">
                                <?php endif; ?>
                                <strong><?= e($item['apelido']) ?></strong>
                            </div>
                        </td>
                        <td><?= e($item['categoria_nome'] ?? '-') ?></td>
                        <td><?= e($item['palavra_jogada']) ?></td>
                        <td style="font-weight:800; color:var(--cor-acerto);"><?= (int) $item['pontos'] ?> pts</td>
                        <td><?= formatar_data($item['data_hora']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h2 style="font-size:1.4rem; font-weight:800; margin-bottom:1rem;">📜 Suas 5 Últimas Partidas</h2>
<?php if (empty($ultimas)): ?>
    <div class="card"><p>Você ainda não jogou nenhuma partida.</p></div>
<?php else: ?>
    <div style="overflow-x:auto;">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Mundo</th>
                    <th>Palavra</th>
                    <th>Erros</th>
                    <th>Pontos</th>
                    <th>Resultado</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ultimas as $p): ?>
                    <tr>
                        <td><?= e($p['categoria_nome'] ?? '-') ?></td>
                        <td><strong><?= e($p['palavra_jogada']) ?></strong></td>
                        <td><?= (int) $p['erros'] ?> / 6</td>
                        <td><strong><?= (int) $p['pontos'] ?></strong></td>
                        <td><?= $p['resultado'] === 'vitoria' ? 'Vitória' : 'Derrota' ?></td>
                        <td><?= formatar_data($p['data_hora']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>