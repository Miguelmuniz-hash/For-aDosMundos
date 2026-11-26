<?php $erros = form_erros(); ?>
<?php if (!$valido): ?>
    <header class="auth-cabecalho">
        <span class="auth-etiqueta">Link inválido</span>
        <h1>Link expirado</h1>
        <p>Este link de recuperação não é mais válido (ele vence em 30 minutos e só pode ser usado uma vez).</p>
    </header>
    <a href="<?= e(url('/esqueci-senha')) ?>" class="btn btn-primario btn-bloco">Solicitar novo link</a>
<?php else: ?>
    <header class="auth-cabecalho">
        <span class="auth-etiqueta">Quase lá</span>
        <h1>Crie uma nova senha</h1>
        <p>Escolha uma senha com pelo menos 6 caracteres.</p>
    </header>
    <form method="post" action="<?= e(url('/redefinir-senha')) ?>" class="form" data-validar novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="campo <?= isset($erros['senha']) ? 'com-erro' : '' ?>">
            <label for="senha">Nova senha</label>
            <div class="input-grupo">
                <input class="input" type="password" id="senha" name="senha" required minlength="6" autocomplete="new-password">
                <button type="button" class="input-olho" data-alternar-senha="senha" aria-label="Mostrar ou ocultar senha">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <small class="campo-erro"><?= e($erros['senha'] ?? '') ?></small>
        </div>
        <div class="campo <?= isset($erros['senha_confirmacao']) ? 'com-erro' : '' ?>">
            <label for="senha_confirmacao">Confirmar nova senha</label>
            <input class="input" type="password" id="senha_confirmacao" name="senha_confirmacao" required data-igual-a="senha" autocomplete="new-password">
            <small class="campo-erro"><?= e($erros['senha_confirmacao'] ?? '') ?></small>
        </div>
        <button type="submit" class="btn btn-primario btn-bloco" data-carregando>Salvar nova senha</button>
    </form>
<?php endif; ?>
