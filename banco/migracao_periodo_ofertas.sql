-- AÇAÍ FLOW — Migração: período de exibição de combos e promoções
-- Execute uma única vez em um banco já existente.
USE acai_flow;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'produtos' AND COLUMN_NAME = 'oferta_inicio') = 0,
    'ALTER TABLE produtos ADD COLUMN oferta_inicio DATETIME NULL AFTER promocao',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'produtos' AND COLUMN_NAME = 'oferta_fim') = 0,
    'ALTER TABLE produtos ADD COLUMN oferta_fim DATETIME NULL AFTER oferta_inicio',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'produtos' AND INDEX_NAME = 'idx_produto_oferta_periodo') = 0,
    'ALTER TABLE produtos ADD INDEX idx_produto_oferta_periodo (oferta_inicio, oferta_fim, ativo)',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
