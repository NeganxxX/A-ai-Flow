# Revisão Açaí Flow — 24/09/2026 — Correções 2

## Cardápio
- Combos e promoções agora usam o mesmo padrão visual e proporção dos cards de produtos.
- Grade especial passou a usar 4 colunas em desktop, reduzindo a escala visual das imagens.
- Área recebeu destaque sutil com borda superior, badges e hover sem aumentar a altura dos cards.
- A exibição continua condicionada a produto ativo e ao período `oferta_inicio` / `oferta_fim` quando essas colunas existem.

## Monte seu açaí
- Combos e promoções vigentes passaram a aparecer no próprio montador, antes da escolha de tamanho.
- São exibidos como cards de produto e podem ser adicionados diretamente ao carrinho usando `product_id`.
- A implementação reutiliza a tabela `produtos` e a mesma consulta do cardápio, evitando catálogo duplicado.
- A numeração dos passos permanece sequencial mesmo quando não existe nenhuma oferta vigente.

## Modo noturno e ícones
- SVGs de benefícios, área do cliente, carrinho, localização e contato passaram a usar `fill:none` + `stroke:currentColor`.
- Isso evita o preenchimento preto padrão dos SVGs quando o tema escuro está ativo.
- Ícones dos benefícios recebem contraste maior no tema escuro.
- A área de combos/promos também recebeu regras específicas do tema escuro.

## Página inicial
- Removido o card de `Tigelas`, que não corresponde aos produtos disponíveis atualmente.
- O bloco de categorias da home foi ajustado para 2 cards, evitando espaço vazio após a remoção.
- A categoria `Tigelas` não foi apagada do banco; a correção é apenas de exibição da área principal para não destruir uma categoria que possa ser usada futuramente no painel.

## Banco
- Nenhuma nova alteração estrutural é necessária para estas correções.
- Combos e promoções continuam utilizando `categorias`, `produtos`, `promocao`, `oferta_inicio` e `oferta_fim` já implementados na versão anterior.

## Validação
- Todos os arquivos PHP foram verificados com `php -l`.
- JavaScript principal foi verificado com `node --check`.
- Foi feita inspeção das referências de combos/promoções, dos ícones e da remoção de Tigelas.
- A renderização automática com Chromium foi tentada, mas o processo travou no ambiente de execução; portanto, não foi usada como comprovação visual final.
