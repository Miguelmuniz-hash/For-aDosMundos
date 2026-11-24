<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validador simples.
 * Regras (separadas por |): required, min:N, max:N, email, same:campo, in:a,b,c, regex:/padrao/ (sem usar "|").
 */
final class Validator
{
    private array $erros = [];

    private function __construct(private array $dados) {}

    public static function make(array $dados, array $regras, array $rotulos = []): self
    {
        $v = new self($dados);
        foreach ($regras as $campo => $lista) {
            $rotulo = $rotulos[$campo] ?? ucfirst($campo);
            $valor  = $dados[$campo] ?? '';
            $valor  = is_string($valor) ? trim($valor) : '';
            foreach (explode('|', $lista) as $regra) {
                [$nome, $arg] = array_pad(explode(':', $regra, 2), 2, null);
                if ($nome !== 'required' && $valor === '') {
                    continue;   // regras opcionais so valem se houver valor
                }
                $msg = match ($nome) {
                    'required' => $valor === '' ? "$rotulo e obrigatorio." : null,
                    'min'      => mb_strlen($valor) < (int) $arg ? "$rotulo deve ter pelo menos $arg caracteres." : null,
                    'max'      => mb_strlen($valor) > (int) $arg ? "$rotulo deve ter no maximo $arg caracteres." : null,
                    'email'    => !filter_var($valor, FILTER_VALIDATE_EMAIL) ? "$rotulo invalido." : null,
                    'same'     => $valor !== (string) ($dados[$arg] ?? '') ? "$rotulo nao confere." : null,
                    'in'       => !in_array($valor, explode(',', (string) $arg), true) ? "$rotulo invalido." : null,
                    'regex'    => !preg_match((string) $arg, $valor) ? "$rotulo possui formato invalido." : null,
                    default    => throw new \InvalidArgumentException("Regra desconhecida: $nome"),
                };
                if ($msg !== null) {
                    $v->erros[$campo] = $msg;
                    break;
                }
            }
        }
        return $v;
    }

    public function fails(): bool
    {
        return $this->erros !== [];
    }

    public function errors(): array
    {
        return $this->erros;
    }

    public function addError(string $campo, string $mensagem): void
    {
        $this->erros[$campo] = $mensagem;
    }
}
