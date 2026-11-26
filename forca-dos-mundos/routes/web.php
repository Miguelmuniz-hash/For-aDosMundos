<?php
/**
 * Mapa de rotas: método + caminho => Controller@ação.
 * A autorização (login/admin) é conferida dentro de cada ação.
 */

// Autenticação
$router->get('/', 'AuthController@inicio');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@autenticar');
$router->get('/cadastro', 'AuthController@cadastro');
$router->post('/cadastro', 'AuthController@registrar');
$router->post('/sair', 'AuthController@sair');
$router->get('/esqueci-senha', 'AuthController@esqueci');
$router->post('/esqueci-senha', 'AuthController@enviarRecuperacao');
$router->get('/redefinir-senha', 'AuthController@redefinir');
$router->post('/redefinir-senha', 'AuthController@salvarRedefinicao');
$router->get('/confirmar-email', 'AuthController@confirmarEmail');
$router->get('/reenviar-confirmacao', 'AuthController@reenviar');
$router->post('/reenviar-confirmacao', 'AuthController@enviarReenvio');

// Área do jogador
$router->get('/inicio', 'HomeController@index');
$router->get('/mundos', 'HomeController@mundos');

// Jogo
$router->post('/jogo/iniciar', 'GameController@iniciar');
$router->get('/jogo', 'GameController@jogar');
$router->post('/jogo/letra', 'GameController@letra');
$router->post('/jogo/abandonar', 'GameController@abandonar');
$router->get('/jogo/resultado', 'GameController@resultado');

// Ranking
$router->get('/ranking', 'RankingController@index');

// Perfil e configurações
$router->get('/perfil', 'ProfileController@index');
$router->post('/perfil/dados', 'ProfileController@salvarDados');
$router->post('/perfil/foto', 'ProfileController@salvarFoto');
$router->post('/perfil/foto/remover', 'ProfileController@removerFoto');
$router->get('/perfil/senha', 'ProfileController@senha');
$router->post('/perfil/senha', 'ProfileController@salvarSenha');
$router->post('/perfil/excluir', 'ProfileController@excluir');
$router->get('/configuracoes', 'ProfileController@configuracoes');

// Administração
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/categorias', 'AdminController@categorias');
$router->post('/admin/categorias/salvar', 'AdminController@salvarCategoria');
$router->post('/admin/categorias/status', 'AdminController@statusCategoria');
$router->post('/admin/categorias/excluir', 'AdminController@excluirCategoria');
$router->get('/admin/palavras', 'AdminController@palavras');
$router->post('/admin/palavras/salvar', 'AdminController@salvarPalavra');
$router->post('/admin/palavras/excluir', 'AdminController@excluirPalavra');
$router->get('/admin/usuarios', 'AdminController@usuarios');
$router->post('/admin/usuarios/salvar', 'AdminController@salvarUsuario');
$router->post('/admin/usuarios/status', 'AdminController@statusUsuario');
$router->post('/admin/usuarios/verificar', 'AdminController@verificarUsuario');
$router->post('/admin/usuarios/excluir', 'AdminController@excluirUsuario');
$router->get('/admin/partidas', 'AdminController@partidas');
