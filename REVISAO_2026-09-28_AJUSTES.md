# Revisão de ajustes — 28/09/2026

- Card de boas-vindas da Home reforçado como modal real centralizado no viewport, com formato quadrado e comportamento seguro em telas pequenas.
- Responsividade final estabilizada para tablet (768–1024px) e celular (até 767px), reduzindo grids apertados e overflow horizontal.
- Ícones de WhatsApp, Instagram e localização da Home receberam contraste e contorno reforçados no modo noturno.
- Cabeçalhos de segurança adicionados no Apache e arquivos de backup/temporários passaram a ser bloqueados.
- Sessão recebeu endurecimento adicional e rotação do token CSRF após autenticação.
- Exclusão de produto administrativo deixou de usar GET e passou para POST com CSRF.
- Formulário de contato recebeu limites de tamanho e campo honeypot anti-bot.
- CSS público recebeu versionamento por filemtime para evitar cache do navegador mantendo a versão anterior.
- Imagens ausentes foram repostas com arquivos-espelho válidos, aceitando repetição quando necessário para eliminar espaços em branco.
- Criados aliases para `acai-barca-real.jpg`, `acai-barca.png`, `acai-tigela-real.jpg`, `acai-tigela.png`, `acai-copo.png` e `img/auth/background.jpg`.
- Corrigido `produto.php` para não aplicar `asset()` duas vezes sobre a URL já pronta de `imagemTamanhoCatalogo()`.
