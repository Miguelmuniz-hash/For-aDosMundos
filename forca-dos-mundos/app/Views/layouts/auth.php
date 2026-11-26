<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php $cssExtras = ['auth.css']; require __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="auth-body" data-base="<?= e(url('')) ?>">
    <canvas id="estrelas" class="fundo-estelar" aria-hidden="true"></canvas>
    <?php require __DIR__ . '/../partials/toasts.php'; ?>

    <main class="auth-layout">
        <!-- Lado esquerdo: ilustração temática -->
        <section class="auth-arte" aria-hidden="true">
            <a class="auth-logo" href="<?= e(url('/')) ?>"><?php require __DIR__ . '/../partials/logo.php'; ?></a>
            <div class="auth-cena">
                <svg viewBox="0 0 520 440" class="cena-svg">
                    <defs>
                        <radialGradient id="gPlaneta" cx="35%" cy="30%" r="80%">
                            <stop offset="0" stop-color="#7ee7ff"/>
                            <stop offset=".45" stop-color="#6d5cff"/>
                            <stop offset="1" stop-color="#1b1456"/>
                        </radialGradient>
                        <radialGradient id="gLua" cx="30%" cy="30%" r="80%">
                            <stop offset="0" stop-color="#ffeec2"/>
                            <stop offset="1" stop-color="#c98e2a"/>
                        </radialGradient>
                        <linearGradient id="gAnel" x1="0" x2="1">
                            <stop offset="0" stop-color="#f5c76b" stop-opacity=".1"/>
                            <stop offset=".5" stop-color="#f5c76b"/>
                            <stop offset="1" stop-color="#f5c76b" stop-opacity=".1"/>
                        </linearGradient>
                    </defs>
                    <g class="cena-constelacao" stroke="rgba(190,200,255,.35)" stroke-width="1.2" fill="none">
                        <polyline points="40,60 110,100 170,70 240,120 300,80"/>
                        <polyline points="380,50 430,110 480,90"/>
                    </g>
                    <g fill="#fff">
                        <circle cx="40" cy="60" r="3"/><circle cx="110" cy="100" r="2.5"/><circle cx="170" cy="70" r="3"/>
                        <circle cx="240" cy="120" r="2.5"/><circle cx="300" cy="80" r="3"/>
                        <circle cx="380" cy="50" r="2.5"/><circle cx="430" cy="110" r="3"/><circle cx="480" cy="90" r="2.5"/>
                    </g>
                    <g class="cena-planeta">
                        <circle cx="260" cy="230" r="105" fill="url(#gPlaneta)"/>
                        <path d="M170 215q90 -40 180 10M165 250q95 -30 190 14" stroke="rgba(255,255,255,.18)" stroke-width="6" fill="none" stroke-linecap="round"/>
                        <ellipse cx="260" cy="238" rx="190" ry="38" fill="none" stroke="url(#gAnel)" stroke-width="9" transform="rotate(-16 260 238)"/>
                    </g>
                    <g class="cena-lua">
                        <circle cx="430" cy="320" r="26" fill="url(#gLua)"/>
                        <circle cx="422" cy="312" r="5" fill="rgba(0,0,0,.15)"/>
                    </g>
                    <!-- Forca em silhueta sobre a montanha -->
                    <path d="M0 440V380q70 -50 140 -10t120 -20 130 25 130 -25v90z" fill="#0a0f2b"/>
                    <g stroke="#f5c76b" stroke-width="5" stroke-linecap="round" fill="none">
                        <path d="M70 392V330M70 332h34M70 350l16 -18"/>
                        <path d="M104 332v10"/>
                    </g>
                    <circle cx="104" cy="350" r="8" stroke="#f5c76b" stroke-width="4" fill="none"/>
                </svg>
            </div>
            <div class="auth-frase">
                <h2>Cada letra é uma descoberta.</h2>
                <p>Explore mundos, desvende palavras secretas e conquiste o topo do ranking.</p>
            </div>
        </section>

        <!-- Lado direito: conteúdo da página -->
        <section class="auth-painel">
            <a class="auth-logo auth-logo-mobile" href="<?= e(url('/')) ?>"><?php require __DIR__ . '/../partials/logo.php'; ?></a>
            <div class="auth-caixa"><?= $conteudo ?></div>
        </section>
    </main>

    <script src="<?= e(asset('js/starfield.js')) ?>"></script>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
    <script src="<?= e(asset('js/forms.js')) ?>"></script>
</body>
</html>
