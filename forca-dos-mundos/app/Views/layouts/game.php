<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $cssExtras = ['layout.css', 'game.css']; require __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="game-body" data-base="<?= e(url('')) ?>">
    <canvas id="estrelas" class="fundo-estelar" aria-hidden="true"></canvas>
    <?php require __DIR__ . '/../partials/toasts.php'; ?>

    <header class="game-header">
        <a class="game-logo" href="<?= e(url('/inicio')) ?>"><?php require __DIR__ . '/../partials/logo.php'; ?></a>
        <div class="topbar-acoes">
            <?php require __DIR__ . '/../partials/botao_som.php'; ?>
            <a href="<?= e(url('/perfil')) ?>" class="topbar-perfil">
                <?php $avatarUsuario = $usuario; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                <span><?= e($usuario['apelido']) ?></span>
            </a>
        </div>
    </header>

    <main class="game-main pagina-entrada"><?= $conteudo ?></main>

    <script src="<?= e(asset('js/starfield.js')) ?>"></script>
    <script src="<?= e(asset('js/sound.js')) ?>"></script>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
    <script src="<?= e(asset('js/game.js')) ?>"></script>
</body>
</html>
