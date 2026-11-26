-- =====================================================================
--  FORCA DOS MUNDOS  |  Fábrica de Jogos - Jogo 02
--  Importe este arquivo no phpMyAdmin (aba "Importar").
--  Ele cria o banco do zero, as tabelas, os relacionamentos e os dados iniciais.
-- =====================================================================

DROP DATABASE IF EXISTS forca_dos_mundos;

CREATE DATABASE forca_dos_mundos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE forca_dos_mundos;

-- ---------------------------------------------------------------------
-- Usuários (jogadores e administradores)
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome      VARCHAR(120) NOT NULL,
    apelido   VARCHAR(30)  NOT NULL,
    email     VARCHAR(160) NOT NULL,
    senha     VARCHAR(255) NOT NULL,
    foto      VARCHAR(80)  NULL DEFAULT NULL,
    perfil    ENUM('admin', 'jogador') NOT NULL DEFAULT 'jogador',
    status    ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    -- Preenchido quando a pessoa clica no link de confirmação enviado por e-mail.
    -- Enquanto for NULL, o login é bloqueado.
    email_verificado_em DATETIME NULL DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    UNIQUE KEY uq_usuarios_apelido (apelido),
    KEY idx_usuarios_perfil_status (perfil, status)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Categorias (os "mundos")
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome      VARCHAR(60)  NOT NULL,
    descricao VARCHAR(255) NOT NULL DEFAULT '',
    imagem    VARCHAR(80)  NULL DEFAULT NULL,
    icone     VARCHAR(20)  NOT NULL DEFAULT 'planeta',
    status    ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_nome (nome)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Palavras (somente A-Z, sem acento, sem espaço; não se repetem)
