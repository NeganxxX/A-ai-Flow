-- AÇAÍ FLOW — Remove o preço fixo armazenado do Combo Casal.
-- O total passa a ser calculado pela soma das duas escolhas de copo/barca
-- e dos adicionais pagos. Execute uma única vez em bases existentes.
USE acai_flow;
UPDATE produtos SET preco = 0.00 WHERE LOWER(TRIM(nome)) = 'combo casal';
