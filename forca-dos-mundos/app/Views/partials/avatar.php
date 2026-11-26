<?php
/**
 * Avatar do usuário. Parâmetros: $avatarUsuario (array), $avatarClasse (opcional).
 */
$avatarFoto = foto_url($avatarUsuario ?? null);
$avatarClasse = $avatarClasse ?? '';
?>
<?php if ($avatarFoto): ?>
    <img class="avatar <?= e($avatarClasse) ?>" src="<?= e($avatarFoto) ?>" alt="Foto de <?= e($avatarUsuario['apelido'] ?? '') ?>">
<?php else: ?>
    <span class="avatar avatar-iniciais <?= e($avatarClasse) ?>"><?= e(iniciais($avatarUsuario['apelido'] ?? '?')) ?></span>
<?php endif; ?>
