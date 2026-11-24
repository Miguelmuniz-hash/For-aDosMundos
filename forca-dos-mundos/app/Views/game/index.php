<div class="game-header">
    <div style="display:flex; align-items:center; gap:0.75rem;">
        <span class="badge-categoria">Mundo: <?= e($estado['categoria_nome']) ?></span>
        <span class="badge-categoria" id="badge-erros">Erros: <?= (int) $estado['erros'] ?> / 6</span>
    </div>
    <div style="display:flex; align-items:center; gap:1rem;">
        <strong style="font-size:1.1rem;" id="pontos-previstos">Pontos possíveis: <?= (int) $estado['pontos_previstos'] ?></strong>
        <form action="<?= url('jogo/abandonar') ?>" method="POST" style="margin:0;" onsubmit="return confirm('Tem certeza que deseja abandonar esta partida?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline" style="padding:0.45rem 0.9rem;">Abandonar</button>
        </form>
    </div>
</div>

<div class="game-grid">
    <!-- Quadro da Forca (SVG com 7 estados: 0 a 6 erros) -->
    <div class="quadro-forca">
        <svg viewBox="0 0 220 260" width="210" height="250" stroke="#0A0A0A" stroke-width="5" stroke-linecap="round" fill="none">
            <!-- Base e estrutura fixa (0 erros) -->
            <line x1="20" y1="240" x2="180" y2="240" />
            <line x1="60" y1="240" x2="60" y2="20" />
            <line x1="60" y1="20" x2="150" y2="20" />
            <line x1="60" y1="55" x2="95" y2="20" />
            <line x1="150" y1="20" x2="150" y2="50" />

            <!-- Erro 1: Cabeça -->
            <circle id="parte-1" cx="150" cy="72" r="22" style="display: <?= $estado['erros'] >= 1 ? 'block' : 'none' ?>;" />
            <!-- Erro 2: Corpo -->
            <line id="parte-2" x1="150" y1="94" x2="150" y2="160" style="display: <?= $estado['erros'] >= 2 ? 'block' : 'none' ?>;" />
            <!-- Erro 3: Braço Esquerdo -->
            <line id="parte-3" x1="150" y1="110" x2="120" y2="140" style="display: <?= $estado['erros'] >= 3 ? 'block' : 'none' ?>;" />
            <!-- Erro 4: Braço Direito -->
            <line id="parte-4" x1="150" y1="110" x2="180" y2="140" style="display: <?= $estado['erros'] >= 4 ? 'block' : 'none' ?>;" />
            <!-- Erro 5: Perna Esquerda -->
            <line id="parte-5" x1="150" y1="160" x2="125" y2="205" style="display: <?= $estado['erros'] >= 5 ? 'block' : 'none' ?>;" />
            <!-- Erro 6: Perna Direita -->
            <line id="parte-6" x1="150" y1="160" x2="175" y2="205" style="display: <?= $estado['erros'] >= 6 ? 'block' : 'none' ?>;" />
        </svg>
        <p style="font-size:0.85rem; font-weight:700; color:var(--cor-cinza-texto); margin-top:0.5rem;" id="texto-restantes">
            Tentativas restantes: <?= (int) $estado['erros_restantes'] ?>
        </p>
    </div>

    <!-- Área da Palavra, Dica e Teclado Virtual -->
    <div class="card" style="text-align:center;">
        <div class="palavra-display" id="palavra-display">
            <?php foreach ($estado['mascara'] as $letra): ?>
                <div class="letra-slot <?= $letra ? 'revelada' : '' ?>">
                    <?= $letra ? e($letra) : '' ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="caixa-dica">
            💡 <strong>Dica:</strong> <?= e($estado['dica']) ?>
        </div>

        <div class="teclado-virtual" id="teclado-virtual">
            <?php foreach (range('A', 'Z') as $l): ?>
                <?php
                    $certa  = in_array($l, $estado['letras_certas'], true);
                    $errada = in_array($l, $estado['letras_erradas'], true);
                    $classe = $certa ? 'tecla certa' : ($errada ? 'tecla errada' : 'tecla');
                    $disabled = ($certa || $errada) ? 'disabled' : '';
                ?>
                <button type="button" class="<?= $classe ?>" data-letra="<?= $l ?>" <?= $disabled ?>>
                    <?= $l ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap;">
            <button type="button" id="btn-revelar" class="btn btn-revelar" <?= $estado['usou_revelar'] ? 'disabled' : '' ?>>
                ✨ Revelar uma letra (-30 pts)
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const csrfToken = <?= json_encode(csrf_token()) ?>;
    const urlLetra  = <?= json_encode(url('jogo/letra')) ?>;
    const urlRevelar = <?= json_encode(url('jogo/revelar')) ?>;
    let bloqueado = false;

    function atualizarInterface(estado) {
        // Atualiza slots da palavra
        const display = document.getElementById('palavra-display');
        display.innerHTML = '';
        estado.mascara.forEach(char => {
            const div = document.createElement('div');
            div.className = 'letra-slot' + (char ? ' revelada' : '');
            div.textContent = char ? char : '';
            display.appendChild(div);
        });

        // Atualiza SVG do boneco (1 a 6)
        for (let i = 1; i <= 6; i++) {
            const parte = document.getElementById('parte-' + i);
            if (parte) {
                parte.style.display = estado.erros >= i ? 'block' : 'none';
            }
        }

        // Atualiza contadores
        document.getElementById('badge-erros').textContent = 'Erros: ' + estado.erros + ' / 6';
        document.getElementById('texto-restantes').textContent = 'Tentativas restantes: ' + estado.erros_restantes;
        document.getElementById('pontos-previstos').textContent = 'Pontos possíveis: ' + estado.pontos_previstos;

        // Atualiza botões do teclado
        document.querySelectorAll('.tecla').forEach(btn => {
            const l = btn.dataset.letra;
            if (estado.letras_certas.includes(l)) {
                btn.className = 'tecla certa';
                btn.disabled = true;
            } else if (estado.letras_erradas.includes(l)) {
                btn.className = 'tecla errada';
                btn.disabled = true;
            }
        });

        // Atualiza botão revelar
        const btnRevelar = document.getElementById('btn-revelar');
        if (estado.usou_revelar) {
            btnRevelar.disabled = true;
        }

        // Redireciona se finalizou
        if (estado.finalizada && estado.resultado_url) {
            setTimeout(() => {
                window.location.href = estado.resultado_url;
            }, 500);
        }
    }

    async function enviarJogada(url, body = {}) {
        if (bloqueado) return;
        bloqueado = true;
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (!res.ok) {
                alert(data.erro || 'Erro ao processar jogada.');
            } else {
                atualizarInterface(data);
            }
        } catch (e) {
            alert('Falha de conexão com o servidor.');
        } finally {
            bloqueado = false;
        }
    }

    document.querySelectorAll('.tecla').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!btn.disabled) {
                enviarJogada(urlLetra, { letra: btn.dataset.letra });
            }
        });
    });

    // Suporte ao teclado físico
    document.addEventListener('keydown', (e) => {
        const letra = e.key.toUpperCase();
        if (/^[A-Z]$/.test(letra)) {
            const btn = document.querySelector('.tecla[data-letra="' + letra + '"]');
            if (btn && !btn.disabled) {
                btn.click();
            }
        }
    });

    document.getElementById('btn-revelar').addEventListener('click', () => {
        enviarJogada(urlRevelar);
    });
})();
</script>