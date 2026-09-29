-- ============================================================
-- OPCIONAL: USUÁRIO MYSQL COM PRIVILÉGIOS LIMITADOS
-- Recomendado para ambientes que não sejam apenas testes.
-- Execute como root no MySQL/MariaDB.
-- ============================================================

CREATE USER IF NOT EXISTS 'acai_flow_app'@'localhost'
IDENTIFIED BY 'Acaiflow#DB9!2026';

GRANT SELECT, INSERT, UPDATE, DELETE
ON acai_flow.*
TO 'acai_flow_app'@'localhost';

FLUSH PRIVILEGES;

-- Depois ajuste config/app.php para:
-- DB_USER = acai_flow_app
-- DB_PASS = Acaiflow#DB9!2026
--
-- Em hospedagem real, use uma senha própria e mantenha credenciais
-- fora de arquivos públicos/versionados.
