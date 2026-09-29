(() => {
  'use strict';
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', String(open));
    });
    nav.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
      nav.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    }));
  }

  document.querySelectorAll('[data-scroll]').forEach(btn => {
    btn.addEventListener('click', event => {
      const target = document.querySelector(btn.dataset.scroll);
      if (target) { event.preventDefault(); target.scrollIntoView({behavior:'smooth', block:'start'}); }
    });
  });

  document.querySelectorAll('[data-auto-dismiss]').forEach(el => {
    setTimeout(() => el.remove(), Number(el.dataset.autoDismiss) || 4500);
  });

  const filters = document.querySelectorAll('[data-filter]');
  const products = document.querySelectorAll('[data-category]');
  filters.forEach(filter => filter.addEventListener('click', () => {
    filters.forEach(f => {
      f.classList.remove('active');
      f.setAttribute('aria-selected', 'false');
    });
    filter.classList.add('active');
    filter.setAttribute('aria-selected', 'true');
    const selected = filter.dataset.filter;
    products.forEach(product => {
      const category = product.dataset.category || '';
      product.hidden = !(selected === 'todos' || category === selected);
    });
  }));
})();

// Açaí Flow — animações de entrada discretas e coerentes com a identidade.
(() => {
  'use strict';
  const automatic = Array.from(document.querySelectorAll('.product-card, .value-card, .contact-panel, .checkout-panel, .tracking-card, .story-image'));
  automatic.forEach(el => { if (!el.matches('[data-reveal]')) el.setAttribute('data-reveal',''); });
  const elements = document.querySelectorAll('[data-reveal]');
  if (!elements.length) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    elements.forEach(el => el.classList.add('is-visible'));
    return;
  }
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, {threshold: .14, rootMargin: '0px 0px -35px 0px'});
  elements.forEach(el => observer.observe(el));
})();


// Açaí Flow — banners com variação suave de tons da identidade.
(() => {
  'use strict';
  const banners = [...document.querySelectorAll('.hero, .page-hero')];
  if (!banners.length) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const tones = ['flow-tone-0','flow-tone-1','flow-tone-2','flow-tone-3','flow-tone-4'];
  banners.forEach(banner => {
    banner.classList.add('flow-banner-ready');
    let current = 0;
    const rotate = () => {
      banner.classList.remove(...tones);
      banner.classList.add(tones[current]);
      current = (current + 1) % tones.length;
    };
    rotate();
    window.setInterval(rotate, 5000);
  });
})();

// Visualização ampliada das imagens de produto.
(() => {
  'use strict';
  const images = [...document.querySelectorAll('.product-detail-image-zoom img')];
  if (!images.length) return;
  let modal = document.getElementById('imageLightbox');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'imageLightbox';
    modal.className = 'image-lightbox';
    modal.innerHTML = '<button type="button" class="image-lightbox-close" aria-label="Fechar">×</button><img alt=""><span>ESC para fechar</span>';
    document.body.appendChild(modal);
  }
  const modalImg = modal.querySelector('img');
  const close = () => { modal.classList.remove('is-open'); document.body.classList.remove('lightbox-open'); };
  images.forEach(img => img.parentElement.addEventListener('click', () => {
    modalImg.src = img.currentSrc || img.src;
    modalImg.alt = img.alt;
    modal.classList.add('is-open');
    document.body.classList.add('lightbox-open');
  }));
  modal.addEventListener('click', e => { if (e.target === modal || e.target.classList.contains('image-lightbox-close')) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
})();


// Card de boas-vindas: em visitantes não autenticados, aparece na Home a cada abertura.
(() => {
  'use strict';
  const modal = document.getElementById('clientWelcome');
  if (!modal) return;

  const open = () => {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('welcome-modal-open');
  };
  const close = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('welcome-modal-open');
  };

  modal.querySelectorAll('[data-welcome-close]').forEach((element) => {
    element.addEventListener('click', close);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) close();
  });

  window.setTimeout(open, 700);
})();
