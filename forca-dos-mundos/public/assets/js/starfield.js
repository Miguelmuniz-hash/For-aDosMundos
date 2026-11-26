/**
 * Céu estrelado animado do fundo (canvas).
 * Também lê as preferências salvas no navegador (configurações do jogador).
 */
(function () {
    'use strict';

    var CHAVE = 'fdm_config';
    var padrao = { som: true, volume: 60, estrelas: true, reduzir: false };

    function lerConfig() {
        var config = Object.assign({}, padrao);
        try {
            var salvo = JSON.parse(localStorage.getItem(CHAVE) || '{}');
            Object.assign(config, salvo);
        } catch (erro) {
            // Sem localStorage: usa o padrão.
        }
        return config;
    }

    function aplicarClasses(config) {
        document.body.classList.toggle('sem-estrelas', !config.estrelas);
        document.body.classList.toggle('reduzir-animacoes', !!config.reduzir);
        document.body.classList.toggle('som-desligado', !config.som);
    }

    // Disponível para os outros scripts.
    window.FDMConfig = {
        ler: lerConfig,
        salvar: function (config) {
            try {
                localStorage.setItem(CHAVE, JSON.stringify(config));
            } catch (erro) {
                // Ignora erro de armazenamento.
            }
            aplicarClasses(config);
        },
        aplicar: aplicarClasses
    };

    var config = lerConfig();
    aplicarClasses(config);

    var canvas = document.getElementById('estrelas');
    if (!canvas || !config.estrelas) {
        return;
    }

    var ctx = canvas.getContext('2d');
    var estrelas = [];
    var largura = 0;
    var altura = 0;
    var cometa = null;
    var movimentoReduzido = window.matchMedia('(prefers-reduced-motion: reduce)').matches || config.reduzir;

    function redimensionar() {
        var escala = window.devicePixelRatio || 1;
        largura = window.innerWidth;
        altura = window.innerHeight;
        canvas.width = largura * escala;
        canvas.height = altura * escala;
        ctx.setTransform(escala, 0, 0, escala, 0, 0);

        var quantidade = Math.min(160, Math.floor((largura * altura) / 9000));
        estrelas = [];
        for (var i = 0; i < quantidade; i++) {
            estrelas.push({
                x: Math.random() * largura,
                y: Math.random() * altura,
                raio: Math.random() * 1.4 + 0.3,
                fase: Math.random() * Math.PI * 2,
                velocidade: Math.random() * 0.02 + 0.005,
                deriva: Math.random() * 0.06 + 0.01,
                dourada: Math.random() < 0.08
            });
        }
    }

    function desenhar() {
        ctx.clearRect(0, 0, largura, altura);

        estrelas.forEach(function (estrela) {
            estrela.fase += estrela.velocidade;
            var brilho = 0.45 + Math.sin(estrela.fase) * 0.4;
            ctx.beginPath();
            ctx.fillStyle = estrela.dourada
                ? 'rgba(245, 199, 107, ' + brilho + ')'
                : 'rgba(210, 220, 255, ' + brilho + ')';
            ctx.arc(estrela.x, estrela.y, estrela.raio, 0, Math.PI * 2);
            ctx.fill();

            if (!movimentoReduzido) {
                estrela.y += estrela.deriva;
                if (estrela.y > altura + 2) {
                    estrela.y = -2;
                    estrela.x = Math.random() * largura;
                }
            }
        });

        if (!movimentoReduzido) {
            if (!cometa && Math.random() < 0.0025) {
                cometa = { x: Math.random() * largura * 0.8, y: Math.random() * altura * 0.4, vida: 0 };
            }
            if (cometa) {
                cometa.x += 7;
                cometa.y += 3.5;
                cometa.vida += 1;
                var gradiente = ctx.createLinearGradient(cometa.x, cometa.y, cometa.x - 90, cometa.y - 45);
                gradiente.addColorStop(0, 'rgba(255, 255, 255, 0.9)');
                gradiente.addColorStop(1, 'rgba(255, 255, 255, 0)');
                ctx.strokeStyle = gradiente;
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.moveTo(cometa.x, cometa.y);
                ctx.lineTo(cometa.x - 90, cometa.y - 45);
                ctx.stroke();
                if (cometa.vida > 70) {
                    cometa = null;
                }
            }
            window.requestAnimationFrame(desenhar);
        }
    }

    window.addEventListener('resize', redimensionar);
    redimensionar();
    desenhar();
})();
