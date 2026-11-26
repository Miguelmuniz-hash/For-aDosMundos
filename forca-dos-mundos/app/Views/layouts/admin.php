<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $cssExtras = ['layout.css', 'home.css', 'admin.css']; require __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="app-body admin-body" data-base="<?= e(url('')) ?>">
    <canvas id="estrelas" class="fundo-estelar" aria-hidden="true"></canvas>
    <?php require __DIR__ . '/../partials/toasts.php'; ?>

    <div class="shell">
        <aside class="sidebar sidebar-admin" id="sidebar">
            <a class="sidebar-logo" href="<?= e(url('/admin')) ?>"><?php require __DIR__ . '/../partials/logo.php'; ?></a>
            <span class="selo-admin">Painel Administrativo</span>
            <?php require __DIR__ . '/../partials/nav_admin.php'; ?>
            <div class="sidebar-rodape">
                <div class="sidebar-usuario">
                    <?php $avatarUsuario = $usuario; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                    <span><strong><?= e($usuario['apelido']) ?></strong><small>Administrador</small></span>
                </div>
                <?php require __DIR__ . '/../partials/botao_sair.php'; ?>
            </div>
        </aside>
        <div class="sidebar-fundo" data-fechar-menu></div>

        <div class="coluna">
            <header class="topbar">
                <button type="button" class="btn-icone menu-toggle" data-abrir-menu aria-label="Abrir menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <nav class="crumbs" aria-label="Você está em">
                    <a href="<?= e(url('/admin')) ?>">Painel</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                    <span><?= e($titulo ?? '') ?></span>
                </nav>
                <div class="topbar-acoes">
                    <span class="badge badge-ouro">Administrador</span>
                    <a href="<?= e(url('/perfil')) ?>" class="topbar-perfil">
                        <?php $avatarUsuario = $usuario; $avatarClasse = 'avatar-sm'; require __DIR__ . '/../partials/avatar.php'; ?>
                        <span><?= e($usuario['apelido']) ?></span>
                    </a>
                </div>
            </header>
            <main class="pagina pagina-entrada"><?= $conteudo ?></main>
        </div>
    </div>

    <!-- Modal genérico de confirmação (exclusões e mudanças de status) -->
    <div class="modal" id="modal-confirmar" hidden>
        <div class="modal-fundo" data-modal-close></div>
        <div class="modal-caixa modal-pequeno" role="dialog" aria-modal="true" aria-labelledby="confirmar-titulo">
            <h3 id="confirmar-titulo">Confirmar ação</h3>
            <p id="confirmar-texto">Tem certeza?</p>
            <form method="post" id="confirmar-form" class="modal-acoes">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="confirmar-id" value="">
                <button type="button" class="btn btn-fantasma" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-perigo" data-carregando>Confirmar</button>
            </form>
        </div>
    </div>

    <script src="<?= e(asset('js/starfield.js')) ?>"></script>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
    <script src="<?= e(asset('js/forms.js')) ?>"></script>
    <script src="<?= e(asset('js/admin.js')) ?>"></script>
</body>
</html>
