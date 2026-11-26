<?php /** Notificações (toasts). As mensagens vão em JSON e o JavaScript as exibe. */ ?>
<div class="toasts" id="toasts" aria-live="polite" data-mensagens="<?= e(json_encode($flashes ?? [], JSON_UNESCAPED_UNICODE)) ?>"></div>
