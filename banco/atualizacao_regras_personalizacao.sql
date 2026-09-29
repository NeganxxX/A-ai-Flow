-- Açaí Flow — atualização para bases já existentes.
-- Executar depois de banco.sql caso o banco acai_flow já tenha sido importado.
USE acai_flow;

UPDATE opcoes
SET preco_adicional = 2.00
WHERE tipo = 'adicional';

UPDATE produtos
SET imagem = CASE
    WHEN categoria_id = 1 THEN 'img/produtos/acai-copo-real.jpg'
    WHEN categoria_id = 2 THEN 'img/produtos/acai-barca-real.jpg'
    WHEN categoria_id = 3 THEN 'img/produtos/acai-tigela-real.jpg'
    ELSE 'img/produtos/acai-tigela-real.jpg'
END
WHERE ativo = 1;
