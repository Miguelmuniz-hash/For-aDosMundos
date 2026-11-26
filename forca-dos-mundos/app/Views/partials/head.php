<?php /** Cabeçalho HTML comum. Parâmetros: $titulo, $cssExtras (array de arquivos em assets/css). */ ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="theme-color" content="#070b1e">
<title><?= e($titulo ?? 'Forca dos Mundos') ?> · Forca dos Mundos</title>
<link rel="icon" type="image/png" href="<?= e(asset('img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;800&family=Nunito:wght@400;600;700;800&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/base.css')) ?>">
<?php foreach (($cssExtras ?? []) as $arquivoCss): ?>
    <link rel="stylesheet" href="<?= e(asset('css/' . $arquivoCss)) ?>">
<?php endforeach; ?>
