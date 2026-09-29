(() => {
  document.querySelectorAll('[data-confirm]').forEach(btn => btn.addEventListener('click', e => {
    if (!window.confirm(btn.dataset.confirm)) e.preventDefault();
  }));
})();
