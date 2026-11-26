<?php
namespace App\Core;

/**
 * Upload seguro de imagens: valida MIME real, tamanho e conteúdo,
 * e grava com nome aleatório (nunca confia no nome enviado).
 */
class Upload
{
    private const TIPOS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    private const TAMANHO_MAXIMO = 2097152; // 2 MB

    /**
     * Salva a imagem de $_FILES[$campo] em public/uploads/$pasta.
     * Retorna [nomeDoArquivo|null, erro|null].
     */
    public static function imagem(string $campo, string $pasta): array
    {
        $arquivo = $_FILES[$campo] ?? null;

        if (!$arquivo || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
            return [null, 'Selecione uma imagem.'];
        }
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            return [null, 'Falha no envio do arquivo. Tente novamente.'];
        }
        if ($arquivo['size'] > self::TAMANHO_MAXIMO) {
            return [null, 'A imagem deve ter no máximo 2 MB.'];
        }
        if (!is_uploaded_file($arquivo['tmp_name'])) {
            return [null, 'Arquivo inválido.'];
        }

        // MIME real, lido pelo conteúdo do arquivo (não pela extensão).
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($arquivo['tmp_name']);
        if (!isset(self::TIPOS[$mime])) {
            return [null, 'Formato inválido. Use JPG, JPEG, PNG ou WEBP.'];
        }
        if (@getimagesize($arquivo['tmp_name']) === false) {
            return [null, 'O arquivo não é uma imagem válida.'];
        }

        $diretorio = BASE_PATH . '/public/uploads/' . $pasta;
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $nome = bin2hex(random_bytes(16)) . '.' . self::TIPOS[$mime];
        if (!move_uploaded_file($arquivo['tmp_name'], $diretorio . '/' . $nome)) {
            return [null, 'Não foi possível salvar a imagem.'];
        }
        return [$nome, null];
    }

    /** Remove um arquivo antigo (com proteção contra caminhos maliciosos). */
    public static function remover(?string $nome, string $pasta): void
    {
        if (!$nome || $nome !== basename($nome)) {
            return;
        }
        $caminho = BASE_PATH . '/public/uploads/' . $pasta . '/' . $nome;
        if (is_file($caminho)) {
            @unlink($caminho);
        }
    }
}
