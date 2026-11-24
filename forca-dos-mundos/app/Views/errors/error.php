<?php /** @var int $codigo @var string $titulo @var string $mensagem @var ?string $detalhe */
$home = function_exists('url') ? url('') : '/';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($codigo . ' - ' . $titulo) ?> | Forca dos Mundos</title>
<style>
  :root{--bg:#F4F6F9;--card:#FFFFFF;--borda:#E5E7EB;--texto:#111827;--ok:#10B981;--erro:#EF4444}
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;display:grid;place-items:center;background:var(--bg);color:var(--texto);
       font-family:'Inter','Segoe UI',system-ui,sans-serif;padding:24px}
  .erro-card{background:var(--card);border:1px solid var(--borda);border-radius:20px;padding:40px 32px;max-width:460px;width:100%;
             text-align:center;box-shadow:0 10px 30px rgba(17,24,39,.06)}
  .codigo{display:inline-flex;gap:6px;margin-bottom:18px}
  .codigo span{width:44px;height:52px;display:grid;place-items:center;border-radius:10px;background:var(--texto);color:#fff;font-weight:800;font-size:1.4rem}
  .codigo span:nth-child(2){background:var(--erro)}
  h1{margin:0 0 8px;font-size:1.4rem}
  p{margin:0 0 20px;color:#4B5563;line-height:1.5}
  code{display:block;margin:0 0 20px;padding:10px;border-radius:10px;background:var(--bg);font-size:.8rem;color:#6B7280;word-break:break-word}
  a.btn{display:inline-block;padding:12px 22px;border-radius:12px;background:var(--texto);color:#fff;text-decoration:none;font-weight:700}
  a.btn:hover{background:var(--ok)}
</style>
</head>
<body>
  <main class="erro-card">
    <div class="codigo" aria-hidden="true">
      <?php foreach (str_split((string) $codigo) as $d): ?><span><?= e($d) ?></span><?php endforeach; ?>
    </div>
    <h1><?= e($titulo) ?></h1>
    <p><?= e($mensagem ?: 'Algo saiu do esperado.') ?></p>
    <?php if (!empty($detalhe)): ?><code><?= e($detalhe) ?></code><?php endif; ?>
    <a class="btn" href="<?= e($home) ?>">Voltar ao inicio</a>
  </main>
</body>
</html>
