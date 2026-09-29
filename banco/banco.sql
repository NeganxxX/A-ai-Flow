-- ============================================================
-- AÇAÍ FLOW - BANCO DE DADOS PRINCIPAL
-- MySQL / MariaDB + InnoDB + utf8mb4
--
-- Instalação limpa para XAMPP/phpMyAdmin.
-- ATENÇÃO: este arquivo recria as tabelas do projeto.
-- Para um banco já utilizado, use a migração separada.
-- ============================================================

CREATE DATABASE IF NOT EXISTS acai_flow
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE acai_flow;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS combo_configuracoes;
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS pedido_status_historico;
DROP TABLE IF EXISTS mensagens_contato;
DROP TABLE IF EXISTS itens_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS enderecos;
DROP TABLE IF EXISTS produto_opcoes;
DROP TABLE IF EXISTS opcoes;
DROP TABLE IF EXISTS tamanhos;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS configuracoes;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS administradores;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. ADMINISTRADOR
-- Apenas a conta criada neste script é disponibilizada pelo sistema.
-- Não existe cadastro público de administradores.
-- A senha é armazenada somente como hash bcrypt.
-- ============================================================
CREATE TABLE administradores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_login_em DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_email (email),
    KEY idx_admin_ativo (ativo)
) ENGINE=InnoDB;

-- ============================================================
-- 2. CLIENTES
-- A senha é armazenada com password_hash() no PHP.
-- ============================================================
CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    telefone VARCHAR(25) DEFAULT NULL,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario_email (email),
    KEY idx_usuario_ativo (ativo)
) ENGINE=InnoDB;

-- ============================================================
-- 3. ENDEREÇOS DOS CLIENTES
-- Guarda endereços reutilizáveis. O pedido mantém uma cópia do
-- endereço usado no momento da compra, preservando o histórico.
-- ============================================================
CREATE TABLE enderecos (
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
    CONSTRAINT fk_endereco_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    KEY idx_endereco_usuario (usuario_id),
    KEY idx_endereco_principal (usuario_id, principal)
) ENGINE=InnoDB;

-- ============================================================
-- 4. CATEGORIAS
-- ============================================================
CREATE TABLE categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_categoria_nome (nome),
    KEY idx_categoria_ativo_ordem (ativo, ordem)
) ENGINE=InnoDB;

-- ============================================================
-- 5. PRODUTOS
-- ============================================================
CREATE TABLE produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT UNSIGNED NOT NULL,
    nome VARCHAR(140) NOT NULL,
    descricao TEXT DEFAULT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    imagem VARCHAR(255) DEFAULT NULL,
    estoque INT NOT NULL DEFAULT 0,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    promocao TINYINT(1) NOT NULL DEFAULT 0,
    oferta_inicio DATETIME NULL,
    oferta_fim DATETIME NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_produto_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    KEY idx_produto_categoria_ativo (categoria_id, ativo),
    KEY idx_produto_destaque (destaque, ativo),
    KEY idx_produto_promocao (promocao, ativo),
    KEY idx_produto_oferta_periodo (oferta_inicio, oferta_fim, ativo)
) ENGINE=InnoDB;

-- ============================================================
-- 5.1. CONFIGURAÇÃO DE COMBOS
-- Regras de montagem independentes do catálogo de produtos.
-- ============================================================
CREATE TABLE combo_configuracoes (
    produto_id INT UNSIGNED PRIMARY KEY,
    configuracao_json LONGTEXT NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_combo_config_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. TAMANHOS / BARCA / COPO
-- Os limites usados pelo sistema também são persistidos no banco.
-- ============================================================
CREATE TABLE tamanhos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('copo','barca') NOT NULL,
    nome VARCHAR(30) NOT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    volume_ml INT UNSIGNED DEFAULT NULL,
    limite_frutas TINYINT UNSIGNED NOT NULL DEFAULT 1,
    limite_cremes TINYINT UNSIGNED NOT NULL DEFAULT 1,
    limite_adicionais TINYINT UNSIGNED NOT NULL DEFAULT 1,
    limite_caldas TINYINT UNSIGNED NOT NULL DEFAULT 1,
    limite_acompanhamentos TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ordem INT NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_tamanho_tipo_nome (tipo, nome),
    KEY idx_tamanho_ativo_ordem (tipo, ativo, ordem)
) ENGINE=InnoDB;

