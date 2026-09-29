(() => {
  'use strict';
  const cartBox = document.getElementById('checkoutCart');
  const totalEl = document.getElementById('checkoutTotal');
  const form = document.getElementById('checkoutForm');
  if (!cartBox || !totalEl) return;
  const baseUrl = window.ACAI_BASE_URL || '';
  const money = value => 'R$ ' + Number(value).toFixed(2).replace('.', ',');

  // hydrate() dispara 'acaiFlowCartUpdated' antes de terminar; sem esta trava o ouvinte
  // chamava render() de novo, que chamava hydrate() de novo (recursão até estourar a pilha).
  let hydrating = false;

  async function render() {
    if (window.AcaiFlowCart?.hydrate && !window.AcaiFlowCart.isHydrated?.()) {
      if (hydrating) return 0;
      hydrating = true;
      try {
        await window.AcaiFlowCart.hydrate();
      } finally {
        hydrating = false;
      }
    }

    const cart = window.AcaiFlowCart?.get() || [];
    if (!cart.length) {
      cartBox.innerHTML = '<div class="empty-state">Seu carrinho está vazio.<br><br><a class="btn btn-bordo btn-small" href="'+baseUrl+'/cardapio.php">VER CARDÁPIO</a></div>';
      totalEl.textContent = money(0);
      if (form) form.querySelector('button[type="submit"]')?.setAttribute('disabled','disabled');
      return 0;
    }
    let total = 0;
    cartBox.innerHTML = cart.map((item, index) => {
      const subtotal = Number(item.price || 0) * Number(item.quantity || 0);
      total += subtotal;
      const image = item.image ? (String(item.image).startsWith('http') ? item.image : baseUrl + '/' + String(item.image).replace(/^\//,'')) : baseUrl + '/img/placeholder.svg';
      const comboPortions = Array.isArray(item.personalization?.porcoes) ? item.personalization.porcoes : [];
      const comboDetails = comboPortions.map((portion, idx) => { const opts = Array.isArray(portion.opcoes) ? portion.opcoes.map(op => op.nome).join(', ') : ''; const base = `Porção ${idx + 1}: ${portion.tipo === 'barca' ? 'Barca ' : ''}${portion.tamanho || ''}`; return opts ? `${base} — ${opts}` : base; });
      const details = comboDetails.length ? comboDetails.join(' | ') : (Array.isArray(item.personalization?.opcoes) ? item.personalization.opcoes.map(op => op.nome).join(', ') : '');
      return `<article class="order-item">
        <div class="order-item-thumb"><img src="${image}" alt=""></div>
        <div><div class="order-item-name">${item.name}</div><div class="order-item-meta">Qtd.: ${item.quantity}${details ? ' • '+details : ''}</div></div>
        <div style="text-align:right"><div class="order-item-price">${money(subtotal)}</div><button type="button" class="mini-btn" data-remove-index="${index}">Remover</button></div>
      </article>`;
    }).join('');
    totalEl.textContent = money(total);
    const aside = document.getElementById('checkoutTotalAside'); if (aside) aside.textContent = money(total);
    form?.querySelector('button[type="submit"]')?.removeAttribute('disabled');
    return total;
  }

  window.addEventListener('acaiFlowCartUpdated', () => { void render(); });

  cartBox.addEventListener('click', event => {
    const remove = event.target.closest('[data-remove-index]');
    if (!remove) return;
    const cart = window.AcaiFlowCart.get();
    cart.splice(Number(remove.dataset.removeIndex), 1);
    window.AcaiFlowCart.save(cart); void render();
  });

  form?.addEventListener('submit', () => {
    let hidden = form.querySelector('input[name="cart_json"]');
    if (!hidden) { hidden = document.createElement('input'); hidden.type='hidden'; hidden.name='cart_json'; form.appendChild(hidden); }
    hidden.value = JSON.stringify(window.AcaiFlowCart?.get() || []);
  });

  document.querySelectorAll('input[name="forma_pagamento"]').forEach(input => input.addEventListener('change', () => {
    const pix = document.getElementById('pixPaymentInfo');
    const troco = document.getElementById('cashChange');
    if (pix) pix.classList.toggle('hide', input.value !== 'pix' || !input.checked);
    if (troco) troco.classList.toggle('hide', input.value !== 'dinheiro' || !input.checked);
  }));
  void render();
})();
