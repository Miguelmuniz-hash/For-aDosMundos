/**
 * Tela da partida e do resultado.
 * ATENÇÃO: este script só cuida de animações e da comunicação com o servidor.
 * Quem decide acerto, erro, vitória, derrota e pontos é SEMPRE o PHP.
 */
(function () {
    'use strict';

    var token = document.querySelector('meta[name="csrf-token"]').content;

    function som(nome) {
        if (window.FDMSom) {
            window.FDMSom.tocar(nome);
        }
    }

    // ================================================== TELA DA PARTIDA
    var jogo = document.getElementById('jogo');

    if (jogo) {
        var teclado = document.getElementById('teclado');
        var slots = document.querySelectorAll('#palavra .slot');
        var partes = document.querySelectorAll('#forca-svg .parte');
        var forca = document.getElementById('forca-svg');
        var vidas = document.querySelectorAll('#vidas .vida');
        var ocupado = false;
        var encerrada = false; // true depois que o servidor avisa que a partida acabou

        function atualizarForca(erros) {
            partes.forEach(function (parte) {
                parte.classList.toggle('on', erros >= parseInt(parte.dataset.parte, 10));
            });
            vidas.forEach(function (vida, indice) {
                vida.classList.toggle('perdida', indice < erros);
            });
        }

        function enviarLetra(letra) {
            if (ocupado || encerrada) {
                return;
            }
            var tecla = teclado.querySelector('button[value="' + letra + '"]');
            if (!tecla || tecla.disabled) {
                return;
            }
            ocupado = true;
            teclado.classList.add('bloqueado');

            fetch(jogo.dataset.urlLetra, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': token
                },
                body: 'letra=' + encodeURIComponent(letra)
            })
                .then(function (resposta) {
                    return resposta.json();
                })
                .then(function (dados) {
                    if (!dados.ok) {
                        window.FDMToast('erro', dados.mensagem || 'Não foi possível jogar essa letra.');
                        if (dados.redirect) {
                            window.location.href = dados.redirect;
                        }
                        return;
                    }
                    processarResposta(letra, tecla, dados);
                })
                .catch(function () {
                    window.FDMToast('erro', 'Falha de conexão. Tente novamente.');
                })
                .finally(function () {
                    ocupado = false;
                    // Partida encerrada: o teclado continua travado até o redirecionamento.
                    // (Antes ele era liberado aqui, e apertar uma tecla nesse intervalo
                    // levava o jogador para /mundos com um aviso de erro.)
                    if (!encerrada) {
                        teclado.classList.remove('bloqueado');
                    }
                });
        }

        function processarResposta(letra, tecla, dados) {
            tecla.disabled = true;

            if (dados.acertou) {
                tecla.classList.add('acertou');
                dados.posicoes.forEach(function (posicao, ordem) {
                    var slot = slots[posicao];
                    window.setTimeout(function () {
                        slot.textContent = letra;
                        slot.classList.add('revelada');
                    }, ordem * 120);
                });
                som('acerto');
            } else {
                tecla.classList.add('errou');
                atualizarForca(dados.erros);
                forca.classList.remove('tremer');
                void forca.getBoundingClientRect(); // reinicia a animação
                forca.classList.add('tremer');
                som('erro');
            }

            document.getElementById('restantes').textContent = dados.restantes;
            var total = dados.progresso.total || 1;
            document.getElementById('progresso-texto').textContent = dados.progresso.reveladas + '/' + dados.progresso.total;
            document.getElementById('progresso-barra').style.width = Math.round(dados.progresso.reveladas / total * 100) + '%';

            // Quem informa que a partida terminou é o servidor.
            if (dados.status !== 'andamento' && dados.redirect) {
                encerrada = true;
                teclado.classList.add('bloqueado');
                window.setTimeout(function () {
                    window.location.href = dados.redirect;
                }, 1300);
            }
        }

        // Clique no teclado virtual
        teclado.addEventListener('click', function (evento) {
            var tecla = evento.target.closest('button.tecla');
            if (!tecla) {
                return;
            }
            evento.preventDefault();
            enviarLetra(tecla.value);
        });

        // Teclado físico (apenas atalho; o servidor valida a letra de qualquer forma)
        document.addEventListener('keydown', function (evento) {
            if (evento.ctrlKey || evento.metaKey || evento.altKey) {
                return;
            }
            if (document.querySelector('.modal:not([hidden])')) {
                return;
            }
            if (/^[a-zA-Z]$/.test(evento.key)) {
                enviarLetra(evento.key.toUpperCase());
            }
        });
    }

    // ================================================== TELA DE RESULTADO
    var resultado = document.getElementById('resultado');

    if (resultado) {
        var vitoria = resultado.dataset.vitoria === '1';
        var efeitos = document.getElementById('efeitos');
        var cores = ['#f5c76b', '#7c5cff', '#22d3ee', '#a855f7', '#3ddc97', '#ff5d7a'];

        // Chuva de confete (vitória) ou de estrelas apagadas (derrota)
        for (var i = 0; i < (vitoria ? 70 : 30); i++) {
            var peca = document.createElement('i');
            peca.className = vitoria ? 'confete' : 'estrela-cai';
            peca.style.left = Math.random() * 100 + '%';
            peca.style.animationDuration = (2.5 + Math.random() * 3) + 's';
            peca.style.animationDelay = Math.random() * 1.2 + 's';
            if (vitoria) {
                peca.style.background = cores[i % cores.length];
            }
            efeitos.appendChild(peca);
        }
        window.setTimeout(function () {
            efeitos.remove();
        }, 8000);

        // Contagem animada da pontuação (apenas visual)
        var contador = resultado.querySelector('[data-contar]');
        if (contador) {
            var alvo = parseInt(contador.dataset.contar, 10);
            var inicio = null;
            var animar = function (agora) {
                inicio = inicio || agora;
                var progresso = Math.min((agora - inicio) / 1100, 1);
                contador.textContent = Math.round(alvo * progresso);
                if (progresso < 1) {
                    window.requestAnimationFrame(animar);
                }
            };
            contador.textContent = '0';
            window.requestAnimationFrame(animar);
        }

        som(vitoria ? 'vitoria' : 'derrota');
    }
})();
