(() => {
  document.querySelectorAll('[data-admin-confirm]').forEach(btn => btn.addEventListener('click', e => {
    if (!window.confirm(btn.dataset.adminConfirm || 'Deseja continuar?')) e.preventDefault();
  }));

  // Menu lateral do painel (celular e tablet): abre como gaveta e fecha de várias formas.
  const sidebar = document.getElementById('adminSidebar');
  const toggle = document.querySelector('.admin-menu-toggle');
  const backdrop = document.getElementById('adminBackdrop');
  if (!sidebar || !toggle) return;

  const closeBtn = sidebar.querySelector('.admin-sidebar-close');
  const drawerQuery = window.matchMedia('(max-width:1050px)');

  const setOpen = (open) => {
    sidebar.classList.toggle('open', open);
    document.body.classList.toggle('admin-menu-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Fechar menu administrativo' : 'Abrir menu administrativo');
    if (backdrop) {
      if (open) { backdrop.hidden = false; requestAnimationFrame(() => backdrop.classList.add('show')); }
      else { backdrop.classList.remove('show'); setTimeout(() => { if (!sidebar.classList.contains('open')) backdrop.hidden = true; }, 250); }
    }
    if (open && closeBtn) closeBtn.focus({preventScroll: true});
    if (!open && document.activeElement && sidebar.contains(document.activeElement)) toggle.focus({preventScroll: true});
  };

  toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('open')));
  if (closeBtn) closeBtn.addEventListener('click', () => setOpen(false));
  if (backdrop) backdrop.addEventListener('click', () => setOpen(false));
  sidebar.querySelectorAll('.admin-nav a').forEach(link => link.addEventListener('click', () => setOpen(false)));
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && sidebar.classList.contains('open')) setOpen(false); });

  // Ao voltar para a tela larga, o menu fixo reaparece e a gaveta é reiniciada.
  const onChange = () => { if (!drawerQuery.matches && sidebar.classList.contains('open')) setOpen(false); };
  if (drawerQuery.addEventListener) drawerQuery.addEventListener('change', onChange); else drawerQuery.addListener(onChange);
})();
