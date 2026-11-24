<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Categoria;
use App\Models\Palavra;
use App\Models\Partida;

final class GameController extends Controller
{
    private const MAX_ERROS = 6;

    /** GET /jogo/{categoriaId} -> sorteia palavra e abre a partida */
    public function iniciar(int|string $categoriaId): void
    {
        $catId = (int) $categoriaId;
        $categoria = Categoria::find($catId);

        if (!$categoria || $categoria['status'] !== 'ativa') {
            $this->erro('Categoria inválida ou inativa.');
            $this->redirect('dashboard');
        }

        $palavraObj = Palavra::sortearPorCategoria($catId);
        if (!$palavraObj) {
            $this->erro('Esta categoria ainda não possui palavras cadastradas.');
            $this->redirect('dashboard');
        }

        $palavra = mb_strtoupper(trim($palavraObj['palavra']));

        Session::set('partida_atual', [
            'categoria_id'   => $catId,
            'categoria_nome' => $categoria['nome'],
            'palavra_id'     => (int) $palavraObj['id'],
            'palavra'        => $palavra,
            'dica'           => $palavraObj['dica'],
            'letras_certas'  => [],
            'letras_erradas' => [],
            'erros'          => 0,
            'usou_revelar'   => false,
            'finalizada'     => false,
            'partida_id'     => null,
        ]);

        $this->redirect('jogo');
    }

    /** GET /jogo -> exibe a partida em andamento */
    public function index(): void
    {
        $jogo = Session::get('partida_atual');
        if (!$jogo) {
            $this->erro('Nenhuma partida em andamento. Escolha um mundo para jogar.');
            $this->redirect('dashboard');
        }

        if (!empty($jogo['finalizada']) && !empty($jogo['partida_id'])) {
            $this->redirect('jogo/resultado/' . $jogo['partida_id']);
        }

        $this->view('game/index', [
            'titulo' => 'Jogando: ' . $jogo['categoria_nome'] . ' - Força dos Mundos',
            'estado' => $this->montarEstadoPublico($jogo),
        ]);
    }

    /** POST /jogo/letra -> recebe letra via JSON, valida no PHP e retorna novo estado */
    public function letra(): void
    {
        Csrf::verifyOrFail();

        $jogo = Session::get('partida_atual');
        if (!$jogo || !empty($jogo['finalizada'])) {
            $this->json(['erro' => 'Nenhuma partida ativa encontrada.'], 400);
        }

        $input = json_decode((string) file_get_contents('php://input'), true);
        $letra = mb_strtoupper(trim((string) ($input['letra'] ?? $_POST['letra'] ?? '')));

        if (!preg_match('/^[A-Z]$/', $letra)) {
            $this->json(['erro' => 'Letra inválida.'], 422);
        }

        if (in_array($letra, $jogo['letras_certas'], true) || in_array($letra, $jogo['letras_erradas'], true)) {
            $this->json(['erro' => 'Letra já utilizada.'], 422);
        }

        $letrasPalavra = str_split($jogo['palavra']);

        if (in_array($letra, $letrasPalavra, true)) {
            $jogo['letras_certas'][] = $letra;
        } else {
            $jogo['letras_erradas'][] = $letra;
            $jogo['erros']++;
        }

        $jogo = $this->verificarEncerramento($jogo);
        Session::set('partida_atual', $jogo);

        $this->json($this->montarEstadoPublico($jogo));
    }

    /** POST /jogo/revelar -> revela 1 letra aleatória ainda oculta (-30 pts) */
    public function revelar(): void
    {
        Csrf::verifyOrFail();

        $jogo = Session::get('partida_atual');
        if (!$jogo || !empty($jogo['finalizada'])) {
            $this->json(['erro' => 'Nenhuma partida ativa encontrada.'], 400);
        }

        if (!empty($jogo['usou_revelar'])) {
            $this->json(['erro' => 'Você já utilizou a ajuda nesta partida.'], 422);
        }

        $letrasPalavra = array_unique(str_split($jogo['palavra']));
        $ocultas = array_values(array_diff($letrasPalavra, $jogo['letras_certas']));

        if (empty($ocultas)) {
            $this->json(['erro' => 'Todas as letras já foram reveladas.'], 422);
        }

        $sorteada = $ocultas[array_rand($ocultas)];
        $jogo['letras_certas'][] = $sorteada;
        $jogo['usou_revelar'] = true;

        $jogo = $this->verificarEncerramento($jogo);
        Session::set('partida_atual', $jogo);

        $this->json($this->montarEstadoPublico($jogo));
    }

