<?php $erros = form_erros(); ?>
<header class="auth-cabecalho">
    <span class="auth-etiqueta">Bem-vindo, explorador</span>
    <h1>Entrar nos Mundos</h1>
    <p>Acesse sua conta para continuar sua jornada.</p>
</header>

<?php if (!empty($erros['geral'])): ?>
    <div class="alerta alerta-erro" role="alert"><?= e($erros['geral']) ?></div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/alerta_dev.php'; ?>

<form method="post" action="<?= e(url('/login')) ?>" class="form" data-validar novalidate>
    <?= csrf_field() ?>
    <div class="campo">
        <label for="email">E-mail</label>
        <input class="input" type="email" id="email" name="email" value="<?= old('email') ?>" autocomplete="email" required placeholder="voce@exemplo.com">
        <small class="campo-erro"></small>
    </div>
    <div class="campo">
        <label for="senha">Senha</label>
        <div class="input-grupo">
            <input class="input" type="password" id="senha" name="senha" autocomplete="current-password" required placeholder="Sua senha">
            <button type="button" class="input-olho" data-alternar-senha="senha" aria-label="Mostrar ou ocultar senha">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>
        <small class="campo-erro"></small>
    </div>
    <div class="form-linha-direita">
        <a href="<?= e(url('/esqueci-senha')) ?>" class="link">Esqueci minha senha</a>
        <a href="<?= e(url('/reenviar-confirmacao')) ?>" class="link">Reenviar confirmação</a>
    </div>
    <button type="submit" class="btn btn-primario btn-bloco" data-carregando>Entrar</button>
</form>

<p class="auth-rodape">Ainda não tem conta? <a href="<?= e(url('/cadastro')) ?>" class="link">Criar conta</a></p>
