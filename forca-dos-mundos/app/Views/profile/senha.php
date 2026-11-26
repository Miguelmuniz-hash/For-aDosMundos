<?php $erros = form_erros(); ?>
<section class="card form-estreito">
    <h1 class="card-titulo">Alterar senha</h1>
    <p class="texto-suave">Para sua segurança, confirme a senha atual antes de escolher uma nova.</p>
    <form method="post" action="<?= e(url('/perfil/senha')) ?>" class="form" data-validar novalidate>
        <?= csrf_field() ?>
        <div class="campo <?= isset($erros['senha_atual']) ? 'com-erro' : '' ?>">
            <label for="senha_atual">Senha atual</label>
            <input class="input" type="password" id="senha_atual" name="senha_atual" required autocomplete="current-password">
            <small class="campo-erro"><?= e($erros['senha_atual'] ?? '') ?></small>
        </div>
        <div class="campo <?= isset($erros['nova_senha']) ? 'com-erro' : '' ?>">
            <label for="nova_senha">Nova senha</label>
            <div class="input-grupo">
                <input class="input" type="password" id="nova_senha" name="nova_senha" required minlength="6" autocomplete="new-password">
                <button type="button" class="input-olho" data-alternar-senha="nova_senha" aria-label="Mostrar ou ocultar senha">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <small class="campo-erro"><?= e($erros['nova_senha'] ?? '') ?></small>
        </div>
        <div class="campo <?= isset($erros['nova_senha_confirmacao']) ? 'com-erro' : '' ?>">
            <label for="nova_senha_confirmacao">Confirmar nova senha</label>
            <input class="input" type="password" id="nova_senha_confirmacao" name="nova_senha_confirmacao" required data-igual-a="nova_senha" autocomplete="new-password">
            <small class="campo-erro"><?= e($erros['nova_senha_confirmacao'] ?? '') ?></small>
        </div>
        <div class="form-botoes">
            <button type="submit" class="btn btn-primario" data-carregando>Salvar nova senha</button>
            <a href="<?= e(url('/perfil')) ?>" class="btn btn-fantasma">Cancelar</a>
        </div>
    </form>
</section>
