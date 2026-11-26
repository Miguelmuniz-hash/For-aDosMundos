/**
 * Interface comum: toasts, modais, menu lateral, botões de carregamento,
 * visualizar senha e transição de páginas.
 */
(function () {
    'use strict';

    // ------------------------------------------------------------ Toasts
    var area = document.getElementById('toasts');

    function mostrarToast(tipo, mensagem) {
        if (!area) {
            return;
        }
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + tipo;
        toast.setAttribute('role', 'status');

        var texto = document.createElement('span');
        texto.textContent = mensagem; // textContent: sem risco de XSS

        var fechar = document.createElement('button');
        fechar.type = 'button';
        fechar.setAttribute('aria-label', 'Fechar');
        fechar.textContent = '\u00d7';

        toast.appendChild(texto);
        toast.appendChild(fechar);
        area.appendChild(toast);

        function remover() {
            toast.classList.add('saindo');
            window.setTimeout(function () {
                toast.remove();
            }, 300);
        }

        fechar.addEventListener('click', remover);
        window.setTimeout(remover, 5500);
    }

    window.FDMToast = mostrarToast;

    if (area && area.dataset.mensagens) {
        try {
            JSON.parse(area.dataset.mensagens).forEach(function (item) {
                mostrarToast(item.tipo, item.mensagem);
            });
        } catch (erro) {
            // Mensagens inválidas são ignoradas.
        }
    }

    // ------------------------------------------------------------- Modais
    function abrirModal(id) {
        var modal = document.getElementById(id);
        if (!modal) {
            return null;
        }
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        var primeiro = modal.querySelector('input:not([type=hidden]), select, textarea, button.btn-primario');
        if (primeiro) {
            window.setTimeout(function () {
                primeiro.focus();
            }, 60);
        }
        return modal;
    }

    function fecharModais() {
        document.querySelectorAll('.modal').forEach(function (modal) {
            modal.hidden = true;
        });
        document.body.style.overflow = '';
    }

    window.FDMModal = { abrir: abrirModal, fechar: fecharModais };

    /** Preenche o formulário/textos do modal com o JSON do botão (data-item). */
    function preencherModal(modal, item, titulo) {
        var form = modal.querySelector('form');
        if (form) {
            form.reset();
            form.querySelectorAll('.com-erro').forEach(function (campo) {
                campo.classList.remove('com-erro');
            });
            form.querySelectorAll('.campo-erro').forEach(function (aviso) {
                aviso.textContent = '';
            });
            Object.keys(item).forEach(function (chave) {
                var campo = form.elements[chave];
                if (campo && campo.type !== 'file' && campo.type !== 'checkbox' && item[chave] !== null) {
                    campo.value = item[chave];
                }
            });
        }
        modal.querySelectorAll('[data-campo]').forEach(function (alvo) {
            var valor = item[alvo.dataset.campo];
            alvo.textContent = valor === null || valor === undefined ? '-' : valor;
        });
        // Foto (se houver) ou iniciais: <img data-campo-img="foto_url"> + <span data-campo-iniciais>
        modal.querySelectorAll('[data-campo-img]').forEach(function (img) {
            var url = item[img.dataset.campoImg];
            var iniciais = modal.querySelector('[data-campo-iniciais]');
            if (url) {
                img.src = url;
                img.hidden = false;
            } else {
                img.removeAttribute('src');
                img.hidden = true;
            }
            if (iniciais) {
                iniciais.hidden = !!url;
            }
        });
        var tituloEl = modal.querySelector('.modal-titulo');
        if (tituloEl && titulo) {
            tituloEl.textContent = titulo;
        }
    }

    document.addEventListener('click', function (evento) {
        var abrir = evento.target.closest('[data-modal-open]');
        if (abrir) {
            var modal = document.getElementById(abrir.dataset.modalOpen);
            if (modal) {
                if (abrir.dataset.item) {
                    try {
                        preencherModal(modal, JSON.parse(abrir.dataset.item), abrir.dataset.titulo);
                    } catch (erro) {
                        // JSON inválido: abre o modal vazio.
                    }
                }
                abrirModal(abrir.dataset.modalOpen);
            }
            return;
        }
        if (evento.target.closest('[data-modal-close]')) {
            fecharModais();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            fecharModais();
        }
    });

    // --------------------------------------------------------- Menu lateral
    document.querySelectorAll('[data-abrir-menu]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            document.body.classList.add('menu-aberto');
        });
    });
    document.querySelectorAll('[data-fechar-menu]').forEach(function (fundo) {
        fundo.addEventListener('click', function () {
            document.body.classList.remove('menu-aberto');
        });
    });

    // ------------------------------------------------- Mostrar/ocultar senha
    document.querySelectorAll('[data-alternar-senha]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var campo = document.getElementById(botao.dataset.alternarSenha);
            if (!campo) {
                return;
            }
            var mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            botao.classList.toggle('ativo', mostrar);
        });
    });

    // ---------------------------------------- Botões com estado de carregamento
    document.addEventListener('submit', function (evento) {
        var formulario = evento.target;
        window.setTimeout(function () {
            // Se a validação cancelou o envio, não mostra o carregando.
            if (evento.defaultPrevented) {
                return;
            }
            var botao = formulario.querySelector('button[data-carregando][type=submit]');
            if (botao) {
                botao.classList.add('carregando');
            }
        }, 0);
    });

    // ----------------------------------------------------- Transição de páginas
    document.addEventListener('click', function (evento) {
        var link = evento.target.closest('a[href]');
        if (!link || evento.defaultPrevented || evento.metaKey || evento.ctrlKey || evento.shiftKey || evento.button !== 0) {
            return;
        }
        if (link.target === '_blank' || link.origin !== window.location.origin || link.hash) {
            return;
        }
        var pagina = document.querySelector('.pagina-entrada');
        if (!pagina) {
            return;
        }
        evento.preventDefault();
        pagina.classList.add('saindo-pagina');
        window.setTimeout(function () {
            window.location.href = link.href;
        }, 160);
    });

    window.addEventListener('pageshow', function () {
        document.querySelectorAll('.saindo-pagina').forEach(function (el) {
            el.classList.remove('saindo-pagina');
        });
        document.querySelectorAll('.btn.carregando').forEach(function (botao) {
            botao.classList.remove('carregando');
        });
    });
})();
