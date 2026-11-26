/**
 * Pré-visualização da foto de perfil antes de salvar.
 */
(function () {
    'use strict';

    var entrada = document.getElementById('foto');
    if (!entrada) {
        return;
    }

    var imagem = document.getElementById('foto-previa-img');
    var iniciais = document.getElementById('foto-previa-iniciais');
    var botaoSalvar = document.getElementById('foto-salvar');
    var aviso = document.getElementById('foto-erro');
    var tiposValidos = ['image/jpeg', 'image/png', 'image/webp'];
    var limite = 2 * 1024 * 1024;

    entrada.addEventListener('change', function () {
        var arquivo = entrada.files[0];
        aviso.textContent = '';
        botaoSalvar.disabled = true;

        if (!arquivo) {
            return;
        }
        if (tiposValidos.indexOf(arquivo.type) === -1) {
            aviso.textContent = 'Formato inválido. Use JPG, PNG ou WEBP.';
            entrada.value = '';
            return;
        }
        if (arquivo.size > limite) {
            aviso.textContent = 'A imagem deve ter no máximo 2 MB.';
            entrada.value = '';
            return;
        }

        imagem.src = URL.createObjectURL(arquivo);
        imagem.hidden = false;
        if (iniciais) {
            iniciais.hidden = true;
        }
        botaoSalvar.disabled = false;
    });
})();
