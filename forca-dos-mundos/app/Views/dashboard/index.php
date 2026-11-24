<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <?php if ($foto = foto_url($usuario)): ?>
            <img src="<?= e($foto) ?>" alt="Foto" style="width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid var(--cor-preto);">
        <?php else: ?>
            <div style="width:64px; height:64px; border-radius:50%; background:var(--cor-preto); color:var(--cor-branco); display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:800;">
                <?= e(mb_strtoupper(mb_substr($usuario['apelido'], 0, 1))) ?>
            </div>
        <?php endif; ?>
        <div>
            <h1 style="font-size:1.75rem; font-weight:800;">Olá, <?= e($usuario['apelido']) ?>!</h1>
            <p style="color:var(--cor-cinza-texto);">Escolha um mundo abaixo para iniciar uma nova partida.</p>
        </div>
    </div>
    <div style="display:flex; gap:0.75rem;">
        <a href="<?= url('ranking') ?>" class="btn btn-outline">🏆 Ver Ranking</a>
        <a href="<?= url('perfil') ?>" class="btn btn-primary">👤 Meu Perfil</a>
    </div>
</div>

<!-- Cards Estatísticos -->
<div class="grid-cards" style="margin-bottom:2.5rem;">
    <div class="card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:0.85rem; color:var(--cor-cinza-texto); font-weight:700;">PARTIDAS</div>
        <div style="font-size:2rem; font-weight:800;"><?= $stats['total_partidas'] ?></div>
    </div>
    <div class="card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:0.85rem; color:var(--cor-cinza-texto); font-weight:700;">VITÓRIAS / DERROTAS</div>
        <div style="font-size:2rem; font-weight:800;">
            <span style="color:var(--cor-acerto);"><?= $stats['vitorias'] ?></span> / <span style="color:var(--cor-erro);"><?= $stats['derrotas'] ?></span>
        </div>
    </div>
    <div class="card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:0.85rem; color:var(--cor-cinza-texto); font-weight:700;">TAXA DE VITÓRIA</div>
        <div style="font-size:2rem; font-weight:800;"><?= $stats['taxa_vitorias'] ?>%</div>
    </div>
    <div class="card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:0.85rem; color:var(--cor-cinza-texto); font-weight:700;">PONTOS ACUMULADOS</div>
        <div style="font-size:2rem; font-weight:800;"><?= $stats['total_pontos'] ?></div>
    </div>
    <div class="card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:0.85rem; color:var(--cor-cinza-texto); font-weight:700;">RECORDE</div>
        <div style="font-size:2rem; font-weight:800; color:var(--cor-revelar);"><?= $stats['maior_pontuacao'] ?> pts</div>
    </div>
</div>

<!-- Explore os Mundos (Categorias) -->
<h2 style="font-size:1.4rem; font-weight:800; margin-bottom:1rem;">🪐 Explore os Mundos</h2>
<?php if (empty($categorias)): ?>
    <div class="card"><p>Nenhum mundo ativo disponível no momento.</p></div>
<?php else: ?>
    <div class="grid-cards" style="margin-bottom:2.5rem;">
        <?php foreach ($categorias as $cat): ?>
            <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    <div style="font-size:2.2rem; margin-bottom:0.5rem;"><?= e($cat['icone'] ?: '🌍') ?></div>
                    <h3 style="font-size:1.25rem; font-weight:800; margin-bottom:0.4rem;"><?= e($cat['nome']) ?></h3>
                    <p style="color:var(--cor-cinza-texto); font-size:0.92rem; margin-bottom:1rem;">
                        <?= e($cat['descricao'] ?: 'Desafie seus conhecimentos neste mundo.') ?>
                    </p>
                </div>
                <div>
                    <div style="font-size:0.82rem; font-weight:700; margin-bottom:0.75rem; color:var(--cor-cinza-texto);">
                        <?= (int) $cat['total_palavras'] ?> palavras disponíveis
                    </div>
                    <?php if ((int) $cat['total_palavras'] > 0): ?>
                        <a href="<?= url('jogo/' . $cat['id']) ?>" class="btn btn-success btn-block">Jogar Agora</a>
                    <?php else: ?>
                        <button class="btn btn-outline btn-block" disabled>Sem palavras</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Últimas Partidas -->
<h2 style="font-size:1.4rem; font-weight:800; margin-bottom:1rem;">📜 Suas Últimas Partidas</h2>
<?php if (empty($ultimas)): ?>
    <div class="card"><p>Você ainda não jogou nenhuma partida. Escolha um mundo acima para começar!</p></div>
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
                        <td><?= e($p['categoria_nome'] ?? 'Removido') ?></td>
                        <td><strong><?= e($p['palavra_jogada']) ?></strong></td>
                        <td><?= (int) $p['erros'] ?> / 6</td>
                        <td><strong><?= (int) $p['pontos'] ?></strong></td>
                        <td>
                            <?php if ($p['resultado'] === 'vitoria'): ?>
                                <span style="color:var(--cor-acerto); font-weight:800;">Vitória</span>
                            <?php else: ?>
                                <span style="color:var(--cor-erro); font-weight:800;">Derrota</span>
                            <?php endif; ?>
                        </td>
                        <td><?= formatar_data($p['data_hora']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>