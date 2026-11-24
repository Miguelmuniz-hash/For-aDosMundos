<?php
declare(strict_types=1);

/** @var \App\Core\Router $router */

// ---------------------------------------------------------------- Publico
$router->get('/', 'DashboardController@home');

// ---------------------------------------------------------------- Visitantes (sem login)
$router->group(['guest'], function ($r) {
    $r->get('/login',                    'AuthController@loginForm');
    $r->post('/login',                   'AuthController@login');
    $r->get('/cadastro',                 'AuthController@registerForm');
    $r->post('/cadastro',                'AuthController@register');
    $r->get('/esqueci-senha',            'AuthController@forgotForm');
    $r->post('/esqueci-senha',           'AuthController@forgot');
    $r->get('/redefinir-senha/{token}',  'AuthController@resetForm');
    $r->post('/redefinir-senha',         'AuthController@reset');
});

// ---------------------------------------------------------------- Jogador autenticado
$router->group(['auth'], function ($r) {
    $r->post('/logout',                  'AuthController@logout');

    $r->get('/dashboard',                'DashboardController@index');
    $r->get('/ranking',                  'RankingController@index');

    // Jogo (a palavra secreta nunca sai do servidor ate o fim da partida)
    $r->get('/jogo/{categoriaId}',       'GameController@iniciar');     // sorteia palavra e abre a partida
    $r->get('/jogo',                     'GameController@index');       // partida em andamento
    $r->post('/jogo/letra',              'GameController@letra');       // JSON
    $r->post('/jogo/revelar',            'GameController@revelar');     // JSON
    $r->get('/jogo/resultado/{id}',      'GameController@resultado');
    $r->post('/jogo/abandonar',          'GameController@abandonar');

    // Perfil
    $r->get('/perfil',                   'ProfileController@index');
    $r->post('/perfil',                  'ProfileController@update');
    $r->post('/perfil/senha',            'ProfileController@password');
    $r->post('/perfil/foto',             'ProfileController@photo');
    $r->post('/perfil/foto/remover',     'ProfileController@removePhoto');
    $r->post('/perfil/excluir',          'ProfileController@destroy');
});

// ---------------------------------------------------------------- Administracao (bloqueada no back-end)
$router->group(['admin'], function ($r) {
    $r->get('/admin',                              'Admin\DashboardController@index');

    $r->get('/admin/categorias',                   'Admin\CategoryController@index');
    $r->get('/admin/categorias/criar',             'Admin\CategoryController@create');
    $r->post('/admin/categorias',                  'Admin\CategoryController@store');
    $r->get('/admin/categorias/{id}/editar',       'Admin\CategoryController@edit');
    $r->post('/admin/categorias/{id}',             'Admin\CategoryController@update');
    $r->post('/admin/categorias/{id}/status',      'Admin\CategoryController@toggle');
    $r->post('/admin/categorias/{id}/excluir',     'Admin\CategoryController@destroy');

    $r->get('/admin/palavras',                     'Admin\WordController@index');
    $r->get('/admin/palavras/criar',               'Admin\WordController@create');
    $r->post('/admin/palavras',                    'Admin\WordController@store');
    $r->get('/admin/palavras/{id}/editar',         'Admin\WordController@edit');
    $r->post('/admin/palavras/{id}',               'Admin\WordController@update');
    $r->post('/admin/palavras/{id}/excluir',       'Admin\WordController@destroy');

    $r->get('/admin/usuarios',                     'Admin\UserController@index');
    $r->post('/admin/usuarios/{id}/status',        'Admin\UserController@toggleStatus');
    $r->post('/admin/usuarios/{id}/perfil',        'Admin\UserController@toggleRole');

    $r->get('/admin/partidas',                     'Admin\MatchController@index');
});
