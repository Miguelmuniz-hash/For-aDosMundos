<h1>Criar Conta</h1>

<form action="<?= url('cadastro') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="nome">Nome Completo</label>
        <input type="text" id="nome" name="nome" value="<?= old('nome') ?>" required>
        <?php if ($err = errors('nome')): ?><span class="field-error"><?= e($err) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="apelido">Apelido (no jogo)</label>
        <input type="text" id="apelido" name="apelido" value="<?= old('apelido') ?>" required>
        <?php if ($err = errors('apelido')): ?><span class="field-error"><?= e($err) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
        <?php if ($err = errors('email')): ?><span class="field-error"><?= e($err) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="senha">Senha (mín. 6 caracteres)</label>
        <input type="password" id="senha" name="senha" required>
        <?php if ($err = errors('senha')): ?><span class="field-error"><?= e($err) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="confirmar_senha">Confirmar Senha</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required>
        <?php if ($err = errors('confirmar_senha')): ?><span class="field-error"><?= e($err) ?></span><?php endif; ?>
    </div>

    <button type="submit">Cadastrar</button>
</form>

<div class="links">
    <p>Já possui uma conta? <a href="<?= url('login') ?>">Fazer login</a></p>
</div>