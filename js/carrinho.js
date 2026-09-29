(() => {
  'use strict';

  const KEY = 'acaiFlowCart:v2';
  const legacyKeys = ['acaiFlowCart'];
  let memoryCart = [];
  let hydrated = false;

  function getStorage() {
    try {
      const testKey = '__acai_flow_storage_test__';
      localStorage.setItem(testKey, '1');
      localStorage.removeItem(testKey);
      return localStorage;
    } catch (_) {
      try {
        const testKey = '__acai_flow_session_test__';
        sessionStorage.setItem(testKey, '1');
        sessionStorage.removeItem(testKey);
        return sessionStorage;
      } catch (_) {
        return null;
      }
    }
  }

  const storage = getStorage();

  function parse(raw) {
    try {
      const parsed = JSON.parse(raw || '[]');
      return Array.isArray(parsed) ? parsed : [];
    } catch (_) {
      return [];
    }
  }

  function getLocalCart() {
    if (!storage) return memoryCart.slice();

    const current = storage.getItem(KEY);
    if (current !== null) return parse(current);

    for (const legacyKey of legacyKeys) {
      const legacy = storage.getItem(legacyKey);
      if (legacy !== null) {
        const migrated = parse(legacy);
        try { storage.setItem(KEY, JSON.stringify(migrated)); } catch (_) {}
        return migrated;
      }
    }

    return [];
  }

  function saveLocalCart(cart) {
    const safeCart = Array.isArray(cart) ? cart : [];
    memoryCart = safeCart.slice();

    if (!storage) return false;

    try {
      storage.setItem(KEY, JSON.stringify(safeCart));
      return true;
    } catch (_) {
      window.ACAI_CART_SYNC.lastError = 'Não foi possível comunicar com o servidor do carrinho.';
      return false;
    }
  }

  function getCart() {
    return getLocalCart();
  }

  function countCart(cart) {
    return cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
  }

  function updateCount() {
    const count = document.getElementById('cartCount');
    if (count) count.textContent = String(countCart(getCart()));
  }

  function dispatchUpdated() {
    window.dispatchEvent(new CustomEvent('acaiFlowCartUpdated', {
      detail: { cart: getCart(), hydrated }
    }));
    updateCount();
  }

  async function syncServer(cart) {
    if (!window.ACAI_CART_SYNC?.authenticated) return true;

    const csrf = window.ACAI_CART_SYNC.csrf || '';
    if (!csrf) return false;

    try {
      const body = new URLSearchParams();
      body.set('csrf', csrf);
      body.set('cart_json', JSON.stringify(cart));

      const response = await fetch(window.ACAI_CART_SYNC.endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body
      });

      const data = await response.json();
      if (!response.ok || !data.ok) {
        window.ACAI_CART_SYNC.lastError = String(data?.message || 'Falha ao sincronizar o carrinho.');
        return false;
      }

      if (Array.isArray(data.cart)) {
        saveLocalCart(data.cart);
      }
      return true;
    } catch (_) {
      return false;
    }
  }

  function saveCart(cart) {
    const ok = saveLocalCart(cart);
    dispatchUpdated();
    void syncServer(getCart());
    return ok;
  }

  function addItem(item) {
    const cart = getCart();
    const safeItem = {
      signature: String(item.signature || item.product_id || ('item-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8))),
      product_id: item.product_id ?? null,
      size_id: item.size_id ?? null,
      size_type: item.size_type ?? null,
      name: String(item.name || 'Açaí Flow'),
      price: Math.max(0, Number(item.price || 0)),
      quantity: Math.max(1, Number(item.quantity || 1)),
      image: String(item.image || ''),
      personalization: item.personalization || null
    };

    const existing = cart.find(row => row.signature === safeItem.signature);
    if (existing) existing.quantity += safeItem.quantity;
    else cart.push(safeItem);

    return saveCart(cart);
  }

  async function addAndSync(item) {
    const saved = addItem(item);
    if (!saved) return false;

    // Em uma conta autenticada, o servidor passa a ser a fonte de
    // continuidade do carrinho entre páginas. Isso evita perder o item
    // durante a troca de página após o cadastro/login.
    if (window.ACAI_CART_SYNC?.authenticated) {
      const synced = await syncServer(getCart());
      if (!synced) return false;
    }

    return true;
  }

  async function hydrateFromServer() {
    if (!window.ACAI_CART_SYNC?.authenticated) {
      hydrated = true;
      dispatchUpdated();
      return;
    }

    // O rodapé já entrega a cópia do carrinho da sessão PHP.
    // Usamos essa cópia imediatamente para evitar qualquer janela em que
    // o checkout possa renderizar vazio enquanto o AJAX ainda responde.
    if (Array.isArray(window.ACAI_CART_SYNC.serverCart)) {
      const local = getCart();
      const server = window.ACAI_CART_SYNC.serverCart;
      const merged = [...server];
      for (const item of local) {
        const existing = merged.find(row => row.signature && row.signature === item.signature);
        if (existing) {
          existing.quantity = Math.max(Number(existing.quantity || 0), Number(item.quantity || 0));
        } else {
          merged.push(item);
        }
      }
      saveLocalCart(merged);
      dispatchUpdated();
    }

    try {
      const response = await fetch(window.ACAI_CART_SYNC.endpoint, {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await response.json();
      if (!response.ok || !data.ok || !Array.isArray(data.cart)) throw new Error('cart-sync');

      const local = getCart();
      const server = data.cart;

      // Após cadastro/login, preserva um carrinho local que já existia.
      // Se o servidor já tinha itens, mantém os dois conjuntos sem duplicar assinaturas.
      const merged = [...server];
      for (const item of local) {
        const existing = merged.find(row => row.signature && row.signature === item.signature);
        if (existing) {
          existing.quantity = Math.max(Number(existing.quantity || 0), Number(item.quantity || 0));
        } else {
          merged.push(item);
        }
      }

      saveLocalCart(merged);
      if (JSON.stringify(merged) !== JSON.stringify(server)) {
        await syncServer(merged);
      }
    } catch (_) {
      // O carrinho local continua funcionando mesmo sem sincronização do servidor.
    } finally {
      hydrated = true;
      dispatchUpdated();
    }
  }

  window.AcaiFlowCart = {
    get: getCart,
    save: saveCart,
    add: addItem,
    addAndSync,
    count: countCart,
    key: KEY,
    isHydrated: () => hydrated,
    hydrate: hydrateFromServer,
    sync: () => syncServer(getCart())
  };

  document.addEventListener('click', event => {
    const button = event.target.closest('[data-add-cart]');
    if (!button) return;

    event.preventDefault();

    let personalization = null;
    if (button.dataset.personalization) {
      try { personalization = JSON.parse(button.dataset.personalization); } catch (_) {}
    }

    const item = {
      signature: String(button.dataset.signature || button.dataset.productId || ('item-' + Date.now())),
      product_id: button.dataset.productId ? Number(button.dataset.productId) : null,
      name: button.dataset.name || 'Açaí Flow',
      price: Number(button.dataset.price || 0),
      quantity: Math.max(1, Number(button.dataset.quantity || 1)),
      image: button.dataset.image || '',
      personalization
    };

    const added = addItem(item);
    if (!added && !storage) {
      alert('Não foi possível guardar o carrinho neste navegador. Ative o armazenamento do site e tente novamente.');
      return;
    }

    button.classList.add('added');
    const original = button.innerHTML;
    button.innerHTML = 'ADICIONADO ✓';
    setTimeout(() => {
      button.innerHTML = original;
      button.classList.remove('added');
    }, 1200);
  });

  updateCount();
  void hydrateFromServer();
})();
