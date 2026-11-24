# Forca dos Mundos

Jogo 02 do Projeto Integrador SENAI - Fabrica de Jogos.
Sistema web em **PHP 8+ / MySQL (PDO) / HTML5 / CSS3 / JavaScript nativo**, arquitetura **MVC**, sem Composer e sem Node.js.

## Requisitos
- XAMPP com **PHP 8.1 ou superior**, Apache e MySQL/MariaDB
- Modulos Apache: `mod_rewrite` (ja vem ativo no XAMPP) e extensoes PHP `pdo_mysql`, `fileinfo`, `mbstring` (padrao no XAMPP)

## Instalacao no XAMPP
1. Copie a pasta **`forca-dos-mundos`** para `C:\xampp\htdocs\` (Linux: `/opt/lampp/htdocs/`).
2. Abra o **XAMPP Control Panel** e inicie **Apache** e **MySQL**.
3. Acesse `http://localhost/phpmyadmin` -> aba **Importar** -> escolha `database/banco.sql` -> **Importar**.
   (o script cria o banco `forca_dos_mundos`, as tabelas e os dados iniciais)
4. Confira `config/config.php`: o padrao do XAMPP (`root`, senha vazia, host `127.0.0.1`) ja funciona.
5. Acesse **`http://localhost/forca-dos-mundos/`** (redireciona para `/public`).

> A URL base e detectada automaticamente: o projeto funciona em qualquer nome de pasta dentro do `htdocs`.
> Se as rotas retornarem 404, verifique se `AllowOverride All` esta habilitado para o `htdocs` (padrao no XAMPP).

## Credenciais de teste
| Perfil  | E-mail               | Senha        |
|---------|----------------------|--------------|
| Admin   | admin@forca.local    | Admin@123    |
| Jogador | jogador@forca.local  | Jogador@123  |

## Recuperacao de senha (modo local)
Como o XAMPP nao envia e-mails, com `reset.modo_local = true` (em `config/config.php`) o link de redefinicao
e **exibido na propria tela**. Somente o hash SHA-256 do token e gravado no banco; o link expira em 30 minutos e e de uso unico.
Em producao, defina `modo_local` como `false` e integre um servico de e-mail.

## Estrutura de pastas
```
forca-dos-mundos/
├── index.php                  Atalho para /public
├── app/
│   ├── Core/                  Router, Controller, Model, Database, Session, Csrf, Auth, Validator, Upload, Request, Response
│   ├── Controllers/           (Etapa 2)
│   ├── Models/                (Etapa 2)
│   └── Views/                 (Etapa 3)  errors/error.php ja incluso
├── config/                    config.php (app, banco, jogo, upload) e database.php
├── database/banco.sql         Esquema + seed (2 usuarios, 3 categorias, 24 palavras)
├── routes/web.php             Todas as rotas e middlewares (guest | auth | admin)
└── public/                    Unica pasta exposta ao navegador
    ├── index.php              Front controller
    ├── .htaccess              Reescrita de URLs + cabecalhos de seguranca
    ├── assets/{css,js,img}    (CSS/JS na Etapa 4)  img/logo.png ja incluso
    └── uploads/perfis/        Fotos de perfil (execucao de scripts bloqueada)
```

## Seguranca embutida no Core
- **PDO** com `EMULATE_PREPARES = false` e prepared statements em todo acesso ao banco
- **CSRF**: toda rota `POST` e validada automaticamente pelo Router (token por sessao, `hash_equals`)
- **XSS**: helper `e()` (`htmlspecialchars`) em todas as saidas
- **Sessao**: cookie `HttpOnly` + `SameSite=Lax`, `use_strict_mode`, `session_regenerate_id(true)` no login
- **Rotas admin** protegidas no back-end (`Auth::requireAdmin`, resposta 403) e conta inativa perde a sessao
- **Upload**: MIME real via `finfo`, `getimagesize`, limite de 2 MB, nome aleatorio, pasta sem execucao de PHP
- Pastas `app/`, `config/`, `database/` e `routes/` com acesso web negado

## Regras de pontuacao
- Vitoria: `100 + (20 x erros restantes)` | Derrota (6 erros): `0`
- "Revelar uma letra" (1x por partida): `-30` pontos caso vença (minimo 0)

## Entrega por etapas
1. Estrutura, README, banco, Core, config e rotas
2. Models e Controllers
3. Views
4. CSS e JavaScript (SVG da forca e microinteracoes)
