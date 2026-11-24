<?php
use App\Core\Session;

$sucesso = Session::flashed('sucesso');
$erro    = Session::flashed('erro');
$usuario = auth_user();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? 'Força dos Mundos') ?></title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { margin: 0; background: #0f172a; color: #f8fafc; min-height: 100vh; display: flex; flex-direction: column; }
        header { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; }
        header a { color: #38bdf8; text-decoration: none; font-weight: bold; font-size: 1.2rem; }
        main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .card { background: #1e293b; padding: 2rem; border-radius: 12px; width: 100%; max-width: 420px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); border: 1px solid #334155; }
        .card h1 { margin-top: 0; font-size: 1.5rem; text-align: center; color: #f8fafc; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.4rem; font-size: 0.9rem; color: #cbd5e1; }
        input { width: 100%; padding: 0.75rem; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: #fff; font-size: 1rem; }
        input:focus { outline: none; border-color: #38bdf8; }
        button { width: 100%; padding: 0.8rem; border: none; border-radius: 6px; background: #0284c7; color: #fff; font-size: 1rem; font-weight: bold; cursor: pointer; margin-top: 0.5rem; }
        button:hover { background: #0369a1; }
        .links { margin-top: 1.2rem; text-align: center; font-size: 0.9rem; }
        .links a { color: #38bdf8; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
        .alert { padding: 0.85rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; word-break: break-all; }
        .alert-sucesso { background: #065f46; border: 1px solid #10b981; color: #ecfdf5; }
        .alert-erro { background: #7f1d1d; border: 1px solid #ef4444; color: #fef2f2; }
        .field-error { color: #f87171; font-size: 0.8rem; margin-top: 0.25rem; display: block; }
    </style>
</head>
<body>
    <header>
        <a href="<?= url('') ?>">🪐 Força dos Mundos</a>
        <?php if ($usuario): ?>
            <div style="display:flex; gap:1rem; align-items:center;">
                <span>Olá, <strong><?= e($usuario['apelido']) ?></strong></span>
                <form action="<?= url('logout') ?>" method="POST" style="margin:0;">
                    <?= csrf_field() ?>
                    <button type="submit" style="padding:0.4rem 0.8rem; margin:0; background:#dc2626; font-size:0.85rem;">Sair</button>
                </form>
            </div>
        <?php endif; ?>
    </header>

    <main>
        <div class="card">
            <?php if ($sucesso): ?>
                <div class="alert alert-sucesso"><?= e($sucesso) ?></div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-erro"><?= e($erro) ?></div>
            <?php endif; ?>

            <?= $conteudo ?>
        </div>
    </main>
</body>
</html>