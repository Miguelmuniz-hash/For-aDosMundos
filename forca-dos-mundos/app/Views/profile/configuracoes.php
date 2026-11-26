<section class="card form-estreito">
    <h1 class="card-titulo">Configurações</h1>
    <p class="texto-suave">Estas preferências ficam salvas neste navegador.</p>

    <div class="config-lista">
        <label class="config-item">
            <span>
                <strong>Efeitos sonoros</strong>
                <small>Sons de acerto, erro, vitória e derrota.</small>
            </span>
            <input type="checkbox" class="interruptor" id="cfg-som" data-config="som">
        </label>
        <label class="config-item">
            <span>
                <strong>Volume</strong>
                <small>Ajuste a intensidade dos sons.</small>
            </span>
            <input type="range" min="0" max="100" step="5" id="cfg-volume" data-config="volume">
        </label>
        <label class="config-item">
            <span>
                <strong>Céu estrelado animado</strong>
                <small>Desative para economizar bateria em celulares.</small>
            </span>
            <input type="checkbox" class="interruptor" id="cfg-estrelas" data-config="estrelas">
        </label>
        <label class="config-item">
            <span>
                <strong>Reduzir animações</strong>
                <small>Diminui movimentos e transições da interface.</small>
            </span>
            <input type="checkbox" class="interruptor" id="cfg-reduzir" data-config="reduzir">
        </label>
    </div>

    <div class="form-botoes">
        <button type="button" class="btn btn-secundario btn-sm" id="cfg-testar">Testar som</button>
    </div>
</section>
