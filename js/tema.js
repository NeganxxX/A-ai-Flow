(() => {
  'use strict';
  const root = document.documentElement;
  const buttons = document.querySelectorAll('.theme-toggle');
  const storageKey = 'acaiFlowTheme';

  function apply(theme) {
    const dark = theme === 'dark';
    root.classList.toggle('dark-mode', dark);
    buttons.forEach(button => {
      button.setAttribute('aria-pressed', String(dark));
      const label = dark ? 'Desativar modo noturno' : 'Ativar modo noturno';
      button.setAttribute('aria-label', label);
      button.setAttribute('title', label);
    });
    try { localStorage.setItem(storageKey, dark ? 'dark' : 'light'); } catch (e) {}
  }

  const initial = root.classList.contains('dark-mode') ? 'dark' : 'light';
  apply(initial);
  buttons.forEach(button => button.addEventListener('click', () => {
    apply(root.classList.contains('dark-mode') ? 'light' : 'dark');
  }));
})();
