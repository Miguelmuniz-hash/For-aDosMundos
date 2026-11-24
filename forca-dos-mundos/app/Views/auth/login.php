<h1>Entrar</h1>

<form action="<?= url('login') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= old('email') ?>" required autofocus>
    </div>

    <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>
    </div>

    <button type="submit">Entrar</button>
</form>

<div class="links">
    <p><a href="<?= url('esqueci-senha') ?>">Esqueci minha senha</a></p>
    <p>Ainda não tem conta? <a href="<?= url('cadastro') ?>">Cadastre-se</a></p>
</div>