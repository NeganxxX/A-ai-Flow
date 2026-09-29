-- Açaí Flow — remoção de CPF da conta do cliente
-- Use somente em bancos que ainda possuem a coluna usuarios.cpf.

UPDATE usuarios SET cpf = NULL;

ALTER TABLE usuarios
    DROP INDEX uq_usuario_cpf,
    DROP COLUMN cpf;
