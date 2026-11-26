<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Partida;

/**
 * Ranking geral (Top 10) e últimas partidas de quem está logado.
 */
class RankingController extends Controller
{
    public function index(): void
    {
        $usuario = $this->exigirLogin();
        $partidas = new Partida();

        $this->render('ranking/index', [
            'titulo'  => 'Ranking',
            'pagina'  => 'ranking',
            'top'     => $partidas->top10(),
            'ultimas' => $partidas->ultimasDoUsuario((int) $usuario['id'], 5),
        ]);
    }
}
