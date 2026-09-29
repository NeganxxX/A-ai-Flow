-- Açaí Flow — consultas rápidas de conferência após importar banco.sql
USE acai_flow;

SELECT 'administradores' AS tabela, COUNT(*) AS registros FROM administradores
UNION ALL SELECT 'usuarios', COUNT(*) FROM usuarios
UNION ALL SELECT 'categorias', COUNT(*) FROM categorias
UNION ALL SELECT 'produtos', COUNT(*) FROM produtos
UNION ALL SELECT 'tamanhos', COUNT(*) FROM tamanhos
UNION ALL SELECT 'opcoes', COUNT(*) FROM opcoes
UNION ALL SELECT 'configuracoes', COUNT(*) FROM configuracoes
UNION ALL SELECT 'mensagens_contato', COUNT(*) FROM mensagens_contato;

SELECT id,nome,email,ativo FROM administradores;

SELECT tipo,nome,preco,volume_ml,limite_frutas,limite_cremes,limite_adicionais,limite_caldas,limite_acompanhamentos
FROM tamanhos
ORDER BY tipo, ordem;

SELECT tipo,nome,preco_adicional,ativo
FROM opcoes
WHERE tipo IN ('fruta','creme','adicional','calda','acompanhamento')
ORDER BY tipo,ordem;
