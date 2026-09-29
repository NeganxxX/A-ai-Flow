-- AÇAÍ FLOW — Migração de imagens dos produtos
-- Execute uma vez no banco acai_flow já existente.
USE acai_flow;

UPDATE produtos SET imagem='img/produtos/acai-180ml.jpg' WHERE LOWER(TRIM(nome))='açaí 180ml';
UPDATE produtos SET imagem='img/produtos/acai-200ml.jpg' WHERE LOWER(TRIM(nome))='açaí 200ml';
UPDATE produtos SET imagem='img/produtos/acai-300ml.jpg' WHERE LOWER(TRIM(nome))='açaí 300ml';
UPDATE produtos SET imagem='img/produtos/acai-400ml.jpg' WHERE LOWER(TRIM(nome))='açaí 400ml';
UPDATE produtos SET imagem='img/produtos/acai-500ml.jpg' WHERE LOWER(TRIM(nome))='açaí 500ml';
UPDATE produtos SET imagem='img/produtos/acai-700ml.jpg' WHERE LOWER(TRIM(nome))='açaí 700ml';

UPDATE produtos SET imagem='img/produtos/barca-pp.jpg' WHERE LOWER(TRIM(nome))='barca pp';
UPDATE produtos SET imagem='img/produtos/barca-p.jpg' WHERE LOWER(TRIM(nome))='barca p';
UPDATE produtos SET imagem='img/produtos/barca-m.jpg' WHERE LOWER(TRIM(nome))='barca m';
UPDATE produtos SET imagem='img/produtos/barca-g.jpg' WHERE LOWER(TRIM(nome))='barca g';
UPDATE produtos SET imagem='img/produtos/barca-gg.jpg' WHERE LOWER(TRIM(nome))='barca gg';
UPDATE produtos SET imagem='img/produtos/barca-xg.jpg' WHERE LOWER(TRIM(nome))='barca xg';
