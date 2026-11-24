<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Partida;

final class RankingController extends Controller
{
    public function index(): void
    {
        $top10   = Partida::topRanking(10);
        $ultimas = Partida::ultimasDoUsuario((int) Auth::id(), 5);

        $this->view('ranking/index', [
            'titulo'  => 'Ranking Geral - Força dos Mundos',
            'top10'   => $top10,
            'ultimas' => $ultimas,
        ]);
    }
}