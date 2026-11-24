<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;

/** Escapa texto para HTML (protecao XSS). */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL absoluta (a partir da raiz publica) independente da pasta onde o projeto esta no XAMPP. */
function url(string $caminho = ''): string
{
    return Config::baseUrl() . '/' . ltrim($caminho, '/');
}

function asset(string $caminho): string
{
    return url('assets/' . ltrim($caminho, '/'));
}

function redirect(string $caminho): never
{
    $destino = preg_match('#^https?://#i', $caminho) ? $caminho : url($caminho);
    header('Location: ' . $destino);
    exit;
}

function config(string $chave, mixed $padrao = null): mixed
{
    return Config::get($chave, $padrao);
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

/** Valor antigo de formulario (reaproveitado apos erro de validacao). */
function old(string $campo, string $padrao = ''): string
{
    $antigos = Session::flashed('_old', []);
    return e($antigos[$campo] ?? $padrao);
}

/** Erros de validacao do request anterior. */
function errors(?string $campo = null): mixed
{
    $erros = Session::flashed('_errors', []);
    return $campo === null ? $erros : ($erros[$campo] ?? null);
}

function auth_user(): ?array
{
    return Auth::user();
}

function foto_url(?array $usuario): ?string
{
    if (!$usuario || empty($usuario['foto'])) {
        return null;
    }
    return url('uploads/perfis/' . rawurlencode(basename((string) $usuario['foto'])));
}

function formatar_data(?string $data, string $formato = 'd/m/Y H:i'): string
{
    if (!$data) {
        return '-';
    }
    $ts = strtotime($data);
    return $ts ? date($formato, $ts) : '-';
}

/** Verifica se a URI atual comeca com o prefixo (para destacar item ativo do menu). */
function rota_ativa(string $prefixo): bool
{
    return str_starts_with(\App\Core\Request::path(), '/' . trim($prefixo, '/'));
}