    /** POST /jogo/abandonar -> encerra a partida na sessão sem salvar vitória */
    public function abandonar(): void
    {
        Csrf::verifyOrFail();
        Session::forget('partida_atual');
        $this->sucesso('Partida abandonada.');
        $this->redirect('dashboard');
    }

    /** GET /jogo/resultado/{id} -> tela final de vitória ou derrota */
    public function resultado(int|string $id): void
    {
        $partidaId = (int) $id;
        $partida = Partida::find($partidaId);

        if (!$partida || (int) $partida['usuario_id'] !== Auth::id()) {
            $this->erro('Resultado de partida não encontrado.');
            $this->redirect('dashboard');
        }

        $categoria = $partida['categoria_id'] ? Categoria::find((int) $partida['categoria_id']) : null;

        $this->view('game/resultado', [
            'titulo'    => ($partida['resultado'] === 'vitoria' ? 'Vitória!' : 'Fim de Jogo') . ' - Força dos Mundos',
            'partida'   => $partida,
            'categoria' => $categoria,
        ]);
    }

    private function verificarEncerramento(array $jogo): array
    {
        $letrasUnicas = array_unique(str_split($jogo['palavra']));
        $acertouTudo  = count(array_diff($letrasUnicas, $jogo['letras_certas'])) === 0;
        $perdeu       = $jogo['erros'] >= self::MAX_ERROS;

        if (!$acertouTudo && !$perdeu) {
            return $jogo;
        }

        // Grava apenas uma única vez
        if (empty($jogo['finalizada'])) {
            $jogo['finalizada'] = true;

            if ($acertouTudo) {
                $errosRestantes = max(0, self::MAX_ERROS - $jogo['erros']);
                $pontos = 100 + ($errosRestantes * 20);
                if (!empty($jogo['usou_revelar'])) {
                    $pontos = max(10, $pontos - 30);
                }
                $resultado = 'vitoria';
            } else {
                $pontos = 0;
                $resultado = 'derrota';
            }

            $partidaId = Partida::registrar(
                (int) Auth::id(),
                (int) $jogo['categoria_id'],
                (int) $jogo['palavra_id'],
                $jogo['palavra'],
                (int) $jogo['erros'],
                $pontos,
                $resultado,
                !empty($jogo['usou_revelar'])
            );

            $jogo['partida_id'] = $partidaId;
        }

        return $jogo;
    }

    /** Monta payload seguro (NUNCA envia a palavra completa enquanto finalizada == false) */
    private function montarEstadoPublico(array $jogo): array
    {
        $mascara = [];
        foreach (str_split($jogo['palavra']) as $char) {
            $mascara[] = in_array($char, $jogo['letras_certas'], true) ? $char : null;
        }

        $errosRestantes = max(0, self::MAX_ERROS - $jogo['erros']);
        $pontosPrevistos = 100 + ($errosRestantes * 20) - (!empty($jogo['usou_revelar']) ? 30 : 0);

        return [
            'categoria_id'     => $jogo['categoria_id'],
            'categoria_nome'   => $jogo['categoria_nome'],
            'dica'             => $jogo['dica'],
            'mascara'          => $mascara,
            'letras_certas'    => $jogo['letras_certas'],
            'letras_erradas'   => $jogo['letras_erradas'],
            'erros'            => $jogo['erros'],
            'erros_restantes'  => $errosRestantes,
            'usou_revelar'     => !empty($jogo['usou_revelar']),
            'pontos_previstos' => max(0, $pontosPrevistos),
            'finalizada'       => !empty($jogo['finalizada']),
            'resultado_url'    => !empty($jogo['partida_id']) ? url('jogo/resultado/' . $jogo['partida_id']) : null,
        ];
    }
}