<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Partida;

final class DashboardController extends Controller
{
    public function home(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        $this->redirect('login');
    }

    public function index(): void
    {
        $usuario = Auth::user();
        $usuarioId = (int) $usuario['id'];

        $stats      = Partida::estatisticasUsuario($usuarioId);
        $categorias = Categoria::ativasComContagem();
        $ultimas    = Partida::ultimasDoUsuario($usuarioId, 5);

        $this->view('dashboard/index', [
            'titulo'     => 'Início - Força dos Mundos',
            'usuario'    => $usuario,
            'stats'      => $stats,
            'categorias' => $categorias,
            'ultimas'    => $ultimas,
        ]);
    }
}