(() => {
  'use strict';

  const form = document.getElementById('customBuilder');
  if (!form) return;

  const mode = form.dataset.mode || 'normal';

  const money = value => 'R$ ' + Number(value || 0).toFixed(2).replace('.', ',');
  const baseUrl = window.ACAI_BASE_URL || '';

  function bindQuantity(render) {
    form.querySelectorAll('input[name="quantity"]').forEach(input => input.addEventListener('input', render));
    document.querySelector('[data-qty-minus]')?.addEventListener('click', () => {
      const input = document.getElementById('builderQuantity');
      input.value = Math.max(1, Number(input.value || 1) - 1);
      render();
    });
    document.querySelector('[data-qty-plus]')?.addEventListener('click', () => {
      const input = document.getElementById('builderQuantity');
      input.value = Math.min(20, Number(input.value || 1) + 1);
      render();
    });
  }

  function initComboBuilder() {
    const totalEl = document.getElementById('builderTotal');
    const summaryEl = document.getElementById('builderSummary');
    const cartButton = document.getElementById('addCustomToCart');
    const noticeEl = document.getElementById('builderNotice');
    const comboId = Number(form.dataset.comboId || 0);
    const comboBasePrice = Number(form.dataset.comboPrice || 0);
    const dynamicComboPricing = form.dataset.comboPricing === 'dynamic';
    let comboConfig = {};
    try { comboConfig = JSON.parse(form.dataset.comboConfig || '{}'); } catch (_) { comboConfig = {}; }

    const TYPES = ['fruta', 'creme', 'adicional', 'calda', 'acompanhamento'];
    const labels = { fruta: 'fruta', creme: 'creme', adicional: 'adicional', calda: 'calda', acompanhamento: 'acompanhamento' };
    const slots = Array.from(form.querySelectorAll('[data-combo-slot]'));

    function notice(message = '') {
      if (noticeEl) noticeEl.textContent = message;
    }

    function selectedSize(slotEl) {
      return slotEl.querySelector('input[type="radio"][name$="_size"]:checked');
    }

    function slotRule(slotEl, type) {
      const index = Number(slotEl.dataset.comboSlot || 0);
      const limits = comboConfig?.porcoes?.[index]?.limites || {};
      return Number(limits[type] ?? 0);
    }

    function readable(type, limit) {
      if (type === 'creme') return '1 creme';
      if (type === 'adicional') return '1 adicional opcional';
      if (limit === 1) return `1 ${labels[type]}`;
      return `${limit} ${labels[type]}s`;
    }

    function refreshSlot(slotEl) {
      const size = selectedSize(slotEl);
      const status = slotEl.querySelector('[data-slot-status]');
      const rulesEl = slotEl.querySelector('[data-slot-rules]');

      if (!size) {
        if (status) status.textContent = 'Escolha o formato e o tamanho';
        if (rulesEl) rulesEl.textContent = 'Selecione um copo ou barca para liberar a personalização.';
        slotEl.querySelectorAll('input[type="checkbox"]').forEach(input => {
          input.disabled = true;
          input.closest('.option-card')?.classList.add('is-disabled');
        });
        slotEl.querySelectorAll('[data-slot-limit-text]').forEach(el => { el.textContent = 'Escolha o tamanho primeiro.'; });
        return;
      }

      const displayName = `${size.dataset.kind === 'barca' ? 'Barca ' : ''}${size.dataset.label}`;
      if (status) status.textContent = displayName;
      if (rulesEl) {
        const extra = slotRule(slotEl, 'acompanhamento');
        rulesEl.innerHTML = `<strong>${displayName}</strong> • até ${readable('fruta', slotRule(slotEl,'fruta'))}, ${readable('calda', slotRule(slotEl,'calda'))} e ${readable('acompanhamento', extra)}. 1 creme e 1 adicional são opcionais.`;
      }

      TYPES.forEach(type => {
        const limit = slotRule(slotEl, type);
        const inputs = Array.from(slotEl.querySelectorAll(`input[type="checkbox"][data-type="${type}"]`));
        let selected = inputs.filter(input => input.checked);
        if (selected.length > limit) {
          selected.slice(limit).forEach(input => { input.checked = false; });
          selected = inputs.filter(input => input.checked);
          notice(`O limite de ${readable(type, limit)} foi aplicado na ${displayName}.`);
        }
        const reached = selected.length >= limit && limit > 0;
        inputs.forEach(input => {
          const card = input.closest('.option-card');
          const locked = limit === 0 || (!input.checked && reached);
          input.disabled = locked;
          card?.classList.toggle('is-disabled', locked);
          card?.classList.toggle('limit-reached', reached);
          card?.setAttribute('aria-disabled', locked ? 'true' : 'false');
        });
        const text = slotEl.querySelector(`[data-slot-limit-text="${type}"]`);
        if (text) text.innerHTML = `Selecionados: <strong>${selected.length}/${limit}</strong> • Limite: ${readable(type, limit)}.`;
      });
    }

    function state() {
      const portions = slots.map((slotEl, index) => {
        const size = selectedSize(slotEl);
        const options = Array.from(slotEl.querySelectorAll('input[type="checkbox"]:checked'));
        const extra = options.reduce((sum, input) => sum + Number(input.dataset.price || 0), 0);
        const sizeBase = dynamicComboPricing && size ? Number(size.dataset.price || 0) : 0;
        const optionData = options.map(input => ({
          id: Number(input.value),
          nome: input.dataset.label,
          preco: Number(input.dataset.price || 0),
          tipo: input.dataset.type || 'outros'
        }));
        return { index, slotEl, size, options, optionData, extra, sizeBase };
      });
      const quantity = Math.max(1, Math.min(20, Number(form.querySelector('input[name="quantity"]')?.value || 1)));
      const extras = portions.reduce((sum, row) => sum + row.extra, 0);
      const selectedBase = portions.reduce((sum, row) => sum + row.sizeBase, 0);
      const complete = portions.length > 0 && portions.every(row => !!row.size);
      const base = dynamicComboPricing ? selectedBase : comboBasePrice;
      return { portions, quantity, extras, selectedBase, complete, total: (base + extras) * quantity };
    }

    function render() {
      const current = state();
      totalEl.textContent = money(current.total);
      const lines = current.portions.map(row => {
        const title = row.size ? `${row.size.dataset.kind === 'barca' ? 'Barca ' : ''}${row.size.dataset.label}` : 'Escolha a porção';
        const opts = row.optionData.map(item => item.preco > 0 ? `${item.nome} (+${money(item.preco)})` : item.nome);
        return `<span><strong>Porção ${row.index + 1}: ${title}</strong>${opts.length ? `<small>${opts.join(' • ')}</small>` : ''}</span>`;
      });
      summaryEl.innerHTML = current.complete ? lines.join('') : `<strong>Escolha todas as porções</strong>${lines.length ? lines.join('') : '<span>Nenhuma porção configurada</span>'}`;
      cartButton.disabled = !current.complete;
    }

    function validate() {
      for (const slotEl of slots) {
        const size = selectedSize(slotEl);
        if (!size) return 'Escolha o tamanho de cada porção antes de adicionar o combo.';
        for (const type of TYPES) {
          const limit = slotRule(slotEl, type);
          const count = slotEl.querySelectorAll(`input[type="checkbox"][data-type="${type}"]:checked`).length;
          if (count > limit) return `Revise a porção ${Number(slotEl.dataset.comboSlot) + 1}: o limite de ${readable(type, limit)} foi ultrapassado.`;
        }
      }
      return '';
    }

    slots.forEach(slotEl => {
      slotEl.addEventListener('change', event => {
        if (event.target.matches('input[type="radio"]')) notice('');
        refreshSlot(slotEl);
        render();
      });
    });

    bindQuantity(render);
    slots.forEach(refreshSlot);
    render();

    cartButton.addEventListener('click', async () => {
      const error = validate();
      if (error) { notice(error); return; }
      const current = state();
      const note = form.querySelector('textarea[name="observacao"]')?.value.trim() || '';
      const portions = current.portions.map(row => ({
        nome: row.size.dataset.kind === 'barca' ? `Barca ${row.size.dataset.label}` : row.size.dataset.label,
        tamanho: row.size.dataset.label,
        tamanho_id: Number(row.size.value),
        tipo: row.size.dataset.kind || 'copo',
        volume_ml: Number(row.size.dataset.volume || 0) || null,
        opcoes: row.optionData,
        limites: comboConfig?.porcoes?.[row.index]?.limites || {}
      }));
      const allOptions = portions.flatMap(portion => portion.opcoes);
      const item = {
        signature: `combo-${comboId}-${Date.now()}-${Math.random().toString(36).slice(2,9)}`,
        product_id: comboId,
        size_id: null,
        size_type: 'combo',
        name: form.closest('.builder')?.querySelector('.builder-combo-intro h3')?.textContent?.trim() || 'Combo Açaí Flow',
        price: (dynamicComboPricing ? current.selectedBase : comboBasePrice) + allOptions.reduce((sum, option) => sum + Number(option.preco || 0), 0),
        quantity: current.quantity,
        image: form.querySelector('.summary-thumb img')?.getAttribute('src') || '',
        personalization: {
          combo: true,
          combo_id: comboId,
          porcoes: portions,
          observacao: note
        }
      };
      const cartApi = window.AcaiFlowCart;
      if (!cartApi || typeof cartApi.add !== 'function') { notice('Não foi possível carregar o carrinho. Atualize a página e tente novamente.'); return; }
      cartButton.disabled = true;
      const original = cartButton.textContent;
      cartButton.textContent = 'ADICIONANDO...';
      try {
        const saved = typeof cartApi.addAndSync === 'function' ? await cartApi.addAndSync(item) : cartApi.add(item);
        if (!saved) { cartButton.disabled = false; cartButton.textContent = original; notice(window.ACAI_CART_SYNC?.lastError || 'Não foi possível salvar o combo no carrinho.'); return; }
        window.location.href = baseUrl + '/pedido.php';
      } catch (_) {
        cartButton.disabled = false;
        cartButton.textContent = original;
        notice('Não foi possível adicionar o combo ao carrinho. Tente novamente.');
      }
    });
  }

  if (mode === 'combo') {
    initComboBuilder();
    return;
  }

  const totalEl = document.getElementById('builderTotal');
  const summaryEl = document.getElementById('builderSummary');
  const cartButton = document.getElementById('addCustomToCart');
  const rulesEl = document.getElementById('builderRules');
  const noticeEl = document.getElementById('builderNotice');
  const rules = JSON.parse(form.dataset.rules || '{}');

  const GROUP_NAMES = { fruta: 'fruta', creme: 'creme', adicional: 'adicional', calda: 'calda', acompanhamento: 'acompanhamento' };
  const TYPES = ['fruta', 'creme', 'adicional', 'calda', 'acompanhamento'];
  const sizeInput = () => form.querySelector('input[name="size"]:checked');
  const optionInputs = () => Array.from(form.querySelectorAll('input[name="options[]"]'));
  const checkedByType = type => optionInputs().filter(input => input.dataset.type === type && input.checked);
  const selectedOptions = () => optionInputs().filter(input => input.checked);
  function currentRule() { const size = sizeInput(); return size ? rules[String(size.value)] || null : null; }
  function setNotice(message = '') { if (noticeEl) noticeEl.textContent = message; }
  function readableRule(type, limit) {
    if (type === 'fruta') return limit === 1 ? '1 fruta' : `${limit} frutas`;
    if (type === 'creme') return '1 creme';
    if (type === 'adicional') return '1 adicional opcional';
    if (type === 'calda') return limit === 1 ? '1 calda' : `${limit} caldas`;
    return limit === 1 ? '1 acompanhamento' : `${limit} acompanhamentos`;
  }
  function capGroup(type, limit) {
    const inputs = optionInputs().filter(input => input.dataset.type === type);
    const safeLimit = Math.max(0, Number(limit) || 0);
    let selected = inputs.filter(input => input.checked);
    if (selected.length > safeLimit) {
      selected.slice(safeLimit).forEach(input => { input.checked = false; });
      selected = inputs.filter(input => input.checked);
      setNotice(`O tamanho selecionado permite no máximo ${readableRule(type, safeLimit)}. As opções excedentes foram removidas.`);
    }
    const reached = selected.length >= safeLimit && safeLimit > 0;
    inputs.forEach(input => {
      const card = input.closest('.option-card');
      const locked = safeLimit === 0 || (!input.checked && reached);
      input.disabled = locked;
      card?.classList.toggle('is-disabled', locked);
      card?.classList.toggle('limit-reached', reached);
      card?.setAttribute('aria-disabled', locked ? 'true' : 'false');
    });
    const limitText = form.querySelector(`[data-limit-text="${type}"]`);
    if (limitText) {
      const current = selected.length;
      const extra = type === 'adicional' ? ' Cada adicional custa R$ 2,00.' : '';
      const optional = type === 'creme' || type === 'adicional' ? ' Opcional.' : '';
      limitText.innerHTML = `Selecionados: <strong>${current}/${safeLimit}</strong> • Limite: ${readableRule(type, safeLimit)}.${optional}${extra}`;
    }
  }
  function updateRules({ announce = false } = {}) {
    const rule = currentRule();
    if (!rule) { rulesEl.classList.remove('visible'); rulesEl.innerHTML = ''; TYPES.forEach(type => capGroup(type, 0)); cartButton.disabled = true; return; }
    const isBarca = rule.kind === 'barca'; const title = `${isBarca ? 'Barca ' : ''}${rule.name}`; const volume = isBarca && rule.volume ? ` • ${rule.volume} ml de açaí` : '';
    rulesEl.innerHTML = `<strong>${title}${volume}</strong><br>Escolha até ${readableRule('acompanhamento', Number(rule.acompanhamento))}, ${readableRule('calda', Number(rule.calda))} e ${readableRule('fruta', Number(rule.fruta))}. 1 adicional é opcional e custa R$ 2,00; 1 creme é opcional.`;
    rulesEl.classList.add('visible'); TYPES.forEach(type => capGroup(type, Number(rule[type] ?? 0))); if (announce) setNotice(`Limites aplicados para ${title}. Opções extras ficam bloqueadas automaticamente.`);
  }
  function read() {
    const size = sizeInput(); const options = selectedOptions(); const basePrice = size ? Number(size.dataset.price || 0) : 0; const extras = options.reduce((sum, input) => sum + Number(input.dataset.price || 0), 0); const quantityInput = form.querySelector('input[name="quantity"]'); const quantity = Math.max(1, Math.min(20, Number(quantityInput?.value || 1))); return { size, options, basePrice, extras, quantity, total: (basePrice + extras) * quantity };
  }
  function render() {
    const state = read(); totalEl.textContent = money(state.total); const sizeText = state.size ? `${state.size.dataset.kind === 'barca' ? 'Barca ' : ''}${state.size.dataset.label}` : 'Escolha um tamanho'; const optionNames = state.options.map(input => { const price = Number(input.dataset.price || 0); return price > 0 ? `${input.dataset.label} (+${money(price)})` : input.dataset.label; }); summaryEl.innerHTML = `<strong>${sizeText}</strong>${optionNames.length ? `<span>${optionNames.join(' • ')}</span>` : '<span>Nenhuma opção escolhida</span>'}`; cartButton.disabled = !state.size;
  }
  function enforceLimitFromChange(input) {
    if (!input.matches('input[name="options[]"]') || !input.checked) return; const rule = currentRule(); if (!rule) { input.checked = false; return; } const type = input.dataset.type; const limit = Number(rule[type] ?? 0); const selected = checkedByType(type); if (selected.length > limit) { input.checked = false; setNotice(`Limite atingido: ${readableRule(type, limit)}. As demais opções permanecem bloqueadas.`); updateRules(); render(); }
  }
  form.addEventListener('change', event => { const target = event.target; if (target.matches('input[name="size"]')) { setNotice(''); updateRules({ announce: true }); render(); return; } if (target.matches('input[name="options[]"]')) { enforceLimitFromChange(target); updateRules(); render(); } });
  bindQuantity(render);
  const query = new URLSearchParams(window.location.search); const requestedSize = query.get('tamanho'); if (requestedSize) { const requested = form.querySelector(`input[name="size"][value="${CSS.escape(requestedSize)}"]`); if (requested) requested.checked = true; }
  updateRules(); render();
  cartButton.addEventListener('click', async () => {
    const state = read(); const rule = currentRule(); if (!state.size || !rule) return; const invalidType = TYPES.find(type => checkedByType(type).length > Number(rule[type] || 0)); if (invalidType) { setNotice(`Revise ${GROUP_NAMES[invalidType]}: o limite do tamanho selecionado foi ultrapassado.`); updateRules(); render(); return; }
    const note = form.querySelector('textarea[name="observacao"]')?.value.trim() || ''; const categoryMap = {}; state.options.forEach(input => { const group = input.dataset.type || 'outros'; if (!categoryMap[group]) categoryMap[group] = []; categoryMap[group].push(input.dataset.label); });
    const item = { signature: 'custom-' + Date.now() + '-' + Math.random().toString(36).slice(2, 9), product_id: null, size_id: Number(state.size.value), size_type: state.size.dataset.kind || 'copo', name: 'Monte seu Açaí — ' + (state.size.dataset.kind === 'barca' ? 'Barca ' : '') + state.size.dataset.label, price: state.basePrice + state.extras, quantity: state.quantity, image: state.size.dataset.kind === 'barca' ? 'img/produtos/acai-barca.png' : 'img/produtos/acai-copo-real-2x.png', personalization: { tamanho: state.size.dataset.label, tamanho_id: Number(state.size.value), tipo: state.size.dataset.kind || 'copo', opcoes: state.options.map(input => ({ id: Number(input.value), nome: input.dataset.label, preco: Number(input.dataset.price || 0), tipo: input.dataset.type || 'outros' })), grupos: categoryMap, observacao: note } };
    const cartApi = window.AcaiFlowCart; if (!cartApi || typeof cartApi.add !== 'function') { setNotice('Não foi possível carregar o carrinho. Atualize a página e tente novamente.'); cartButton.disabled = false; return; }
    cartButton.disabled = true; const originalLabel = cartButton.textContent; cartButton.textContent = 'ADICIONANDO...';
    try { const saved = typeof cartApi.addAndSync === 'function' ? await cartApi.addAndSync(item) : cartApi.add(item); if (!saved) { cartButton.disabled = false; cartButton.textContent = originalLabel; const detail = window.ACAI_CART_SYNC?.lastError || ''; setNotice(detail || 'Não foi possível salvar o pedido no carrinho. Atualize a página e tente novamente.'); return; } window.location.href = baseUrl + '/pedido.php'; } catch (error) { cartButton.disabled = false; cartButton.textContent = originalLabel; setNotice('Não foi possível adicionar o pedido ao carrinho. Tente novamente.'); }
  });
})();
