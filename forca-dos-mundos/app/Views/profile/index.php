<?php $erros = form_erros(); ?>
<section class="perfil-topo card">
    <div class="perfil-foto">
        <?php $avatarUsuario = $usuario; $avatarClasse = 'avatar-xl'; require __DIR__ . '/../partials/avatar.php'; ?>
    </div>
    <div class="perfil-info">
        <h1><?= e($usuario['apelido']) ?></h1>
        <p><?= e($usuario['nome']) ?> · <?= e($usuario['email']) ?></p>
        <p class="perfil-desde">Explorador desde <?= e(formatar_data($usuario['criado_em'], false)) ?>
            <?php if ($usuario['perfil'] === 'admin'): ?><span class="badge badge-ouro">Administrador</span><?php endif; ?>
        </p>
    </div>
    <div class="perfil-numeros">
        <div><strong><?= (int) $stats['total'] ?></strong><small>Partidas</small></div>
        <div><strong><?= (int) $stats['vitorias'] ?></strong><small>Vitórias</small></div>
        <div><strong class="texto-ouro"><?= (int) $stats['maior'] ?></strong><small>Maior pontuação</small></div>
    </div>
</section>

<div class="perfil-grade">
    <!-- Foto de perfil -->
    <section class="card">
        <h2 class="card-titulo">Foto de perfil</h2>
        <form method="post" action="<?= e(url('/perfil/foto')) ?>" enctype="multipart/form-data" class="form" id="form-foto">
            <?= csrf_field() ?>
            <div class="foto-editor">
                <div class="foto-previa" id="foto-previa">
                    <?php if (foto_url($usuario)): ?>
                        <img src="<?= e(foto_url($usuario)) ?>" alt="Pré-visualização" id="foto-previa-img">
                    <?php else: ?>
                        <img src="" alt="Pré-visualização" id="foto-previa-img" hidden>
                        <span id="foto-previa-iniciais"><?= e(iniciais($usuario['apelido'])) ?></span>
                    <?php endif; ?>
                </div>
                <div class="foto-controles">
                    <label class="btn btn-secundario btn-sm" for="foto">Escolher imagem</label>
                    <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" hidden>
                    <small>JPG, JPEG, PNG ou WEBP, até 2 MB.</small>
                    <small class="campo-erro" id="foto-erro"></small>
                </div>
            </div>
            <div class="form-botoes">
                <button type="submit" class="btn btn-primario btn-sm" id="foto-salvar" data-carregando disabled>Salvar foto</button>
            </div>
        </form>
        <?php if (foto_url($usuario)): ?>
            <form method="post" action="<?= e(url('/perfil/foto/remover')) ?>" class="form-inline-remover">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-fantasma btn-sm">Remover foto atual</button>
            </form>
        <?php endif; ?>
    </section>

    <!-- Dados -->
    <section class="card">
        <h2 class="card-titulo">Informações pessoais</h2>
        <form method="post" action="<?= e(url('/perfil/dados')) ?>" class="form" data-validar novalidate>
            <?= csrf_field() ?>
            <div class="campo <?= isset($erros['nome']) ? 'com-erro' : '' ?>">
                <label for="nome">Nome completo</label>
                <input class="input" type="text" id="nome" name="nome" value="<?= old('nome', $usuario['nome']) ?>" required minlength="3" maxlength="120">
                <small class="campo-erro"><?= e($erros['nome'] ?? '') ?></small>
            </div>
            <div class="campo <?= isset($erros['apelido']) ? 'com-erro' : '' ?>">
                <label for="apelido">Apelido</label>
                <input class="input" type="text" id="apelido" name="apelido" value="<?= old('apelido', $usuario['apelido']) ?>" required minlength="3" maxlength="30">
                <small class="campo-erro"><?= e($erros['apelido'] ?? '') ?></small>
            </div>
            <div class="campo <?= isset($erros['email']) ? 'com-erro' : '' ?>">
                <label for="email">E-mail</label>
                <input class="input" type="email" id="email" name="email" value="<?= old('email', $usuario['email']) ?>" required maxlength="160">
                <small class="campo-erro"><?= e($erros['email'] ?? '') ?></small>
            </div>
            <div class="form-botoes">
                <button type="submit" class="btn btn-primario" data-carregando>Salvar alterações</button>
                <a href="<?= e(url('/perfil/senha')) ?>" class="btn btn-secundario">Alterar senha</a>
            </div>
        </form>
    </section>
</div>

<!-- Zona de perigo -->
<section class="card zona-perigo">
    <div>
        <h2 class="card-titulo texto-perigo">Zona de perigo</h2>
        <p>Excluir a conta é <strong>permanente</strong>: seus dados pessoais e sua foto serão apagados e não podem ser recuperados. Suas partidas permanecem apenas de forma anônima, fora do ranking.</p>
    </div>
    <button type="button" class="btn btn-perigo" data-modal-open="modal-excluir">Excluir minha conta</button>
</section>

<div class="modal" id="modal-excluir" hidden>
    <div class="modal-fundo" data-modal-close></div>
    <div class="modal-caixa modal-pequeno" role="dialog" aria-modal="true" aria-labelledby="excluir-titulo">
        <h3 id="excluir-titulo">Excluir conta definitivamente?</h3>
        <p>Esta ação não pode ser desfeita. Digite sua senha para confirmar.</p>
        <form method="post" action="<?= e(url('/perfil/excluir')) ?>" class="form">
            <?= csrf_field() ?>
            <div class="campo">
                <label for="senha-excluir">Sua senha</label>
                <input class="input" type="password" id="senha-excluir" name="senha" required autocomplete="current-password">
            </div>
            <div class="modal-acoes">
                <button type="button" class="btn btn-fantasma" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-perigo" data-carregando>Excluir para sempre</button>
            </div>
        </form>
    </div>
</div>