-- ============================================================
-- 7. OPÇÕES DE PERSONALIZAÇÃO
-- ============================================================
CREATE TABLE opcoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('fruta','creme','adicional','calda','acompanhamento') NOT NULL,
    nome VARCHAR(100) NOT NULL,
    preco_adicional DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    imagem VARCHAR(255) DEFAULT NULL,
    ordem INT NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_opcao_tipo_nome (tipo, nome),
    KEY idx_opcao_tipo_ativo_ordem (tipo, ativo, ordem)
) ENGINE=InnoDB;

CREATE TABLE produto_opcoes (
    produto_id INT UNSIGNED NOT NULL,
    opcao_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (produto_id, opcao_id),
    CONSTRAINT fk_produto_opcao_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_produto_opcao_opcao
        FOREIGN KEY (opcao_id) REFERENCES opcoes(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. PEDIDOS
-- O endereço é armazenado como snapshot para preservar o endereço
-- usado naquela compra mesmo se o cliente o alterar depois.
-- ============================================================
CREATE TABLE pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    endereco_id INT UNSIGNED DEFAULT NULL,
    nome_cliente VARCHAR(120) NOT NULL,
    telefone VARCHAR(25) NOT NULL,
    cep VARCHAR(10) NOT NULL,
    rua VARCHAR(160) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(100) DEFAULT NULL,
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    observacao TEXT DEFAULT NULL,
    observacao_entrega TEXT DEFAULT NULL,
    forma_pagamento ENUM('dinheiro','pix','credito','debito') NOT NULL,
    pagamento_status ENUM('pendente','recebido','cancelado') NOT NULL DEFAULT 'pendente',
    troco_para DECIMAL(10,2) DEFAULT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    taxa_entrega DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('recebido','confirmado','preparando','pronto','saiu_entrega','entregue','cancelado') NOT NULL DEFAULT 'recebido',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pedido_endereco
        FOREIGN KEY (endereco_id) REFERENCES enderecos(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    KEY idx_pedido_usuario_data (usuario_id, criado_em),
    KEY idx_pedido_status_data (status, criado_em),
    KEY idx_pedido_pagamento (pagamento_status)
) ENGINE=InnoDB;

-- ============================================================
-- 9. ITENS DOS PEDIDOS
-- personalizacao_json mantém um retrato da composição daquele item.
-- ============================================================
CREATE TABLE itens_pedido (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED DEFAULT NULL,
    nome_produto VARCHAR(160) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    personalizacao_json JSON DEFAULT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_item_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_item_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    KEY idx_item_pedido (pedido_id),
    KEY idx_item_produto (produto_id)
) ENGINE=InnoDB;

-- ============================================================
-- 10. HISTÓRICO DE STATUS
-- Permite acompanhar como o pedido evoluiu.
-- ============================================================
CREATE TABLE pedido_status_historico (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    status ENUM('recebido','confirmado','preparando','pronto','saiu_entrega','entregue','cancelado') NOT NULL,
    mensagem VARCHAR(255) DEFAULT NULL,
    alterado_por_admin_id INT UNSIGNED DEFAULT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_status_admin
        FOREIGN KEY (alterado_por_admin_id) REFERENCES administradores(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    KEY idx_status_pedido_data (pedido_id, criado_em),
    KEY idx_status_admin (alterado_por_admin_id)
) ENGINE=InnoDB;

-- ============================================================
-- 11. FORMULÁRIO DE CONTATO
-- ============================================================
CREATE TABLE mensagens_contato (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    assunto VARCHAR(160) DEFAULT NULL,
    mensagem TEXT NOT NULL,
    status ENUM('nova','lida','respondida','arquivada') NOT NULL DEFAULT 'nova',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_contato_status_data (status, criado_em),
    KEY idx_contato_email (email)
) ENGINE=InnoDB;

-- ============================================================
-- 12. CONFIGURAÇÕES DA LOJA
-- ============================================================
CREATE TABLE configuracoes (
    chave VARCHAR(80) PRIMARY KEY,
    valor TEXT NOT NULL,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 13. LOG ADMINISTRATIVO
-- Não guarda senha. Registra ações relevantes no painel.
-- ============================================================
CREATE TABLE admin_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    administrador_id INT UNSIGNED DEFAULT NULL,
    acao VARCHAR(80) NOT NULL,
    entidade VARCHAR(80) DEFAULT NULL,
    entidade_id INT UNSIGNED DEFAULT NULL,
    detalhes VARCHAR(500) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_admin
        FOREIGN KEY (administrador_id) REFERENCES administradores(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    KEY idx_log_admin_data (administrador_id, criado_em),
    KEY idx_log_entidade (entidade, entidade_id)
) ENGINE=InnoDB;

-- ============================================================
-- ADMIN ÚNICO DO PROJETO
-- E-mail: admin@acaiflow.local
-- Senha inicial: AF-Admin!9mQ2#Lx7V
-- ATENÇÃO: a senha abaixo NÃO está em texto puro no banco.
-- ============================================================
INSERT INTO administradores (nome, email, senha, ativo)
VALUES (
    'Administrador Açaí Flow',
    'admin@acaiflow.local',
    '$2y$12$7Al902e32NRKHBWa8vMtVu/CHjn5Em7p/N4NeRs3EQ6SzLMzohtBG',
    1
);

-- ============================================================
-- CONTA DE DEMONSTRAÇÃO PARA TESTE DO LOGIN DO CLIENTE
-- E-mail: cliente@acaiflow.local
-- Senha inicial: AcaiFlow@2026
-- A senha abaixo é um hash bcrypt gerado com password_hash().
-- ============================================================
INSERT INTO usuarios (nome, email, telefone, senha, ativo)
VALUES (
    'Cliente Demonstração',
    'cliente@acaiflow.local',
    '(92) 99999-2026',
    '$2y$12$CgAZkPoBx1ctz9AZu9B4cucWDbdflKC4xpUY3YerEm4N6WUsSxvbe',
    1
);

-- ============================================================
-- CATEGORIAS
-- ============================================================
INSERT INTO categorias (nome, ordem, ativo) VALUES
('Açaí no copo', 1, 1),
('Barcas', 2, 1),
('Tigelas', 3, 1),
('Combos', 4, 1),
('Sobremesas', 5, 1),
('Smoothies', 6, 1),
('Bebidas', 7, 1);

-- ============================================================
-- COPOS E BARCAS
-- ============================================================
INSERT INTO tamanhos (tipo,nome,preco,volume_ml,limite_frutas,limite_cremes,limite_adicionais,limite_caldas,limite_acompanhamentos,ordem,ativo) VALUES
('copo','180ml',5.00,180,1,1,1,1,1,1,1),
('copo','200ml',7.00,200,1,1,1,1,2,2,1),
('copo','300ml',10.00,300,1,1,1,1,2,3,1),
('copo','400ml',13.00,400,1,1,1,1,3,4,1),
('copo','500ml',16.00,500,1,1,1,1,3,5,1),
('copo','700ml',20.00,700,1,1,1,2,4,6,1),
('barca','PP',15.00,500,1,1,1,1,3,1,1),
('barca','P',22.00,700,2,1,1,2,4,2,1),
('barca','M',30.00,1000,2,1,1,2,4,3,1),
('barca','G',40.00,1500,3,1,1,3,5,4,1),
('barca','GG',50.00,2000,4,1,1,3,5,5,1),
('barca','XG',70.00,3000,5,1,1,4,6,6,1);

-- ============================================================
-- OPÇÕES
-- ============================================================
INSERT INTO opcoes (tipo,nome,preco_adicional,ordem,ativo) VALUES
('fruta','Morango',0.00,1,1),
('fruta','Banana',0.00,2,1),
('fruta','Kiwi',0.00,3,1),
('fruta','Uva',0.00,4,1),
('fruta','Abacate',0.00,5,1),
('fruta','Abacaxi',0.00,6,1),
('fruta','Manga',0.00,7,1),
('fruta','Cereja',0.00,8,1),
('fruta','Mirtilo',0.00,9,1),
('fruta','Amora',0.00,10,1),
('creme','Cupuaçu',0.00,1,1),
('creme','Maracujá',0.00,2,1),
('creme','Chocolate',0.00,3,1),
('creme','Morango',0.00,4,1),
('creme','Pistache',0.00,5,1),
('adicional','Ovomaltine',2.00,1,1),
('adicional','Nutella',2.00,2,1),
('adicional','Kit-Kat',2.00,3,1),
('adicional','Kinder Ovo',2.00,4,1),
('adicional','Sonho de Valsa',2.00,5,1),
('adicional','Ferrero Rocher',2.00,6,1),
('calda','Leite condensado',0.00,1,1),
('calda','Chocolate',0.00,2,1),
('calda','Morango',0.00,3,1),
('calda','Caramelo',0.00,4,1),
('acompanhamento','Leite em pó',0.00,1,1),
('acompanhamento','Leite Ninho',0.00,2,1),
('acompanhamento','Granola',0.00,3,1),
('acompanhamento','Ovomaltine em pó',0.00,4,1),
('acompanhamento','Paçoca',0.00,5,1),
('acompanhamento','Amendoim',0.00,6,1),
('acompanhamento','Castanha',0.00,7,1),
('acompanhamento','Castanha-do-Pará',0.00,8,1),
('acompanhamento','Farinha láctea',0.00,9,1),
('acompanhamento','Neston',0.00,10,1),
('acompanhamento','Sucrilhos',0.00,11,1),
('acompanhamento','Flocos de arroz',0.00,12,1),
('acompanhamento','Flocos de tapioca',0.00,13,1),
('acompanhamento','Tapioca',0.00,14,1),
('acompanhamento','Chia',0.00,15,1),
('acompanhamento','Linhaça',0.00,16,1),
('acompanhamento','Gergelim',0.00,17,1),
('acompanhamento','Chocoball',0.00,18,1),
('acompanhamento','Chocopower',0.00,19,1),
('acompanhamento','Granulado de chocolate',0.00,20,1),
('acompanhamento','Granulado colorido',0.00,21,1),
('acompanhamento','Confete',0.00,22,1),
('acompanhamento',"M&M's",0.00,23,1),
('acompanhamento','Gotas de chocolate',0.00,24,1),
('acompanhamento','Jujuba',0.00,25,1),
('acompanhamento','Marshmallow',0.00,26,1),
('acompanhamento','Negresco/Oreo',0.00,27,1),
('acompanhamento','Tubetes',0.00,28,1);

-- ============================================================
-- PRODUTOS
-- ============================================================
INSERT INTO produtos (categoria_id,nome,descricao,preco,imagem,estoque,destaque,promocao,ativo) VALUES
(1,'Açaí 180ml','Açaí expresso para uma pausa rápida e saborosa.',5.00,'img/produtos/acai-180ml.jpg',30,0,0,1),
(1,'Açaí 200ml','Tamanho compacto para matar a vontade de açaí.',7.00,'img/produtos/acai-200ml.jpg',30,0,0,1),
(1,'Açaí 300ml','O clássico do Flow para acompanhar seu dia.',10.00,'img/produtos/acai-300ml.jpg',24,1,0,1),
(1,'Açaí 400ml','Mais espaço para sua combinação favorita.',13.00,'img/produtos/acai-400ml.jpg',20,0,0,1),
(1,'Açaí 500ml','Uma porção generosa para personalizar do seu jeito.',16.00,'img/produtos/acai-500ml.jpg',18,1,0,1),
(1,'Açaí 700ml','Para quem quer mergulhar de vez no Flow.',20.00,'img/produtos/acai-700ml.jpg',12,0,1,1),
(2,'Barca PP','Barca compacta para uma combinação especial.',15.00,'img/produtos/barca-pp.jpg',10,0,0,1),
(2,'Barca P','Barca para compartilhar ou caprichar nos acompanhamentos.',22.00,'img/produtos/barca-p.jpg',8,1,0,1),
(2,'Barca M','Uma barca completa para momentos maiores.',30.00,'img/produtos/barca-m.jpg',8,1,0,1),
(2,'Barca G','Grande no tamanho e no sabor.',40.00,'img/produtos/barca-g.jpg',6,0,0,1),
(2,'Barca GG','A experiência Flow para dividir com a turma.',50.00,'img/produtos/barca-gg.jpg',5,0,1,1),
(2,'Barca XG','Nossa maior opção de barca.',70.00,'img/produtos/barca-xg.jpg',3,0,0,1),
(3,'Tigela Frutas Vermelhas','Açaí cremoso com uma seleção frutada.',12.00,'img/produtos/acai-tigela.png',15,1,0,1),
(3,'Tigela Tropical','Açaí, frutas e um toque natural.',14.00,'img/produtos/acai-tigela.png',13,1,0,1),
(3,'Tigela Crocante','Açaí com textura e acompanhamentos crocantes.',15.00,'img/produtos/acai-tigela.png',11,0,0,1),
(4,'Combo Flow','Açaí 500ml + combinação de acompanhamentos.',20.00,'img/produtos/acai-tigela.png',10,1,1,1),
(4,'Combo Casal','Duas porções para compartilhar o momento.',0.00,'img/produtos/acai-tigela.png',8,1,1,1),
(5,'Açaí com Banana e Chocolate','Uma combinação doce e cremosa.',13.00,'img/produtos/acai-tigela.png',9,0,0,1),
(6,'Smoothie de Açaí','Bebida cremosa para refrescar o dia.',15.00,'img/produtos/acai-tigela.png',7,0,0,1),
(7,'Água Mineral','Água para acompanhar seu pedido.',3.00,'img/produtos/acai-copo.png',25,0,0,1);

-- Regras iniciais dos combos personalizáveis
INSERT INTO combo_configuracoes (produto_id,configuracao_json,ativo)
SELECT id,
       CASE
         WHEN nome='Combo Flow' THEN '{"porcoes":[{"nome":"Açaí Flow 500ml","tipos":["copo"],"tamanhos":[5],"limites":{"fruta":1,"creme":1,"adicional":1,"calda":1,"acompanhamento":6}}]}'
         WHEN nome='Combo Casal' THEN '{"porcoes":[{"nome":"Porção 1","tipos":["copo","barca"],"tamanhos":[1,2,3,4,5,6,7,8,9,10,11,12],"limites":{"fruta":2,"creme":1,"adicional":1,"calda":2,"acompanhamento":5}},{"nome":"Porção 2","tipos":["copo","barca"],"tamanhos":[1,2,3,4,5,6,7,8,9,10,11,12],"limites":{"fruta":2,"creme":1,"adicional":1,"calda":2,"acompanhamento":5}}]}'
       END, 1
FROM produtos
WHERE nome IN ('Combo Flow','Combo Casal') AND categoria_id=(SELECT id FROM categorias WHERE nome='Combos' LIMIT 1);

-- ============================================================
-- CONFIGURAÇÕES INICIAIS
-- ============================================================
INSERT INTO configuracoes (chave,valor) VALUES
('pix_chave',''),
('pix_nome_recebedor','Açaí Flow'),
('whatsapp',''),
('instagram','@acaiflow'),
('cidade','Manacapuru - AM'),
('slogan','Seu açaí. Seu flow.'),
('nome_loja','Açaí Flow'),
('taxa_entrega','0.00');

-- Nenhuma senha de cliente é criada por SQL.
-- Clientes devem usar cadastro.php, que chama password_hash().
