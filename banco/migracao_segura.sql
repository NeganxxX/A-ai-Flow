-- ============================================================
-- AÇAÍ FLOW - MIGRAÇÃO SEGURA DA BASE EXISTENTE
-- Use este arquivo somente em uma base já criada com versões
-- anteriores do projeto. Faça backup do banco antes.
-- ============================================================

USE acai_flow;

-- O arquivo verifica/cria objetos sem apagar os dados existentes.

CREATE TABLE IF NOT EXISTS enderecos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    apelido VARCHAR(60) DEFAULT NULL,
    cep VARCHAR(10) NOT NULL,
    rua VARCHAR(160) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(100) DEFAULT NULL,
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    referencia VARCHAR(160) DEFAULT NULL,
    principal TINYINT(1) NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_endereco_usuario (usuario_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedido_status_historico (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    status ENUM('recebido','confirmado','preparando','pronto','saiu_entrega','entregue','cancelado') NOT NULL,
    mensagem VARCHAR(255) DEFAULT NULL,
    alterado_por_admin_id INT UNSIGNED DEFAULT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_status_pedido_data (pedido_id, criado_em)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    administrador_id INT UNSIGNED DEFAULT NULL,
    acao VARCHAR(80) NOT NULL,
    entidade VARCHAR(80) DEFAULT NULL,
    entidade_id INT UNSIGNED DEFAULT NULL,
    detalhes VARCHAR(500) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_log_admin_data (administrador_id, criado_em),
    KEY idx_log_entidade (entidade, entidade_id)
) ENGINE=InnoDB;

ALTER TABLE usuarios

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE administradores
    ADD COLUMN IF NOT EXISTS ultimo_login_em DATETIME NULL,
    ADD COLUMN IF NOT EXISTS atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE mensagens_contato
    ADD COLUMN IF NOT EXISTS status ENUM('nova','lida','respondida','arquivada') NOT NULL DEFAULT 'nova',
    ADD COLUMN IF NOT EXISTS atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE pedidos
    ADD COLUMN IF NOT EXISTS endereco_id INT UNSIGNED NULL,
    ADD COLUMN IF NOT EXISTS pagamento_status ENUM('pendente','recebido','cancelado') NOT NULL DEFAULT 'pendente',
    ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS taxa_entrega DECIMAL(10,2) NOT NULL DEFAULT 0.00;

ALTER TABLE itens_pedido
    ADD COLUMN IF NOT EXISTS criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- Garante que o adicional esteja sempre em R$ 2,00.
UPDATE opcoes
SET preco_adicional = 2.00
WHERE tipo = 'adicional' AND ativo = 1;

-- Atualiza os limites oficiais.
UPDATE tamanhos SET limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=1 WHERE tipo='copo' AND nome='180ml';
UPDATE tamanhos SET limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=2 WHERE tipo='copo' AND nome IN ('200ml','300ml');
UPDATE tamanhos SET limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=3 WHERE tipo='copo' AND nome IN ('400ml','500ml');
UPDATE tamanhos SET limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=2, limite_acompanhamentos=4 WHERE tipo='copo' AND nome='700ml';
UPDATE tamanhos SET limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=3 WHERE tipo='barca' AND nome='PP';
UPDATE tamanhos SET limite_frutas=2, limite_cremes=1, limite_adicionais=1, limite_caldas=2, limite_acompanhamentos=4 WHERE tipo='barca' AND nome IN ('P','M');
UPDATE tamanhos SET limite_frutas=3, limite_cremes=1, limite_adicionais=1, limite_caldas=3, limite_acompanhamentos=5 WHERE tipo='barca' AND nome='G';
UPDATE tamanhos SET limite_frutas=4, limite_cremes=1, limite_adicionais=1, limite_caldas=3, limite_acompanhamentos=5 WHERE tipo='barca' AND nome='GG';
UPDATE tamanhos SET limite_frutas=5, limite_cremes=1, limite_adicionais=1, limite_caldas=4, limite_acompanhamentos=6 WHERE tipo='barca' AND nome='XG';

-- Credencial administrativa única do projeto.
-- A migração remove outras contas administrativas e fixa a conta única.
DELETE FROM administradores
WHERE email <> 'admin@acaiflow.local';

INSERT INTO administradores (nome,email,senha,ativo)
SELECT 'Administrador Açaí Flow','admin@acaiflow.local',
       '$2y$12$7Al902e32NRKHBWa8vMtVu/CHjn5Em7p/N4NeRs3EQ6SzLMzohtBG',1
WHERE NOT EXISTS (
    SELECT 1 FROM administradores WHERE email='admin@acaiflow.local'
);

UPDATE administradores
SET nome='Administrador Açaí Flow',
    senha='$2y$12$7Al902e32NRKHBWa8vMtVu/CHjn5Em7p/N4NeRs3EQ6SzLMzohtBG',
    ativo=1
WHERE email='admin@acaiflow.local';

INSERT INTO configuracoes(chave,valor) VALUES
('pix_nome_recebedor','Açaí Flow')
ON DUPLICATE KEY UPDATE valor=VALUES(valor);

-- Observação:
-- Caso a sua versão do MariaDB não aceite ADD COLUMN IF NOT EXISTS,
-- use banco/banco.sql em uma instalação limpa ou execute os ALTER TABLE
-- individualmente pelo phpMyAdmin.
