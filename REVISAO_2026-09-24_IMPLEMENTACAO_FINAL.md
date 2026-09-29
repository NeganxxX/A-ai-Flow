# Açaí Flow — implementação final das correções solicitadas

## Cardápio
- Criada uma área específica de Combos e Promoções no `cardapio.php`.
- O conteúdo vem da mesma tabela `produtos` já utilizada pelo painel.
- Combos são identificados pela categoria `Combos`.
- Promoções usam a flag `promocao` existente.
- Campos `oferta_inicio` e `oferta_fim` controlam a janela de exibição.
- Sem datas, o item fica disponível continuamente enquanto estiver ativo.
- Com datas, o item aparece somente no período correspondente.
- O cardápio mantém os tamanhos do `Monte seu açaí` em uma seção independente.

## Painel administrativo
- Cadastro e edição de produtos passaram a aceitar período de exibição.
- A página de Promoções mostra situação e período, com link de edição.
- A página de Combos mostra situação e período, com edição direta.
- O botão `+ Novo combo` abre o cadastro com a categoria Combos pré-selecionada.
- A solução reaproveita a estrutura de produtos para evitar conflito entre catálogos.

## Login/cadastro
- Removido `Acesso administrativo` da área pública de login.
- A arte genérica lateral foi removida de login e cadastro.
- O espaço lateral inteiro ficou preparado para uma imagem escolhida pelo projeto, via `--auth-bg-image` em `css/login.css`.
- Textos e estrutura da área de autenticação foram preservados.

## Modo noturno
- Cobertura adicional em login/cadastro e login administrativo.
- Campos, tabelas, cards, filtros, alertas, área do cliente e componentes claros recebem fundo/texto/borda coerentes.
- Ícones de usuário e carrinho do cabeçalho passaram de glifos/emoji para SVG com `currentColor`, evitando símbolos que fiquem pretos em tema escuro.
- Indicador nativo de calendário foi ajustado no modo escuro.

## Banco
- Novo arquivo de migração: `banco/migracao_periodo_ofertas.sql`.
- `banco/banco.sql` já inclui as novas colunas para instalações novas.
