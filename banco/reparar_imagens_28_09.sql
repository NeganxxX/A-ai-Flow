-- AÇAÍ FLOW — Reposição de imagens ausentes
-- Execute após copiar a versão atualizada do projeto para o XAMPP.
USE acai_flow;

UPDATE produtos SET imagem='img/produtos/acai-tigela.png'
WHERE LOWER(TRIM(nome)) IN ('tigela frutas vermelhas','tigela tropical','tigela crocante','combo flow','combo casal','açaí com banana e chocolate','smoothie de açaí');

UPDATE produtos SET imagem='img/produtos/acai-copo.png'
WHERE LOWER(TRIM(nome))='água mineral';
