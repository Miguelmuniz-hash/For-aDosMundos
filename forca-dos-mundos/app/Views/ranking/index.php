<section class="hero hero-compacto">
    <div class="hero-texto">
        <span class="auth-etiqueta">Hall da fama</span>
        <h1>Ranking dos Mundos</h1>
        <p>As 10 maiores pontuações de todos os jogadores. Em caso de empate, vale quem conquistou primeiro.</p>
    </div>
</section>

<?php if (!$top): ?>
    <div class="vazio card">
        <p>Ainda não há vitórias registradas. Seja o primeiro no topo!</p>
        <a href="<?= e(url('/mundos')) ?>" class="btn btn-primario btn-sm">Jogar agora</a>
    </div>
<?php else: ?>
    <?php if (count($top) >= 3): ?>
        <div class="podio">
            <?php foreach ([1, 0, 2] as $i): $p = $top[$i]; ?>
                <div class="podio-item podio-<?= $i + 1 ?>">
                    <span class="podio-coroa"><?= $i === 0 ? '👑' : '' ?></span>
                    <?php $avatarUsuario = $p; $avatarClasse = 'avatar-lg'; require __DIR__ . '/../partials/avatar.php'; ?>
                    <strong><?= e($p['apelido']) ?></strong>
                    <span class="podio-pontos"><?= (int) $p['pontos'] ?> pts</span>
                    <div class="podio-base"><?= $i + 1 ?>º</div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card tabela-caixa">
        <table class="tabela tabela-ranking">
            <thead><tr><th>Posição</th><th>Jogador</th><th>Pontuação</th><th>Data da partida</th></tr></thead>
            <tbody>
                <?php foreach ($top as $i => $p): ?>
                    <tr class="<?= $i < 3 ? 'destaque destaque-' . ($i + 1) : '' ?> <?= $p['apelido'] === $usuario['apelido'] ? 'eu' : '' ?>">
                        <td data-rotulo="Posição"><span class="posicao"><?= $i + 1 ?>º</span></td>
                        <td data-rotulo="Jogador">
                            <span class="jogador-celula">
                                <?php $avatarUsuario = $p; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                                <?= e($p['apelido']) ?>
                            </span>
                        </td>
                        <td data-rotulo="Pontuação" class="texto-ouro"><strong><?= (int) $p['pontos'] ?></strong></td>
                        <td data-rotulo="Data"><?= e(formatar_data($p['data_hora'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<section>
    <div class="secao-titulo"><h2>Suas 5 últimas partidas</h2></div>
    <?php require __DIR__ . '/../partials/lista_partidas.php'; ?>
</section>
