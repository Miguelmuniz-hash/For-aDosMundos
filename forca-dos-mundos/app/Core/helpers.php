<?php
/**
 * Funções utilitárias globais, usadas por Controllers e Views.
 */

/** Escapa texto para exibição segura (proteção contra XSS). */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Gera uma URL interna. Ex.: url('/login') */
function url(string $caminho = ''): string
{
    return BASE_URL . '/' . ltrim($caminho, '/');
}

/** URL de um arquivo em public/assets. */
function asset(string $caminho): string
{
    return BASE_URL . '/assets/' . ltrim($caminho, '/');
}

/** URL de um arquivo enviado (public/uploads). */
function upload_url(?string $arquivo, string $pasta): ?string
{
    if (!$arquivo) {
        return null;
    }
    return BASE_URL . '/uploads/' . $pasta . '/' . rawurlencode($arquivo);
}

/** Token CSRF da sessão atual. */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Campo hidden com o token CSRF. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Guarda uma mensagem para a próxima página (toast). */
function flash(string $tipo, string $mensagem): void
{
    $_SESSION['_flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/** Recupera (e apaga) as mensagens guardadas. */
function pull_flash(): array
{
    $mensagens = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $mensagens;
}

/** Guarda erros de formulário e os valores digitados (nunca senhas). */
function flash_form(array $erros, array $valores = []): void
{
    $_SESSION['_erros'] = $erros;
    unset(
        $valores['senha'],
        $valores['senha_confirmacao'],
        $valores['senha_atual'],
        $valores['nova_senha'],
        $valores['nova_senha_confirmacao'],
        $valores['_csrf']
    );
    $_SESSION['_old'] = $valores;
}

/** Erros de formulário da última requisição. */
function form_erros(): array
{
    static $erros = null;
    if ($erros === null) {
        $erros = $_SESSION['_erros'] ?? [];
        unset($_SESSION['_erros']);
    }
    return $erros;
}

/** Valor digitado anteriormente (para repopular formulários), já escapado. */
function old(string $campo, $padrao = ''): string
{
    static $old = null;
    if ($old === null) {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
    }
    return e($old[$campo] ?? $padrao);
}

/** Formata data/hora do MySQL para dd/mm/aaaa hh:mm. */
function formatar_data(?string $data, bool $comHora = true): string
{
    if (!$data) {
        return '-';
    }
    return date($comHora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($data));
}

/** URL da foto do usuário ou null (a view mostra as iniciais). */
function foto_url(?array $usuario): ?string
{
    if (!$usuario || empty($usuario['foto'])) {
        return null;
    }
    return upload_url($usuario['foto'], 'perfis');
}

/** Iniciais do apelido para o avatar padrão. */
function iniciais(?string $apelido): string
{
    $apelido = trim((string) $apelido);
    return mb_strtoupper(mb_substr($apelido !== '' ? $apelido : '?', 0, 2));
}

/** Calcula os dados de paginação. */
function paginar(int $total, int $pagina, int $porPagina = 10): array
{
    $totalPaginas = max(1, (int) ceil($total / $porPagina));
    $pagina = min(max(1, $pagina), $totalPaginas);
    return [
        'total'         => $total,
        'pagina'        => $pagina,
        'por_pagina'    => $porPagina,
        'total_paginas' => $totalPaginas,
        'offset'        => ($pagina - 1) * $porPagina,
    ];
}

/** Monta uma query string preservando os filtros atuais. */
function query_com(array $novos): string
{
    $atual = $_GET;
    foreach ($novos as $chave => $valor) {
        $atual[$chave] = $valor;
    }
    return '?' . http_build_query($atual);
}

/** Ícones SVG temáticos das categorias. */
function icone_svg(string $chave): string
{
    $icones = [
        'planeta' => '<circle cx="32" cy="32" r="14"/><ellipse cx="32" cy="34" rx="28" ry="8" transform="rotate(-18 32 34)"/>',
        'estrela' => '<path d="M32 6l7.5 17 18.5 1.8-14 12.3 4.2 18.1L32 45.7 15.8 55.2 20 37.1 6 24.8 24.5 23z"/>',
        'lua'     => '<path d="M44 8a24 24 0 1 0 12 34A20 20 0 0 1 44 8z"/><circle cx="48" cy="48" r="1.5"/>',
        'foguete' => '<path d="M32 6c10 8 14 20 10 34H22C18 26 22 14 32 6z"/><circle cx="32" cy="24" r="4"/><path d="M22 40l-8 10 10-2M42 40l8 10-10-2M28 46l4 12 4-12"/>',
        'castelo' => '<path d="M10 56V24l6 4V16l6 4V12h20v8l6-4v12l6-4v32z"/><path d="M26 56V42a6 6 0 0 1 12 0v14"/>',
        'folha'   => '<path d="M12 52C10 28 28 10 54 10c0 26-16 44-40 42z"/><path d="M12 52L38 26"/>',
    ];
    $conteudo = $icones[$chave] ?? $icones['planeta'];
    return '<svg class="icone-svg" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $conteudo . '</svg>';
}

/** Lista de chaves de ícones disponíveis. */
function icones_disponiveis(): array
{
    return ['planeta' => 'Planeta', 'estrela' => 'Estrela', 'lua' => 'Lua', 'foguete' => 'Foguete', 'castelo' => 'Castelo', 'folha' => 'Folha'];
}

/** Porcentagem inteira (0-100) de $parte em relação a $total; 0 se o total for zero. */
function pct($parte, $total): int
{
    $total = (float) $total;
    if ($total <= 0) {
        return 0;
    }
    return (int) max(0, min(100, round(((float) $parte) / $total * 100)));
}

/** Escolhe singular/plural: plural(1, 'partida', 'partidas') => "1 partida". */
function plural(int $n, string $singular, string $plural): string
{
    return $n . ' ' . ($n === 1 ? $singular : $plural);
}

/** Data por extenso em português: "quarta-feira, 25 de novembro de 2026". */
function data_extensa(?int $timestamp = null): string
{
    $timestamp = $timestamp ?? time();
    $dias = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
    $meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    return $dias[(int) date('w', $timestamp)] . ', ' . date('j', $timestamp) . ' de ' . $meses[(int) date('n', $timestamp) - 1] . ' de ' . date('Y', $timestamp);
}

/** Tempo decorrido em texto curto: "agora", "há 5 min", "há 3 h", "há 2 dias" (ou a data). */
function tempo_atras(?string $data): string
{
    if (!$data) {
        return '-';
    }
    $segundos = time() - strtotime($data);
    if ($segundos < 60) {
        return 'agora';
    }
    if ($segundos < 3600) {
        return 'há ' . (int) floor($segundos / 60) . ' min';
    }
    if ($segundos < 86400) {
        return 'há ' . (int) floor($segundos / 3600) . ' h';
    }
    if ($segundos < 86400 * 7) {
        $dias = (int) floor($segundos / 86400);
        return 'há ' . $dias . ($dias === 1 ? ' dia' : ' dias');
    }
    return date('d/m/Y', strtotime($data));
}

/** Ícones de traço usados no painel administrativo. */
function icone_admin(string $nome): string
{
    $icones = [
        'usuarios'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-5.5 7-5.5s7 2 7 5.5M17 11a3 3 0 1 0 0-6M22 19c0-2.5-2-4-5-4.5"/>',
        'partidas'   => '<path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/>',
        'vitorias'   => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>',
        'derrotas'   => '<circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/>',
        'pontos'     => '<path d="M12 2l2.9 6.6 7.1.7-5.3 4.8 1.5 7-6.2-3.6-6.2 3.6 1.5-7L2 9.3l7.1-.7z"/>',
        'categorias' => '<circle cx="12" cy="12" r="4"/><ellipse cx="12" cy="13" rx="10" ry="3" transform="rotate(-15 12 13)"/>',
        'palavras'   => '<path d="M4 7V5h16v2M12 5v14M9 19h6"/>',
        'email'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'email-ok'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6M8 16l2.5 2.5L16 13"/>',
        'olho'       => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'lapis'      => '<path d="M4 20l1-4L16.5 4.5a2.1 2.1 0 0 1 3 3L8 19z"/><path d="M14 7l3 3"/>',
        'energia'    => '<path d="M12 3v9"/><path d="M6.3 6.8a8 8 0 1 0 11.4 0"/>',
        'lixeira'    => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        'busca'      => '<circle cx="11" cy="11" r="6.5"/><path d="M20 20l-4-4"/>',
        'check'      => '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.8"/>',
        'mais'       => '<path d="M12 5v14M5 12h14"/>',
    ];
    $conteudo = $icones[$nome] ?? $icones['partidas'];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $conteudo . '</svg>';
}
