<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $cssExtras = ['layout.css', 'home.css', 'profile.css', 'ranking.css']; require __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="app-body" data-base="<?= e(url('')) ?>">
    <canvas id="estrelas" class="fundo-estelar" aria-hidden="true"></canvas>
    <?php require __DIR__ . '/../partials/toasts.php'; ?>

    <div class="shell">
        <aside class="sidebar" id="sidebar">
            <a class="sidebar-logo" href="<?= e(url('/inicio')) ?>"><?php require __DIR__ . '/../partials/logo.php'; ?></a>
            <?php require __DIR__ . '/../partials/nav_jogador.php'; ?>
            <div class="sidebar-rodape">
                <?php require __DIR__ . '/../partials/botao_sair.php'; ?>
            </div>
        </aside>
        <div class="sidebar-fundo" data-fechar-menu></div>

        <div class="coluna">
            <header class="topbar">
                <button type="button" class="btn-icone menu-toggle" data-abrir-menu aria-label="Abrir menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <div class="topbar-titulo"><?= e($titulo ?? '') ?></div>
                <div class="topbar-acoes">
                    <?php require __DIR__ . '/../partials/botao_som.php'; ?>
                    <a href="<?= e(url('/perfil')) ?>" class="topbar-perfil">
                        <?php $avatarUsuario = $usuario; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                        <span><?= e($usuario['apelido']) ?></span>
                    </a>
                </div>
            </header>
            <main class="pagina pagina-entrada"><?= $conteudo ?></main>
        </div>
    </div>

    <script src="<?= e(asset('js/starfield.js')) ?>"></script>
    <script src="<?= e(asset('js/sound.js')) ?>"></script>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
    <script src="<?= e(asset('js/forms.js')) ?>"></script>
    <script src="<?= e(asset('js/profile.js')) ?>"></script>
</body>
</html>
