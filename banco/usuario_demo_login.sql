-- ============================================================
-- AÇAÍ FLOW — CONTA DE DEMONSTRAÇÃO DO LOGIN DO CLIENTE
-- Execute no banco acai_flow caso ele já esteja instalado.
-- ============================================================
USE acai_flow;

INSERT INTO usuarios (nome, email, telefone, senha, ativo)
SELECT
    'Cliente Demonstração',
    'cliente@acaiflow.local',
    '(92) 99999-2026',
    '$2y$12$CgAZkPoBx1ctz9AZu9B4cucWDbdflKC4xpUY3YerEm4N6WUsSxvbe',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'cliente@acaiflow.local'
);

-- Credenciais de teste
-- E-mail: cliente@acaiflow.local
-- Senha:  AcaiFlow@2026
