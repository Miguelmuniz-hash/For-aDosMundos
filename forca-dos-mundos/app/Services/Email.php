<?php
namespace App\Services;

use App\Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once BASE_PATH . '/app/Libraries/PHPMailer/Exception.php';
require_once BASE_PATH . '/app/Libraries/PHPMailer/PHPMailer.php';
require_once BASE_PATH . '/app/Libraries/PHPMailer/SMTP.php';

/**
 * Envio de e-mails por SMTP (PHPMailer). Configuração em config/mail.php.
 */
class Email
{
    public static function enviar(string $para, string $nome, string $assunto, string $html, string $texto): bool
    {
        $cfg = require BASE_PATH . '/config/mail.php';
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host        = $cfg['host'];
            $mail->SMTPAuth    = true;
            $mail->Username    = $cfg['usuario'];
            $mail->Password    = $cfg['senha_app'];
            $mail->SMTPSecure  = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port        = (int) $cfg['porta'];
            $mail->CharSet     = 'UTF-8';
            // Sem isso, um SMTP fora do ar deixaria a página travada por até 5 minutos.
            $mail->Timeout     = 12;
            $mail->SMTPKeepAlive = false;

            // O Gmail só aceita remetente igual à conta autenticada (ou um alias dela).
            // Se "remetente" ficou com o valor de exemplo, usamos a própria conta.
            $remetente = trim((string) ($cfg['remetente'] ?? ''));
            if ($remetente === '' || stripos($remetente, 'seuemail') !== false || !filter_var($remetente, FILTER_VALIDATE_EMAIL)) {
                $remetente = $cfg['usuario'];
            }

            $mail->setFrom($remetente, $cfg['nome']);
            $mail->addAddress($para, $nome);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $html;
            $mail->AltBody = $texto;

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            $linha = '[' . date('Y-m-d H:i:s') . '] ERRO ao enviar para ' . $para . ': '
                . ($mail->ErrorInfo !== '' ? $mail->ErrorInfo : $e->getMessage()) . PHP_EOL;

            // Em desenvolvimento, guarda também o texto da mensagem: o link continua acessível.
            if (Database::config('ambiente') === 'local') {
                $linha .= '    Conteúdo da mensagem:' . PHP_EOL . '    '
                    . str_replace("\n", PHP_EOL . '    ', $texto) . PHP_EOL;
            }

            @file_put_contents(BASE_PATH . '/storage/emails.log', $linha, FILE_APPEND | LOCK_EX);
            return false;
        }
    }
}
