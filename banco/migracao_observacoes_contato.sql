-- Açaí Flow — migração para observação de entrega
-- Execute uma vez no banco acai_flow já existente.

SET @col_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'pedidos'
    AND COLUMN_NAME = 'observacao_entrega'
);

SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE pedidos ADD COLUMN observacao_entrega TEXT NULL AFTER observacao',
  'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
