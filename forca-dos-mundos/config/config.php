<?php
/**
 * Configuracao central do sistema Forca dos Mundos.
 * Ajuste apenas as credenciais do banco se o seu XAMPP for diferente do padrao.
 */
return [
    'app' => [
        'nome'     => 'Forca dos Mundos',
        'debug'    => true,                 // false em producao (oculta detalhes de erro)
        'timezone' => 'America/Sao_Paulo',
    ],
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'forca_dos_mundos',
        'user'    => 'root',
        'pass'    => '',                    // XAMPP padrao: senha vazia
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name'     => 'FDM_SESSID',
        'lifetime' => 7200,                 // 2 horas
    ],
    'game' => [
        'max_erros'               => 6,
        'pontos_vitoria'          => 100,
        'bonus_por_erro_restante' => 20,
        'custo_revelar'           => 30,
    ],
    'upload' => [
        'dir'       => PUBLIC_PATH . '/uploads/perfis',
        'max_bytes' => 2 * 1024 * 1024,     // 2 MB
        'mimes'     => [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ],
    ],
    'reset' => [
        'ttl_minutos' => 30,
        // true = o link de redefinicao e exibido na tela (teste local no XAMPP, sem e-mail).
        'modo_local'  => true,
    ],
];
