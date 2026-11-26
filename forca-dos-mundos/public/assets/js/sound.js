/**
 * Efeitos sonoros gerados pelo navegador (Web Audio API), sem arquivos de áudio.
 * O som é opcional e pode ser desligado no botão do topo ou em Configurações.
 */
(function () {
    'use strict';

    var contexto = null;

    function obterContexto() {
        if (!contexto) {
            var Classe = window.AudioContext || window.webkitAudioContext;
            if (!Classe) {
                return null;
            }
            contexto = new Classe();
        }
        if (contexto.state === 'suspended') {
            contexto.resume();
        }
        return contexto;
    }

    /** Toca uma nota: frequência, início (s), duração (s), tipo de onda. */
    function nota(frequencia, inicio, duracao, tipo, volumeRelativo) {
        var ctx = obterContexto();
        if (!ctx) {
            return;
        }
        var config = window.FDMConfig.ler();
        var volume = (config.volume / 100) * 0.25 * (volumeRelativo || 1);
        var oscilador = ctx.createOscillator();
        var ganho = ctx.createGain();
        var t0 = ctx.currentTime + inicio;

        oscilador.type = tipo || 'sine';
        oscilador.frequency.setValueAtTime(frequencia, t0);
        ganho.gain.setValueAtTime(0.0001, t0);
        ganho.gain.exponentialRampToValueAtTime(Math.max(volume, 0.0002), t0 + 0.02);
        ganho.gain.exponentialRampToValueAtTime(0.0001, t0 + duracao);

        oscilador.connect(ganho);
        ganho.connect(ctx.destination);
        oscilador.start(t0);
        oscilador.stop(t0 + duracao + 0.05);
    }

    var sons = {
        acerto: function () {
            nota(660, 0, 0.14, 'sine');
            nota(880, 0.08, 0.2, 'sine');
        },
        erro: function () {
            nota(220, 0, 0.18, 'sawtooth', 0.7);
            nota(160, 0.1, 0.25, 'sawtooth', 0.7);
        },
        vitoria: function () {
            [523, 659, 784, 1047].forEach(function (freq, i) {
                nota(freq, i * 0.13, 0.35, 'triangle');
            });
        },
        derrota: function () {
            [392, 330, 262, 196].forEach(function (freq, i) {
                nota(freq, i * 0.18, 0.4, 'sine');
            });
        },
        clique: function () {
            nota(500, 0, 0.06, 'sine', 0.6);
        }
    };

    window.FDMSom = {
        tocar: function (nome, forcar) {
            var config = window.FDMConfig.ler();
            if (!config.som && !forcar) {
                return;
            }
            if (sons[nome]) {
                sons[nome]();
            }
        }
    };

    // Botão de som no topo
    document.querySelectorAll('[data-alternar-som]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var config = window.FDMConfig.ler();
            config.som = !config.som;
            window.FDMConfig.salvar(config);
            if (config.som) {
                window.FDMSom.tocar('clique');
            }
            if (window.FDMToast) {
                window.FDMToast('info', config.som ? 'Som ativado.' : 'Som desativado.');
            }
        });
    });

    // Página de configurações
    var controles = document.querySelectorAll('[data-config]');
    if (controles.length) {
        var config = window.FDMConfig.ler();
        controles.forEach(function (controle) {
            var chave = controle.dataset.config;
            if (controle.type === 'checkbox') {
                controle.checked = !!config[chave];
            } else {
                controle.value = config[chave];
            }
            controle.addEventListener('input', function () {
                var atual = window.FDMConfig.ler();
                atual[chave] = controle.type === 'checkbox' ? controle.checked : parseInt(controle.value, 10);
                window.FDMConfig.salvar(atual);
            });
        });
        var testar = document.getElementById('cfg-testar');
        if (testar) {
            testar.addEventListener('click', function () {
                window.FDMSom.tocar('vitoria', true);
            });
        }
    }
})();
