-- =====================================================================
--  ATUALIZAÇÃO para quem JÁ tem o banco "forca_dos_mundos" criado
--  (não apaga nenhum dado). No phpMyAdmin: selecione o banco
--  forca_dos_mundos > aba "Importar" > escolha este arquivo.
--
--  Use este arquivo se o seu banco foi criado com a versão antiga do
--  banco.sql, que não tinha a confirmação de e-mail. Sem estas peças,
--  o login e o cadastro dão erro.
--
--  Compatível com o MariaDB do XAMPP. (No MySQL 8 puro, remova o
--  "IF NOT EXISTS" do ALTER TABLE e rode só se a coluna ainda não existir.)
-- =====================================================================
USE forca_dos_mundos;

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS email_verificado_em DATETIME NULL DEFAULT NULL AFTER status;

CREATE TABLE IF NOT EXISTS verificacoes_email (
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

-- As duas contas de teste do README passam a contar como confirmadas.
UPDATE usuarios
   SET email_verificado_em = NOW()
 WHERE email IN ('admin@forcadosmundos.com', 'jogador@forcadosmundos.com')
   AND email_verificado_em IS NULL;

-- Se você tem OUTRAS contas antigas que devem continuar entrando sem confirmar
-- o e-mail, tire o "--" da linha abaixo e importe de novo:
-- UPDATE usuarios SET email_verificado_em = criado_em WHERE email_verificado_em IS NULL;
-- (Também dá para confirmar uma conta específica pelo Painel Admin > Usuários.)
