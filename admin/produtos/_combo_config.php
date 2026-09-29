<?php
require_once __DIR__.'/../../config/catalogo.php';

$comboConfigAdmin = $comboConfigAdmin ?? null;
$comboDefault = $comboConfigAdmin ?: [
    'porcoes' => [[
        'nome' => 'Porção 1',
        'tipos' => ['copo','barca'],
        'tamanhos' => range(1,12),
        'limites' => ['fruta'=>1,'creme'=>1,'adicional'=>1,'calda'=>1,'acompanhamento'=>3]
    ]]
];
$adminComboSizes = $adminComboSizes ?? [];
?>
<div class="admin-combo-config" id="comboConfigPanel">
    <div class="admin-offer-settings-head">
        <div>
            <h2>Montagem do combo</h2>
            <p>Defina quantas porções o cliente monta, quais formatos/tamanhos pode escolher e os limites de complementos de cada porção.</p>
        </div>
        <label class="checkbox-row"><input type="checkbox" name="combo_config_ativo" value="1" id="comboConfigEnabled" <?=!empty($comboConfigAdmin)?'checked':''?>> Combo personalizável</label>
    </div>
    <div id="comboConfigBody">
        <div class="form-group combo-slots-count-wrap">
            <label>Quantidade de porções</label>
            <select class="form-control" id="comboSlotsCount" name="combo_slots_count">
                <?php $slotCount=count($comboDefault['porcoes'] ?? []); if($slotCount<1)$slotCount=1; if($slotCount>3)$slotCount=3; for($n=1;$n<=3;$n++): ?><option value="<?=$n?>" <?=$slotCount===$n?'selected':''?>><?=$n?></option><?php endfor; ?>
            </select>
        </div>
        <div class="combo-slot-editor-list">
        <?php for($slotIndex=0;$slotIndex<3;$slotIndex++):
            $slot=$comboDefault['porcoes'][$slotIndex] ?? ['nome'=>'Porção '.($slotIndex+1),'tipos'=>['copo','barca'],'tamanhos'=>range(1,12),'limites'=>['fruta'=>1,'creme'=>1,'adicional'=>1,'calda'=>1,'acompanhamento'=>3]];
        ?>
            <fieldset class="combo-slot-editor" data-slot-editor="<?=$slotIndex?>">
                <legend>Porção <?=$slotIndex+1?></legend>
                <div class="form-grid">
                    <div class="form-group full"><label>Nome da porção</label><input class="form-control" name="combo_slot[<?=$slotIndex?>][nome]" value="<?=e((string)$slot['nome'])?>" maxlength="80"></div>
                    <div class="form-group full"><label>Formatos permitidos</label><div class="combo-check-grid combo-type-grid">
                        <label class="checkbox-row"><input type="checkbox" name="combo_slot[<?=$slotIndex?>][tipos][]" value="copo" <?=in_array('copo',$slot['tipos']??[],true)?'checked':''?>> Copo</label>
                        <label class="checkbox-row"><input type="checkbox" name="combo_slot[<?=$slotIndex?>][tipos][]" value="barca" <?=in_array('barca',$slot['tipos']??[],true)?'checked':''?>> Barca</label>
                    </div></div>
                    <div class="form-group full"><label>Tamanhos disponíveis</label><div class="combo-check-grid combo-size-check-grid">
                        <?php foreach($adminComboSizes as $sz): $kind=$sz['tipo']==='barca'?'Barca '.$sz['nome']:$sz['nome']; $value=(int)$sz['id']; ?>
                            <label class="checkbox-row"><input type="checkbox" name="combo_slot[<?=$slotIndex?>][tamanhos][]" value="<?=$value?>" data-kind="<?=e($sz['tipo'])?>" <?=in_array($value,array_map('intval',$slot['tamanhos']??[]),true)?'checked':''?>> <?=e($kind)?></label>
                        <?php endforeach; ?>
                    </div></div>
                    <div class="form-group"><label>Frutas</label><input class="form-control" type="number" min="0" max="10" name="combo_slot[<?=$slotIndex?>][limites][fruta]" value="<?=e((string)($slot['limites']['fruta']??1))?>"></div>
                    <div class="form-group"><label>Cremes</label><input class="form-control" type="number" min="0" max="10" name="combo_slot[<?=$slotIndex?>][limites][creme]" value="<?=e((string)($slot['limites']['creme']??1))?>"></div>
                    <div class="form-group"><label>Adicionais</label><input class="form-control" type="number" min="0" max="10" name="combo_slot[<?=$slotIndex?>][limites][adicional]" value="<?=e((string)($slot['limites']['adicional']??1))?>"></div>
                    <div class="form-group"><label>Caldas</label><input class="form-control" type="number" min="0" max="10" name="combo_slot[<?=$slotIndex?>][limites][calda]" value="<?=e((string)($slot['limites']['calda']??1))?>"></div>
                    <div class="form-group"><label>Acompanhamentos</label><input class="form-control" type="number" min="0" max="20" name="combo_slot[<?=$slotIndex?>][limites][acompanhamento]" value="<?=e((string)($slot['limites']['acompanhamento']??3))?>"></div>
                </div>
            </fieldset>
        <?php endfor; ?>
        </div>
        <p class="form-help">Exemplo: o <strong>Combo Casal</strong> pode ter duas porções com copo ou barca. O <strong>Combo Flow</strong> pode ter apenas o copo de 500ml e mais acompanhamentos. Essas regras ficam editáveis aqui.</p>
    </div>
</div>
<script>
(() => {
  const panel=document.getElementById('comboConfigPanel');
  if(!panel) return;
  const enabled=document.getElementById('comboConfigEnabled');
  const body=document.getElementById('comboConfigBody');
  const count=document.getElementById('comboSlotsCount');
  const slots=[...panel.querySelectorAll('[data-slot-editor]')];
  const category=panel.closest('form')?.querySelector('[data-category-select]');
  const isComboCategory=()=>String(category?.selectedOptions?.[0]?.textContent||'').trim().toLowerCase()==='combos';
  const refresh=()=>{
    const categoryIsCombo=isComboCategory();
    if(categoryIsCombo && enabled && !enabled.checked) enabled.checked=true;
    const active=categoryIsCombo && !!enabled?.checked;
    panel.hidden=!categoryIsCombo;
    body?.classList.toggle('is-disabled',!active);
    slots.forEach((slot,i)=>{slot.hidden=!active || i>=Number(count?.value||1);});
    panel.classList.toggle('is-disabled',!active);
  };
  enabled?.addEventListener('change',refresh); count?.addEventListener('change',refresh); category?.addEventListener('change',()=>{ if(isComboCategory()&&!enabled.checked) enabled.checked=true; refresh(); }); refresh();
})();
</script>
