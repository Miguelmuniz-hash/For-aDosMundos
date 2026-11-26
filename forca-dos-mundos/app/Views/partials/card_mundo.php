<?php /** Card ilustrado de categoria. Requer $cat. */ ?>
<article class="mundo">
    <div class="mundo-arte mundo-<?= e($cat['icone']) ?>" <?php if (!empty($cat['imagem'])): ?>style="background-image:url('<?= e(upload_url($cat['imagem'], 'categorias')) ?>')"<?php endif; ?>>
        <span class="mundo-icone"><?= icone_svg($cat['icone']) ?></span>
    </div>
    <div class="mundo-corpo">
        <h3><?= e($cat['nome']) ?></h3>
        <p><?= e($cat['descricao']) ?></p>
        <div class="mundo-rodape">
            <span class="badge badge-neutro"><?= (int) $cat['total_palavras'] ?> palavras</span>
            <?php if ((int) $cat['total_palavras'] > 0): ?>
                <form method="post" action="<?= e(url('/jogo/iniciar')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="categoria_id" value="<?= (int) $cat['id'] ?>">
                    <button type="submit" class="btn btn-primario btn-sm" data-carregando>Jogar</button>
                </form>
            <?php else: ?>
                <span class="badge badge-erro">Sem palavras</span>
            <?php endif; ?>
        </div>
    </div>
</article>
