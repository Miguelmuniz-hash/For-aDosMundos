<h1>Nova Senha</h1>

<form action="<?= url('redefinir-senha') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="form-group">
        <label for="senha">Nova Senha (mín. 6 caracteres)</label>
        <input type="password" id="senha" name="senha" required autofocus>
    </div>

    <div class="form-group">
        <label for="confirmar_senha">Confirmar Nova Senha</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required>
    </div>

    <button type="submit">Salvar Nova Senha</button>
</form>