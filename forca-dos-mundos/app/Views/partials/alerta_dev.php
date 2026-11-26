<?php
/**
 * Aviso de desenvolvimento: aparece só com 'ambiente' => 'local' (config/database.php)
 * e apenas quando o e-mail NÃO pôde ser enviado. Requer $linkDev.
 */
if (empty($linkDev)) {
    return;
}
?>
<div class="alerta alerta-dev" role="status">
    <strong>Modo desenvolvimento</strong>
    <p>O e-mail não pôde ser enviado (confira <code>config/mail.php</code> e a internet). O link também foi gravado em <code>storage/emails.log</code>. Use este endereço:</p>
    <a class="link link-quebra" href="<?= e($linkDev) ?>"><?= e($linkDev) ?></a>
</div>
