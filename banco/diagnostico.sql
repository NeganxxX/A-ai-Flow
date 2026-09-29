-- Açaí Flow — diagnóstico da base
USE acai_flow;

SELECT DATABASE() AS banco_atual, VERSION() AS servidor;

SELECT 'administradores' AS tabela, COUNT(*) AS registros FROM administradores
UNION ALL SELECT 'usuarios', COUNT(*) FROM usuarios
UNION ALL SELECT 'categorias', COUNT(*) FROM categorias
UNION ALL SELECT 'produtos', COUNT(*) FROM produtos
UNION ALL SELECT 'tamanhos', COUNT(*) FROM tamanhos
UNION ALL SELECT 'opcoes', COUNT(*) FROM opcoes
UNION ALL SELECT 'pedidos', COUNT(*) FROM pedidos
UNION ALL SELECT 'itens_pedido', COUNT(*) FROM itens_pedido
UNION ALL SELECT 'mensagens_contato', COUNT(*) FROM mensagens_contato;

SELECT id,tipo,nome,preco,volume_ml,limite_frutas,limite_cremes,limite_adicionais,limite_caldas,limite_acompanhamentos
FROM tamanhos ORDER BY tipo,ordem,id;

SELECT tipo,COUNT(*) AS quantidade,MIN(preco_adicional) AS menor_preco,MAX(preco_adicional) AS maior_preco
FROM opcoes GROUP BY tipo ORDER BY tipo;

SELECT id,nome,email,ativo FROM administradores;

SELECT id,nome,preco,estoque,imagem,ativo FROM produtos ORDER BY id;

SELECT id,nome,preco_adicional,ativo FROM opcoes WHERE tipo='adicional' AND (preco_adicional<>2.00 OR ativo<>1);

SELECT tipo,nome FROM tamanhos WHERE ativo=1 AND NOT (
  (tipo='copo' AND nome IN ('180ml','200ml','300ml','400ml','500ml','700ml')) OR
  (tipo='barca' AND nome IN ('PP','P','M','G','GG','XG'))
);
