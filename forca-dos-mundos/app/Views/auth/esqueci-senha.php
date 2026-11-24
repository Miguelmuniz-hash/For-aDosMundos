<?php
use App\Core\Session;

$linkRecuperacao = Session::flashed('link_recuperacao');
?>
<h1>Recuperar Senha</h1>

<?php if ($linkRecuperacao): ?>
    <div class="alert alert-sucesso">
        <strong>Modo de Teste Local:</strong><br>
        Clique no link abaixo para redefinir sua senha:<br><br>
        <a href="<?= e($linkRecuperacao) ?>" style="color:#fde047; font-weight:bold;">
            Redefinir minha senha agora
        </a>
    </div>
<?php endif; ?>

<form action="<?= url('esqueci-senha') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email">Digite o e-mail da sua conta</label>
        <input type="email" id="email" name="email" required autofocus>
    </div>

    <button type="submit">Enviar link de recuperação</button>
</form>

<div class="links">
    <p><a href="<?= url('login') ?>">Voltar para o login</a></p>
</div>