<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Partida;

/**
 * Dashboard do jogador e seleção de mundos (categorias).
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $usuario = $this->exigirLogin();
        $partidas = new Partida();

        $this->render('home/index', [
            'titulo'     => 'Início',
            'pagina'     => 'inicio',
            'stats'      => $partidas->estatisticasUsuario((int) $usuario['id']),
            'ultimas'    => $partidas->ultimasDoUsuario((int) $usuario['id'], 5),
            'grafico'    => $partidas->pontosRecentes((int) $usuario['id'], 10),
            'categorias' => (new Categoria())->ativasComContagem(),
        ]);
    }

    public function mundos(): void
    {
        $this->exigirLogin();
        $this->render('home/mundos', [
            'titulo'     => 'Escolha seu mundo',
            'pagina'     => 'mundos',
            'categorias' => (new Categoria())->ativasComContagem(),
        ]);
    }
}
