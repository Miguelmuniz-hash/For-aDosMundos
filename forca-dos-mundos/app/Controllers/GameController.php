<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Palavra;
use App\Models\Partida;
use App\Services\ForcaJogo;

/**
 * Partida da forca. Toda decisão (acerto, erro, vitória, derrota, pontos)
 * acontece aqui no PHP; o estado fica na sessão e a palavra nunca vai para o HTML.
 */
class GameController extends Controller
{
    /** Começa uma partida na categoria escolhida. */
    public function iniciar(): void
    {
        $usuario = $this->exigirLogin();
        $categoriaId = (int) $this->post('categoria_id');

        $categoria = (new Categoria())->buscarPorId($categoriaId);
        if (!$categoria || $categoria['status'] !== 'ativo') {
            flash('erro', 'Esse mundo não está disponível.');
            $this->redirect('/mundos');
        }

        // Se havia uma partida aberta, ela termina como derrota (evita "pular" palavras difíceis).
        if (!empty($_SESSION['jogo'])) {
            $this->finalizar($usuario, $_SESSION['jogo'], true);
        }

        $evitar = (int) ($_SESSION['ultima_palavra'] ?? 0);
        $palavra = (new Palavra())->sortear($categoriaId, $evitar);
        if (!$palavra) {
            flash('erro', 'Este mundo ainda não tem palavras cadastradas.');
            $this->redirect('/mundos');
        }

        unset($_SESSION['resultado']);
        $_SESSION['jogo'] = ForcaJogo::novoEstado($palavra);
        $_SESSION['ultima_palavra'] = (int) $palavra['id'];
        $this->redirect('/jogo');
    }

    /** Tela da partida (sem revelar a palavra). */
    public function jogar(): void
    {
        $usuario = $this->exigirLogin();
        $estado = $_SESSION['jogo'] ?? null;
        if (!$estado) {
            flash('info', 'Escolha um mundo para começar uma partida.');
            $this->redirect('/mundos');
        }

        [$acertos, $erradas] = ForcaJogo::classificarLetras($estado);
        $categoria = (new Categoria())->buscarPorId($estado['categoria_id']);

        $this->render('game/jogo', [
            'titulo'    => 'Partida',
            'pagina'    => 'mundos',
            'categoria' => $categoria,
            'dica'      => $estado['dica'],
            'mascara'   => ForcaJogo::mascara($estado),
            'erros'     => $estado['erros'],
            'maxErros'  => ForcaJogo::MAX_ERROS,
            'acertos'   => $acertos,
            'erradas'   => $erradas,
            'progresso' => ForcaJogo::progresso($estado),
            'usuario'   => $usuario,
        ], 'game');
    }

    /** Recebe uma letra (via fetch/JSON ou formulário comum). */
    public function letra(): void
    {
        $usuario = $this->exigirLogin();
        $estado = $_SESSION['jogo'] ?? null;
        $json = self::querJson();

        if (!$estado) {
            if ($json) {
                $this->json(['ok' => false, 'mensagem' => 'Nenhuma partida em andamento.', 'redirect' => url('/mundos')], 409);
            }
            $this->redirect('/mundos');
        }

        [$sucesso, $mensagem, $acertou, $posicoes] = ForcaJogo::tentarLetra($estado, $this->post('letra'));
        $_SESSION['jogo'] = $estado;

        if (!$sucesso) {
            if ($json) {
                $this->json(['ok' => false, 'mensagem' => $mensagem], 422);
            }
            flash('erro', $mensagem);
            $this->redirect('/jogo');
        }

        $terminou = $estado['status'] !== 'andamento';
        if ($terminou) {
            $this->finalizar($usuario, $estado, false);
        }

        if ($json) {
            $this->json([
                'ok'        => true,
                'acertou'   => $acertou,
                'posicoes'  => $posicoes,
                'erros'     => $estado['erros'],
                'restantes' => ForcaJogo::MAX_ERROS - $estado['erros'],
                'status'    => $estado['status'],
                'progresso' => ForcaJogo::progresso($estado),
                'redirect'  => $terminou ? url('/jogo/resultado') : null,
            ]);
        }
        $this->redirect($terminou ? '/jogo/resultado' : '/jogo');
    }

    /** Abandonar conta como derrota (0 pontos). */
    public function abandonar(): void
    {
        $usuario = $this->exigirLogin();
        if (!empty($_SESSION['jogo'])) {
            $this->finalizar($usuario, $_SESSION['jogo'], true);
            $this->redirect('/jogo/resultado');
        }
        $this->redirect('/mundos');
    }

    /** Resultado da última partida (lido da sessão: recarregar não grava de novo). */
    public function resultado(): void
    {
        $this->exigirLogin();
        $resultado = $_SESSION['resultado'] ?? null;
        if (!$resultado) {
            $this->redirect('/inicio');
        }
        $this->render('game/resultado', [
            'titulo'    => $resultado['vitoria'] ? 'Vitória!' : 'Fim de jogo',
            'pagina'    => 'mundos',
            'resultado' => $resultado,
            'maxErros'  => ForcaJogo::MAX_ERROS,
        ], 'game');
    }

    /**
     * Encerra a partida: calcula pontos (somente aqui, no PHP), grava UMA vez
     * e remove o estado da sessão para impedir gravação dupla.
     */
    private function finalizar(array $usuario, array $estado, bool $abandono): void
    {
        $vitoria = !$abandono && $estado['status'] === 'vitoria';
        $status = $vitoria ? 'vitoria' : 'derrota';
        $erros = min((int) $estado['erros'], ForcaJogo::MAX_ERROS);
        $pontos = ForcaJogo::calcularPontos($status, $erros);

        // Remove o estado ANTES de gravar: se o usuário recarregar, nada é regravado.
        unset($_SESSION['jogo']);

        (new Partida())->registrar(
            (int) $usuario['id'],
            (int) $estado['categoria_id'],
            (int) $estado['palavra_id'],
            $estado['palavra'],
            $erros,
            $pontos,
            $status
        );

        $categoria = (new Categoria())->buscarPorId((int) $estado['categoria_id']);
        $_SESSION['resultado'] = [
            'vitoria'      => $vitoria,
            'abandono'     => $abandono,
            'palavra'      => $estado['palavra'],
            'erros'        => $erros,
            'pontos'       => $pontos,
            'categoria_id' => (int) $estado['categoria_id'],
            'categoria'    => $categoria['nome'] ?? '',
        ];
    }
}
