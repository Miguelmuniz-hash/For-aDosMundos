<?php $erros = form_erros(); ?>
<header class="auth-cabecalho">
    <span class="auth-etiqueta">Nova jornada</span>
    <h1>Crie sua conta</h1>
    <p>Escolha um apelido: é ele que aparece no ranking.</p>
</header>

<form method="post" action="<?= e(url('/cadastro')) ?>" class="form" data-validar novalidate>
    <?= csrf_field() ?>
    <div class="campo <?= isset($erros['nome']) ? 'com-erro' : '' ?>">
        <label for="nome">Nome completo</label>
        <input class="input" type="text" id="nome" name="nome" value="<?= old('nome') ?>" required minlength="3" maxlength="120" autocomplete="name">
        <small class="campo-erro"><?= e($erros['nome'] ?? '') ?></small>
    </div>
    <div class="campo <?= isset($erros['apelido']) ? 'com-erro' : '' ?>">
        <label for="apelido">Apelido</label>
        <input class="input" type="text" id="apelido" name="apelido" value="<?= old('apelido') ?>" required minlength="3" maxlength="30" autocomplete="nickname">
        <small class="campo-erro"><?= e($erros['apelido'] ?? '') ?></small>
    </div>
    <div class="campo <?= isset($erros['email']) ? 'com-erro' : '' ?>">
        <label for="email">E-mail</label>
        <input class="input" type="email" id="email" name="email" value="<?= old('email') ?>" required maxlength="160" autocomplete="email">
        <small class="campo-erro"><?= e($erros['email'] ?? '') ?></small>
    </div>
    <div class="form-duas-colunas">
        <div class="campo <?= isset($erros['senha']) ? 'com-erro' : '' ?>">
            <label for="senha">Senha</label>
            <div class="input-grupo">
                <input class="input" type="password" id="senha" name="senha" required minlength="6" autocomplete="new-password">
                <button type="button" class="input-olho" data-alternar-senha="senha" aria-label="Mostrar ou ocultar senha">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <small class="campo-erro"><?= e($erros['senha'] ?? '') ?></small>
        </div>
        <div class="campo <?= isset($erros['senha_confirmacao']) ? 'com-erro' : '' ?>">
            <label for="senha_confirmacao">Confirmar senha</label>
            <input class="input" type="password" id="senha_confirmacao" name="senha_confirmacao" required data-igual-a="senha" autocomplete="new-password">
            <small class="campo-erro"><?= e($erros['senha_confirmacao'] ?? '') ?></small>
        </div>
    </div>
    <button type="submit" class="btn btn-primario btn-bloco" data-carregando>Criar conta</button>
</form>

<p class="auth-rodape">Já tem conta? <a href="<?= e(url('/login')) ?>" class="link">Entrar</a></p>
