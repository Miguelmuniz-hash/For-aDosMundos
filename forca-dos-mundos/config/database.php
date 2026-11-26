<?php
/**
 * Configuração da conexão PDO e do ambiente.
 * Altere usuário/senha conforme o seu MySQL do XAMPP.
 * (No XAMPP padrão: usuário "root" e senha vazia.)
 */
return [
    'host'    => '127.0.0.1',
    'porta'   => 3306,
    'banco'   => 'forca_dos_mundos',
    'usuario' => 'root',
    'senha'   => '',
    'charset' => 'utf8mb4',

    // "local" mostra o link de recuperação de senha na tela (somente desenvolvimento).
    // Em produção use "producao": o link só é enviado por e-mail (mail()).
    'ambiente' => 'local',
];
