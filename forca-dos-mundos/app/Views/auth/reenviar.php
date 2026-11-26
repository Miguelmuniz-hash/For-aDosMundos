<?php $erros = form_erros(); ?>
<header class="auth-cabecalho">
    <span class="auth-etiqueta">Confirmação</span>
    <h1>Reenviar e-mail</h1>
    <p>Informe o e-mail do cadastro e enviaremos um novo link de confirmação.</p>
</header>

<?php require __DIR__ . '/../partials/alerta_dev.php'; ?>

<form method="post" action="<?= e(url('/reenviar-confirmacao')) ?>" class="form" data-validar novalidate>
    <?= csrf_field() ?>
    <div class="campo <?= isset($erros['email']) ? 'com-erro' : '' ?>">
        <label for="email">E-mail da conta</label>
        <input class="input" type="email" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email">
        <small class="campo-erro"><?= e($erros['email'] ?? '') ?></small>
    </div>
    <button type="submit" class="btn btn-primario btn-bloco" data-carregando>Reenviar link</button>
</form>

<p class="auth-rodape"><a href="<?= e(url('/login')) ?>" class="link">&larr; Voltar ao login</a></p>
