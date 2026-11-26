# Forca dos Mundos — Fábrica de Jogos (Jogo 02)

Jogo de forca temático (espaço/fantasia) feito em **PHP 8, MySQL (PDO), HTML5, CSS3 e JavaScript**, com arquitetura **MVC**, painel administrativo e ranking.
Roda localmente no **XAMPP**, sem Composer e sem Node.js.

## Instalação no XAMPP

1. **Copie a pasta** `forca-dos-mundos` para dentro de `htdocs`
   (Windows: `C:\xampp\htdocs\forca-dos-mundos`).
2. Abra o **XAMPP Control Panel** e inicie **Apache** e **MySQL**.
   - O módulo `mod_rewrite` do Apache precisa estar ativo (já vem ativo no XAMPP).
   - Se o XAMPP ignorar os `.htaccess`, confira `AllowOverride All` em `httpd.conf`.
3. Abra `http://localhost/phpmyadmin` → aba **Importar** → escolha `database/banco.sql` → **Importar**.
   O arquivo cria o banco `forca_dos_mundos`, as tabelas e os dados iniciais.
4. **Conexão PDO:** edite `config/database.php` se o seu MySQL tiver senha
   (padrão do XAMPP: usuário `root`, senha vazia).
5. Acesse: **http://localhost/forca-dos-mundos/**

### Já tenho o banco criado de antes?

Se o seu banco foi importado com uma versão antiga do `banco.sql` (sem a confirmação de e-mail), importe
`database/atualizacao_verificacao_email.sql` no phpMyAdmin. Ele **não apaga dados**: só adiciona a coluna
`usuarios.email_verificado_em` e a tabela `verificacoes_email`, e marca as duas contas de teste como confirmadas.

## Contas de teste

| Perfil        | E-mail                       | Senha         |
|---------------|------------------------------|---------------|
| Administrador | admin@forcadosmundos.com     | `Admin@123`   |
| Jogador       | jogador@forcadosmundos.com   | `Jogador@123` |

As senhas ficam no banco apenas em hash (bcrypt). O painel admin está em `/admin`.

## Como jogar (regras do Catálogo — Jogo 02)

1. Escolha um mundo (categoria). O PHP sorteia uma palavra e mostra uma dica.
2. Clique nas letras do teclado virtual (ou use o teclado físico).
3. Cada erro desenha uma parte do boneco (7 estados, 0 a 6). Com **6 erros** você perde.
4. **Vitória:** 100 pontos + 20 por erro que sobrou (ex.: 2 erros → 100 + 4×20 = 180). **Derrota:** 0.
5. Abandonar a partida conta como derrota (0 pontos).

Toda a lógica (acerto, erro, fim de jogo, pontos) está em `app/Services/ForcaJogo.php` e no `GameController`.
O estado fica na **sessão PHP**; a palavra secreta só é enviada ao navegador depois que a partida termina.

## Estrutura (MVC)

```
app/Controllers   Auth, Home, Game, Profile, Ranking, Admin
app/Models        Usuario, Categoria, Palavra, Partida, RecuperacaoSenha, VerificacaoEmail, TentativaLogin
app/Libraries     PHPMailer
app/Services      ForcaJogo (regras do jogo), Email (SMTP)
app/Views         layouts, partials, auth, home, game, profile, ranking, admin, errors
app/Core          Router, Controller, Model, Database, Upload, helpers
config/           database.php
routes/web.php    mapa de rotas
public/           index.php, assets (css/js), uploads
database/banco.sql
```

## Segurança implementada

`password_hash`/`password_verify`, PDO com prepared statements, `htmlspecialchars` (função `e()`), token CSRF em todo POST,
regeneração do ID de sessão no login, cookie de sessão HttpOnly/SameSite, bloqueio após 5 tentativas de login em 15 min,
autorização de rotas no servidor (`exigirLogin` / `exigirAdmin`), upload com validação de MIME real e nome aleatório
(pasta `uploads` sem execução de scripts), transações na recuperação de senha, proteção do último administrador ativo.

## Confirmação de e-mail e envio de e-mails

O cadastro exige confirmar o e-mail (link válido por 24 h, guardado só como hash). Os e-mails saem por SMTP
com PHPMailer; configure `config/mail.php` (no Gmail use uma **senha de app**, nunca a senha da conta).
Se o e-mail não puder ser enviado, o erro vai para `storage/emails.log` e, com `'ambiente' => 'local'`,
o link aparece na tela ("Modo desenvolvimento"). O administrador também pode confirmar um e-mail manualmente em
**Admin > Usuários**.

## Painel administrativo

`/admin` mostra o resumo do jogo (usuários, partidas, taxa de vitória, pontos, erros nas vitórias,
desempenho por mundo, pontos de atenção, ranking e cadastros recentes). As telas de categorias, palavras,
usuários e partidas trazem resumo de números, busca/filtros, taxa de vitória/acerto e ações por ícone.
Atalhos como `/admin/palavras?novo=1` abrem direto o formulário de cadastro.

## Recuperação de senha

O token é gerado com `random_bytes`, **só o hash (SHA-256) vai para o banco**, vale 30 minutos e é de uso único.
Sem servidor de e-mail no XAMPP, em `config/database.php` o `'ambiente' => 'local'` faz o link aparecer num aviso
"Modo desenvolvimento" e ser gravado em `storage/emails.log`. Em produção use `'ambiente' => 'producao'`:
nada é exibido na tela e o link é enviado por `mail()`.

## Observações de projeto

- A palavra é **única em todo o jogo** (regra do catálogo: não repetir), o que também garante unicidade por categoria.
- Ao excluir uma conta, as partidas ficam **anonimizadas** (`usuario_id = NULL`) e saem do ranking.
- Foi acrescentada a tabela `tentativas_login` e a coluna `categorias.icone` (além das pedidas).
- Preferências de som/animações ficam no navegador (`localStorage`). Sons são gerados pela Web Audio API.
- As fontes (Cinzel/Nunito) vêm do Google Fonts; sem internet o sistema usa fontes padrão e continua funcionando.
