<?php
$mensagens = [
    403 => ['Acesso negado', 'Esta área é exclusiva para administradores.'],
    404 => ['Mundo não encontrado', 'A página que você procura se perdeu entre as estrelas.'],
];
[$tituloErro, $textoErro] = $mensagens[$codigo] ?? ['Algo deu errado', 'Tente novamente em instantes.'];
?>
<header class="auth-cabecalho erro-cabecalho">
    <span class="erro-codigo"><?= (int) $codigo ?></span>
    <h1><?= e($tituloErro) ?></h1>
    <p><?= e($textoErro) ?></p>
</header>
<a href="<?= e(url('/inicio')) ?>" class="btn btn-primario btn-bloco">Voltar ao início</a>
