/**
 * Painel administrativo: modal de confirmação para ações perigosas.
 */
(function () {
    'use strict';

    var form = document.getElementById('confirmar-form');
    if (!form) {
        return;
    }

    document.addEventListener('click', function (evento) {
        var botao = evento.target.closest('[data-confirmar]');
        if (!botao) {
            return;
        }
        form.action = botao.dataset.acao;
        document.getElementById('confirmar-id').value = botao.dataset.id;
        document.getElementById('confirmar-texto').textContent = botao.dataset.texto || 'Tem certeza?';

        var confirmar = form.querySelector('button[type=submit]');
        confirmar.textContent = botao.dataset.botao || 'Excluir';
        confirmar.classList.remove('carregando');
        confirmar.classList.toggle('btn-perigo', !botao.dataset.neutro);
        confirmar.classList.toggle('btn-primario', !!botao.dataset.neutro);

        window.FDMModal.abrir('modal-confirmar');
    });

    // Atalhos do painel (?novo=1): abre direto o modal de cadastro da página.
    if (/[?&]novo=1(&|$)/.test(window.location.search)) {
        var botaoNovo = document.querySelector('[data-modal-open][data-novo]');
        if (botaoNovo) {
            botaoNovo.click();
        }
        // Limpa o parâmetro para que recarregar a página não reabra o modal.
        if (window.history && window.history.replaceState) {
            var limpa = window.location.search.replace(/([?&])novo=1(&|$)/, function (_, antes, depois) {
                return depois ? antes : '';
            });
            window.history.replaceState(null, '', window.location.pathname + limpa + window.location.hash);
        }
    }
})();
