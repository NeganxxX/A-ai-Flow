# Revisão 24/09 — Combos personalizáveis e navegação do cardápio

## Alterações desta rodada

- Os produtos normais do cardápio agora usam **VER ITEM** em vez de **MONTAR**.
- A página de detalhe do produto mantém a opção **MONTAR DO MEU JEITO**; produtos da categoria **Combos** passam a usar **MONTAR COMBO**.
- O montador ganhou um modo específico para combos.
- **Combo Casal** passa a trabalhar com duas porções independentes. Cada porção pode aceitar copo ou barca, incluindo combinações como dois copos, duas barcas ou um de cada.
- **Combo Flow** passa a usar uma porção de copo 500ml com maior limite de acompanhamentos.
- Cada porção do combo tem personalização própria: frutas, creme, adicional, calda e acompanhamentos.
- Adicionais pagos entram no preço do combo e são recalculados pelo PHP antes do pedido ser registrado.
- O servidor valida novamente tamanho, tipo, quantidade de porções, limites e opções permitidas. A configuração do navegador não é considerada fonte de verdade.
- Os pedidos e a área do cliente passaram a exibir as porções e suas personalizações.
- O painel administrativo ganhou configuração estruturada de combos: quantidade de porções, formatos, tamanhos e limites de complementos.
- Promoções continuam editáveis como produtos normais no painel, inclusive preço, estoque, imagem, status e período de exibição.
- A migração limpa/segura para a configuração de combos fica em `banco/migracao_combos_personalizaveis.sql`.
- `banco/banco.sql` foi atualizado para instalações novas, incluindo a tabela e as configurações iniciais de Combo Flow e Combo Casal.
- O texto/estilo do modo noturno foi reforçado para que os tamanhos e subtítulos do montador permaneçam legíveis em fundo escuro.

## Configuração inicial entregue

**Combo Flow**
- 1 porção.
- Somente copo de 500ml.
- Limite de acompanhamentos: 6.

**Combo Casal**
- 2 porções independentes.
- Cada porção pode escolher copo ou barca.
- Todos os tamanhos existentes do catálogo ficam disponíveis inicialmente.
- Limites por porção: 2 frutas, 1 creme, 1 adicional, 2 caldas e 5 acompanhamentos.

Todos esses limites podem ser alterados no painel administrativo.
