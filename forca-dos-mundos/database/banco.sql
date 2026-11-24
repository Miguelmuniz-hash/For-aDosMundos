-- =====================================================================
-- Forca dos Mundos - Projeto Integrador SENAI (Fabrica de Jogos)
-- Importar no phpMyAdmin (aba "Importar"). Cria o banco automaticamente.
-- Charset: utf8mb4 | Engine: InnoDB
-- =====================================================================
CREATE DATABASE IF NOT EXISTS forca_dos_mundos
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forca_dos_mundos;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS recuperacao_senhas;
DROP TABLE IF EXISTS partidas;
DROP TABLE IF EXISTS palavras;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome      VARCHAR(100) NOT NULL,
  apelido   VARCHAR(30)  NOT NULL,
  email     VARCHAR(150) NOT NULL,
  senha     VARCHAR(255) NOT NULL,
  foto      VARCHAR(255) NULL DEFAULT NULL,
  perfil    ENUM('admin','jogador') NOT NULL DEFAULT 'jogador',
  status    ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_apelido (apelido),
  UNIQUE KEY uq_usuarios_email (email),
  KEY idx_usuarios_perfil_status (perfil, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorias (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome      VARCHAR(60)  NOT NULL,
  descricao VARCHAR(255) NULL DEFAULT NULL,
  icone     VARCHAR(10)  NULL DEFAULT NULL COMMENT 'Emoji ou texto curto exibido no card',
  status    ENUM('ativa','inativa') NOT NULL DEFAULT 'ativa',
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categorias_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE palavras (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  categoria_id INT UNSIGNED NOT NULL,
  palavra      VARCHAR(30)  NOT NULL COMMENT 'Somente A-Z maiusculo',
  dica         VARCHAR(160) NOT NULL,
  criado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_palavras_categoria_palavra (categoria_id, palavra),
  CONSTRAINT fk_palavras_categoria FOREIGN KEY (categoria_id)
    REFERENCES categorias (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE partidas (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id     INT UNSIGNED NOT NULL,
  categoria_id   INT UNSIGNED NULL DEFAULT NULL,
  palavra_id     INT UNSIGNED NULL DEFAULT NULL,
  palavra_jogada VARCHAR(30)  NOT NULL COMMENT 'Copia da palavra (preserva historico)',
  erros          TINYINT UNSIGNED NOT NULL DEFAULT 0,
  pontos         INT NOT NULL DEFAULT 0,
  resultado      ENUM('vitoria','derrota') NOT NULL,
  usou_revelar   TINYINT(1) NOT NULL DEFAULT 0,
  data_hora      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_partidas_usuario_data (usuario_id, data_hora),
  KEY idx_partidas_ranking (pontos, data_hora),
  CONSTRAINT fk_partidas_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuarios (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_partidas_categoria FOREIGN KEY (categoria_id)
    REFERENCES categorias (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_partidas_palavra FOREIGN KEY (palavra_id)
    REFERENCES palavras (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE recuperacao_senhas (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id   INT UNSIGNED NOT NULL,
  token_hash   CHAR(64) NOT NULL COMMENT 'SHA-256 do token enviado ao usuario',
  expira_em    DATETIME NOT NULL,
  utilizado_em DATETIME NULL DEFAULT NULL,
  criado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_recuperacao_token (token_hash),
  KEY idx_recuperacao_usuario (usuario_id),
  CONSTRAINT fk_recuperacao_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuarios (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- SEED (senhas geradas com password_hash / bcrypt)
-- admin@forca.local   -> Admin@123
-- jogador@forca.local -> Jogador@123
-- ---------------------------------------------------------------------
INSERT INTO usuarios (nome, apelido, email, senha, perfil, status) VALUES
('Administrador do Sistema', 'admin',   'admin@forca.local',   '$2y$12$d.BUDKBnZR7.9EMWPq/xHecupFrEKJphOFlbrFd6vjZWy3nP9Z1HW', 'admin',   'ativo'),
('Jogador de Teste',         'jogador', 'jogador@forca.local', '$2y$12$8bvXHGk0LD3oJs3FqmbPOeFnxjACbLwg.BdzYrn8rwvNvBfWiCNWu', 'jogador', 'ativo');

INSERT INTO categorias (nome, descricao, icone, status) VALUES
('Espaco',     'Planetas, estrelas e tudo que existe alem da Terra.', '🪐', 'ativa'),
('Animais',    'Bichos de todos os cantos do planeta.',               '🦒', 'ativa'),
('Tecnologia', 'Computadores, programacao e internet.',               '💻', 'ativa');

INSERT INTO palavras (categoria_id, palavra, dica) VALUES
(1, 'JUPITER',   'Maior planeta do Sistema Solar'),
(1, 'SATURNO',   'Planeta famoso pelos aneis'),
(1, 'GALAXIA',   'Conjunto gigante de estrelas, como a Via Lactea'),
(1, 'COMETA',    'Corpo de gelo com cauda brilhante'),
(1, 'ASTEROIDE', 'Rocha que orbita o Sol, comum entre Marte e Jupiter'),
(1, 'ORBITA',    'Trajetoria de um astro ao redor de outro'),
(1, 'FOGUETE',   'Veiculo usado para viajar ao espaco'),
(1, 'NEBULOSA',  'Nuvem de gas e poeira onde nascem estrelas'),
(2, 'ELEFANTE',  'Maior animal terrestre'),
(2, 'GIRAFA',    'Tem o pescoco mais longo do reino animal'),
(2, 'GOLFINHO',  'Mamifero marinho muito inteligente'),
(2, 'PINGUIM',   'Ave que nao voa e vive em regioes geladas'),
(2, 'CAVALO',    'Animal usado para montaria e corridas'),
(2, 'TARTARUGA', 'Reptil lento com casco protetor'),
(2, 'LEOPARDO',  'Felino de pelagem manchada'),
(2, 'CAMALEAO',  'Reptil que muda de cor'),
(3, 'TECLADO',   'Periferico usado para digitar'),
(3, 'SERVIDOR',  'Computador que fornece servicos a outros'),
(3, 'NAVEGADOR', 'Programa para acessar paginas da web'),
(3, 'PROGRAMA',  'Conjunto de instrucoes executado pelo computador'),
(3, 'ALGORITMO', 'Sequencia de passos para resolver um problema'),
(3, 'INTERNET',  'Rede mundial de computadores'),
(3, 'MONITOR',   'Tela do computador'),
(3, 'BANCO',     'Local onde ficam armazenados os dados do sistema');
