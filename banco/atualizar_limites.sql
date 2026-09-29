/*
  Açaí Flow — atualização das regras de personalização
  Use este arquivo em um banco que já foi criado com uma versão anterior do projeto.
  Execute apenas uma vez.
*/

SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE tamanhos
  ADD COLUMN IF NOT EXISTS volume_ml INT UNSIGNED NULL AFTER preco,
  ADD COLUMN IF NOT EXISTS limite_frutas TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER volume_ml,
  ADD COLUMN IF NOT EXISTS limite_cremes TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER limite_frutas,
  ADD COLUMN IF NOT EXISTS limite_adicionais TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER limite_cremes,
  ADD COLUMN IF NOT EXISTS limite_caldas TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER limite_adicionais,
  ADD COLUMN IF NOT EXISTS limite_acompanhamentos TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER limite_caldas;

UPDATE tamanhos SET volume_ml=180, limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=1 WHERE tipo='copo' AND nome='180ml';
UPDATE tamanhos SET volume_ml=200, limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=2 WHERE tipo='copo' AND nome='200ml';
UPDATE tamanhos SET volume_ml=300, limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=2 WHERE tipo='copo' AND nome='300ml';
UPDATE tamanhos SET volume_ml=400, limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=3 WHERE tipo='copo' AND nome='400ml';
UPDATE tamanhos SET volume_ml=500, limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=3 WHERE tipo='copo' AND nome='500ml';
UPDATE tamanhos SET volume_ml=700, limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=2, limite_acompanhamentos=4 WHERE tipo='copo' AND nome='700ml';

UPDATE tamanhos SET volume_ml=500, limite_frutas=1, limite_cremes=1, limite_adicionais=1, limite_caldas=1, limite_acompanhamentos=3 WHERE tipo='barca' AND nome='PP';
UPDATE tamanhos SET volume_ml=700, limite_frutas=2, limite_cremes=1, limite_adicionais=1, limite_caldas=2, limite_acompanhamentos=4 WHERE tipo='barca' AND nome='P';
UPDATE tamanhos SET volume_ml=1000, limite_frutas=2, limite_cremes=1, limite_adicionais=1, limite_caldas=2, limite_acompanhamentos=4 WHERE tipo='barca' AND nome='M';
UPDATE tamanhos SET volume_ml=1500, limite_frutas=3, limite_cremes=1, limite_adicionais=1, limite_caldas=3, limite_acompanhamentos=5 WHERE tipo='barca' AND nome='G';
UPDATE tamanhos SET volume_ml=2000, limite_frutas=4, limite_cremes=1, limite_adicionais=1, limite_caldas=3, limite_acompanhamentos=5 WHERE tipo='barca' AND nome='GG';
UPDATE tamanhos SET volume_ml=3000, limite_frutas=5, limite_cremes=1, limite_adicionais=1, limite_caldas=4, limite_acompanhamentos=6 WHERE tipo='barca' AND nome='XG';

UPDATE opcoes SET preco_adicional=2.00 WHERE tipo='adicional';

SET FOREIGN_KEY_CHECKS=1;
