<?php
declare(strict_types=1);

namespace App\Core;

/** Upload seguro de imagem: valida MIME real (finfo), tamanho, conteudo de imagem e gera nome aleatorio. */
final class Upload
{
    /** @return array{ok:bool, arquivo?:string, erro?:string} */
    public static function imagem(array $arquivo): array
    {
        if (!isset($arquivo['error']) || is_array($arquivo['error'])) {
            return ['ok' => false, 'erro' => 'Envio invalido.'];
        }
        if ($arquivo['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'erro' => 'Selecione uma imagem.'];
        }
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'erro' => 'Falha no upload (codigo ' . (int) $arquivo['error'] . ').'];
        }
        $max = (int) Config::get('upload.max_bytes');
        if ($arquivo['size'] > $max) {
            return ['ok' => false, 'erro' => 'A imagem excede ' . round($max / 1048576, 1) . ' MB.'];
        }
        if (!is_uploaded_file($arquivo['tmp_name'])) {
            return ['ok' => false, 'erro' => 'Origem do arquivo invalida.'];
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
        $permitidos = Config::get('upload.mimes');
        if (!isset($permitidos[$mime]) || @getimagesize($arquivo['tmp_name']) === false) {
            return ['ok' => false, 'erro' => 'Formato nao permitido. Use JPG, PNG ou WEBP.'];
        }

        $dir = (string) Config::get('upload.dir');
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['ok' => false, 'erro' => 'Nao foi possivel criar a pasta de uploads.'];
        }
        $nome = bin2hex(random_bytes(16)) . '.' . $permitidos[$mime];
        if (!move_uploaded_file($arquivo['tmp_name'], $dir . '/' . $nome)) {
            return ['ok' => false, 'erro' => 'Nao foi possivel salvar a imagem.'];
        }
        return ['ok' => true, 'arquivo' => $nome];
    }

    public static function remover(?string $nome): void
    {
        if (!$nome) {
            return;
        }
        $caminho = Config::get('upload.dir') . '/' . basename($nome);
        if (is_file($caminho)) {
            @unlink($caminho);
        }
    }
}