-- ---------------------------------------------------------------------
CREATE TABLE palavras (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria_id INT UNSIGNED NOT NULL,
    palavra      VARCHAR(30)  NOT NULL,
    dica         VARCHAR(200) NOT NULL,
    criado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_palavras_palavra (palavra),
    KEY idx_palavras_categoria (categoria_id),
    CONSTRAINT fk_palavras_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Partidas (cada partida é gravada uma única vez, ao terminar)
-- ---------------------------------------------------------------------
CREATE TABLE partidas (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id     INT UNSIGNED NULL,
    categoria_id   INT UNSIGNED NULL,
    palavra_id     INT UNSIGNED NULL,
    palavra_jogada VARCHAR(30)  NOT NULL,
    erros          TINYINT UNSIGNED NOT NULL,
    pontos         INT UNSIGNED NOT NULL DEFAULT 0,
    resultado      ENUM('vitoria', 'derrota') NOT NULL,
    data_hora      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_partidas_usuario (usuario_id),
    KEY idx_partidas_categoria (categoria_id),
    KEY idx_partidas_ranking (pontos, data_hora),
    -- Ao excluir a conta, as partidas permanecem anonimizadas (usuario_id = NULL).
    CONSTRAINT fk_partidas_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_partidas_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_partidas_palavra
        FOREIGN KEY (palavra_id) REFERENCES palavras (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Recuperação de senha (guarda apenas o HASH do token)
-- ---------------------------------------------------------------------
CREATE TABLE recuperacao_senhas (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NOT NULL,
    token_hash  CHAR(64) NOT NULL,
    expira_em   DATETIME NOT NULL,
    utilizado_em DATETIME NULL DEFAULT NULL,
    criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_recuperacao_token (token_hash),
    KEY idx_recuperacao_usuario (usuario_id),
    CONSTRAINT fk_recuperacao_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Confirmação de e-mail do cadastro (guarda apenas o HASH do token)
-- ---------------------------------------------------------------------
CREATE TABLE verificacoes_email (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id   INT UNSIGNED NOT NULL,
    token_hash   CHAR(64) NOT NULL,
    expira_em    DATETIME NOT NULL,
    utilizado_em DATETIME NULL DEFAULT NULL,
    criado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_verificacao_token (token_hash),
    KEY idx_verificacao_usuario (usuario_id),
    CONSTRAINT fk_verificacao_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Tentativas de login (controle contra força bruta)
-- ---------------------------------------------------------------------
CREATE TABLE tentativas_login (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email     VARCHAR(160) NOT NULL,
    ip        VARCHAR(45)  NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tentativas_email (email, criado_em),
    KEY idx_tentativas_ip (ip, criado_em)
) ENGINE = InnoDB;

-- =====================================================================
-- DADOS INICIAIS
-- Senhas guardadas em hash (bcrypt / password_hash). Veja o README.
--   admin@forcadosmundos.com    -> Admin@123
--   jogador@forcadosmundos.com  -> Jogador@123
-- =====================================================================
-- As contas de teste já nascem com o e-mail confirmado (senão não conseguiriam entrar).
INSERT INTO usuarios (nome, apelido, email, senha, perfil, status, email_verificado_em) VALUES
('Administrador dos Mundos', 'Guardião', 'admin@forcadosmundos.com',
 '$2y$10$3QfZjFedyw1qsR8SWanad.giry1CHoBBcwVCUq8myahUDbGiVH0bq', 'admin', 'ativo', NOW()),
('Jogador de Teste', 'Viajante', 'jogador@forcadosmundos.com',
 '$2y$10$Njea0kDbJ76wWdy0GR6VFOONMR03MdQdyFn.TxBGvcVePv0TBo/2K', 'jogador', 'ativo', NOW());

INSERT INTO categorias (nome, descricao, icone, status) VALUES
('Cosmos', 'Planetas, estrelas e mistérios do espaço sideral.', 'planeta', 'ativo'),
('Reinos Encantados', 'Criaturas, magias e lugares de fantasia.', 'castelo', 'ativo'),
('Natureza Selvagem', 'Paisagens e maravilhas naturais do nosso mundo.', 'folha', 'ativo');

INSERT INTO palavras (categoria_id, palavra, dica) VALUES
(1, 'PLANETA',     'Corpo celeste que gira em torno de uma estrela.'),
(1, 'GALAXIA',     'Conjunto gigante de estrelas, como a Via Láctea.'),
(1, 'COMETA',      'Bola de gelo e poeira com uma cauda brilhante.'),
(1, 'ASTRONAUTA',  'Quem viaja pelo espaço sideral.'),
(1, 'NEBULOSA',    'Nuvem de gás e poeira onde nascem estrelas.'),
(1, 'ORBITA',      'Caminho que um astro percorre ao redor de outro.'),
(1, 'ECLIPSE',     'Quando um astro esconde a luz de outro.'),
(1, 'FOGUETE',     'Veículo usado para levar naves ao espaço.'),
(2, 'DRAGAO',      'Criatura que voa e solta fogo pela boca.'),
(2, 'UNICORNIO',   'Cavalo mágico com um chifre na testa.'),
(2, 'FEITICEIRO',  'Aquele que domina magias e poções.'),
(2, 'CASTELO',     'Fortaleza onde vivem reis e rainhas.'),
(2, 'GRIMORIO',    'Livro antigo cheio de feitiços.'),
(2, 'PORTAL',      'Passagem mágica que liga um mundo a outro.'),
(2, 'ELFO',        'Ser de orelhas pontudas que vive nas florestas.'),
(2, 'AMULETO',     'Objeto encantado que protege quem o carrega.'),
(3, 'FLORESTA',    'Grande área coberta por árvores.'),
(3, 'OCEANO',      'Enorme massa de água salgada que cobre o planeta.'),
(3, 'VULCAO',      'Montanha que pode expelir lava.'),
(3, 'DESERTO',     'Região muito seca, com pouca chuva e muita areia.'),
(3, 'CACHOEIRA',   'Queda de água de um rio em um desnível.'),
(3, 'GELEIRA',     'Grande massa de gelo que se move lentamente.'),
(3, 'PANTANAL',    'Imensa planície alagável que abriga jacarés e onças.'),
(3, 'MONTANHA',    'Grande elevação natural do terreno.');
