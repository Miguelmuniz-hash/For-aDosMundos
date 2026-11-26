<?php
namespace App\Services;

/**
 * Regras de negócio da forca (nenhuma regra fica no JavaScript nem nas Views).
 * O estado da partida vive na sessão PHP; a palavra secreta nunca vai para o HTML.
 */
class ForcaJogo
{
    public const MAX_ERROS = 6;
    public const PONTOS_VITORIA = 100;
    public const PONTOS_POR_ERRO_RESTANTE = 20;

    /** Cria o estado inicial de uma partida. */
    public static function novoEstado(array $palavra): array
    {
        return [
            'categoria_id' => (int) $palavra['categoria_id'],
            'palavra_id'   => (int) $palavra['id'],
            'palavra'      => $palavra['palavra'],
            'dica'         => $palavra['dica'],
            'tentadas'     => [],
            'erros'        => 0,
            'status'       => 'andamento',
        ];
    }

    /** Letras A-Z válidas? */
    public static function letraValida(string $letra): bool
    {
        return preg_match('/^[A-Z]$/', $letra) === 1;
    }

    /**
     * Processa uma letra. Retorna [sucesso, mensagemDeErro, acertou, posicoes].
     * Atualiza o estado por referência.
     */
    public static function tentarLetra(array &$estado, string $letra): array
    {
        $letra = strtoupper($letra);

        if ($estado['status'] !== 'andamento') {
            return [false, 'A partida já terminou.', false, []];
        }
        if (!self::letraValida($letra)) {
            return [false, 'Letra inválida.', false, []];
        }
        if (in_array($letra, $estado['tentadas'], true)) {
            return [false, 'Você já usou essa letra.', false, []];
        }

        $estado['tentadas'][] = $letra;
        $letras = str_split($estado['palavra']);
        $posicoes = array_keys($letras, $letra, true);
        $acertou = count($posicoes) > 0;

        if (!$acertou) {
            $estado['erros']++;
        }

        if (self::palavraCompleta($estado)) {
            $estado['status'] = 'vitoria';
        } elseif ($estado['erros'] >= self::MAX_ERROS) {
            $estado['status'] = 'derrota';
        }

        return [true, null, $acertou, $posicoes];
    }

    /** Todas as letras da palavra já foram descobertas? */
    public static function palavraCompleta(array $estado): bool
    {
        foreach (str_split($estado['palavra']) as $letra) {
            if (!in_array($letra, $estado['tentadas'], true)) {
                return false;
            }
        }
        return true;
    }

    /** Máscara segura: só mostra as letras já acertadas (as demais viram null). */
    public static function mascara(array $estado): array
    {
        $mascara = [];
        foreach (str_split($estado['palavra']) as $letra) {
            $mascara[] = in_array($letra, $estado['tentadas'], true) ? $letra : null;
        }
        return $mascara;
    }

    /** Letras acertadas e erradas (para pintar o teclado). */
    public static function classificarLetras(array $estado): array
    {
        $acertos = [];
        $erros = [];
        $letrasDaPalavra = str_split($estado['palavra']);
        foreach ($estado['tentadas'] as $letra) {
            if (in_array($letra, $letrasDaPalavra, true)) {
                $acertos[] = $letra;
            } else {
                $erros[] = $letra;
            }
        }
        return [$acertos, $erros];
    }

    /** Pontuação: vitória = 100 + 20 por erro que sobrou; derrota = 0. */
    public static function calcularPontos(string $status, int $erros): int
    {
        if ($status !== 'vitoria') {
            return 0;
        }
        $restantes = max(0, self::MAX_ERROS - $erros);
        return self::PONTOS_VITORIA + $restantes * self::PONTOS_POR_ERRO_RESTANTE;
    }

    /** Quantidade de letras distintas já reveladas / total de letras distintas (progresso). */
    public static function progresso(array $estado): array
    {
        $distintas = array_unique(str_split($estado['palavra']));
        $reveladas = 0;
        foreach ($distintas as $letra) {
            if (in_array($letra, $estado['tentadas'], true)) {
                $reveladas++;
            }
        }
        return ['reveladas' => $reveladas, 'total' => count($distintas)];
    }
}
