/**
 * Validação de formulários no navegador (conforto para o usuário).
 * O servidor SEMPRE valida de novo: esta validação não substitui o PHP.
 */
(function () {
    'use strict';

    function mensagemDoCampo(campo) {
        var valor = campo.value.trim();

        if (campo.hasAttribute('required') && valor === '' && campo.type !== 'file') {
            return 'Este campo é obrigatório.';
        }
        if (valor === '') {
            return '';
        }
        if (campo.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
            return 'Informe um e-mail válido.';
        }
        var minimo = parseInt(campo.getAttribute('minlength') || '0', 10);
        if (minimo && campo.value.length < minimo) {
            return 'Use pelo menos ' + minimo + ' caracteres.';
        }
        var padrao = campo.getAttribute('pattern');
        if (padrao && !new RegExp('^(?:' + padrao + ')$').test(campo.value)) {
            return 'Use apenas letras de A a Z, sem acentos ou espaços.';
        }
        if (campo.dataset.igualA) {
            var outro = document.getElementById(campo.dataset.igualA);
            if (outro && outro.value !== campo.value) {
                return 'As senhas não conferem.';
            }
        }
        return '';
    }

    function mostrarErro(campo, mensagem) {
        var caixa = campo.closest('.campo');
        if (!caixa) {
            return;
        }
        var aviso = caixa.querySelector('.campo-erro');
        caixa.classList.toggle('com-erro', mensagem !== '');
        if (aviso) {
            aviso.textContent = mensagem;
        }
    }

    document.querySelectorAll('form[data-validar]').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            var invalido = false;
            formulario.querySelectorAll('input, select, textarea').forEach(function (campo) {
                if (campo.type === 'hidden' || campo.type === 'checkbox') {
                    return;
                }
                var mensagem = mensagemDoCampo(campo);
                mostrarErro(campo, mensagem);
                if (mensagem !== '') {
                    invalido = true;
                }
            });
            if (invalido) {
                evento.preventDefault();
                var primeiro = formulario.querySelector('.com-erro input, .com-erro select');
                if (primeiro) {
                    primeiro.focus();
                }
            }
        });

        formulario.addEventListener('input', function (evento) {
            var campo = evento.target;
            if (campo.closest('.com-erro')) {
                mostrarErro(campo, mensagemDoCampo(campo));
            }
        });
    });

    // Campos de palavra: só letras A-Z, sempre em maiúsculas.
    document.querySelectorAll('[data-so-letras]').forEach(function (campo) {
        campo.addEventListener('input', function () {
            campo.value = campo.value.toUpperCase().replace(/[^A-Z]/g, '');
        });
    });
})();
