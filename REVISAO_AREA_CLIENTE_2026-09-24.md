# Revisão — Área do cliente Açaí Flow

## Alterações visuais
- Dashboard do cliente com hero, estatísticas, pedido em andamento, histórico, atalhos e CTA.
- Sidebar redesenhada com logo, identificação da conta, navegação agrupada e CTA.
- Layout de histórico e detalhes de pedido reorganizado para desktop e mobile.
- Tela de dados do cliente reorganizada em seções pessoais e segurança.
- Responsividade reforçada para tablets e celulares.
- Modo noturno contemplado nos novos componentes da área do cliente.

## Correções de integração visual
- `css/cliente.css` agora é carregado antes do `header.php` em todas as páginas da área do cliente.
- Removidas dependências de estilos inline para a maior parte da interface.
- Mantido o fluxo PHP e as consultas existentes; a alteração é focada no visual e na organização.

## Validação
- cliente/index.php — sintaxe OK
- cliente/meus-pedidos.php — sintaxe OK
- cliente/pedido-detalhes.php — sintaxe OK
- cliente/meus-dados.php — sintaxe OK
