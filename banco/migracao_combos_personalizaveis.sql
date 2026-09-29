-- AÇAÍ FLOW — Migração: combos personalizáveis
-- Execute uma vez em bancos existentes. A tabela guarda apenas as regras de montagem.
USE acai_flow;

CREATE TABLE IF NOT EXISTS combo_configuracoes (
    produto_id INT UNSIGNED PRIMARY KEY,
    configuracao_json LONGTEXT NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_combo_config_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Configurações iniciais coerentes com os combos entregues no projeto.
INSERT INTO combo_configuracoes (produto_id, configuracao_json, ativo)
SELECT id,
       CASE
         WHEN nome='Combo Flow' THEN JSON_OBJECT(
           'porcoes', JSON_ARRAY(
             JSON_OBJECT(
               'nome','Açaí Flow 500ml',
               'tipos',JSON_ARRAY('copo'),
               'tamanhos',JSON_ARRAY(5),
               'limites',JSON_OBJECT('fruta',1,'creme',1,'adicional',1,'calda',1,'acompanhamento',6)
             )
           )
         )
         WHEN nome='Combo Casal' THEN JSON_OBJECT(
           'porcoes', JSON_ARRAY(
             JSON_OBJECT('nome','Porção 1','tipos',JSON_ARRAY('copo','barca'),'tamanhos',JSON_ARRAY(1,2,3,4,5,6,7,8,9,10,11,12),'limites',JSON_OBJECT('fruta',2,'creme',1,'adicional',1,'calda',2,'acompanhamento',5)),
             JSON_OBJECT('nome','Porção 2','tipos',JSON_ARRAY('copo','barca'),'tamanhos',JSON_ARRAY(1,2,3,4,5,6,7,8,9,10,11,12),'limites',JSON_OBJECT('fruta',2,'creme',1,'adicional',1,'calda',2,'acompanhamento',5))
           )
         )
       END,
       1
FROM produtos
WHERE nome IN ('Combo Flow','Combo Casal')
  AND categoria_id = (SELECT id FROM categorias WHERE nome='Combos' LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM combo_configuracoes cc WHERE cc.produto_id=produtos.id);
