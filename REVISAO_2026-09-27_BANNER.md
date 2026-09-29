# Revisão 27/09/2026 — Banner principal Açaí Flow

## Alteração aplicada
O banner inicial foi refeito para usar a arte integral aprovada como a peça principal da home, substituindo a composição anterior que separava texto e produto em elementos diferentes.

### Desktop
- `img/banners/hero-flow-banner.webp` como fonte principal otimizada.
- `img/banners/hero-flow-banner.png` como fallback.
- A arte ocupa toda a largura do hero, preservando a composição completa.
- As duas chamadas desenhadas na própria arte continuam funcionais por áreas clicáveis transparentes:
  - Monte seu açaí
  - Ver cardápio

### Responsividade
- Até 700 px o banner troca para uma composição vertical específica em:
  - `hero-flow-banner-mobile.webp`
  - `hero-flow-banner-mobile.png`
- A versão móvel foi composta para manter legíveis logo, localização, headline, descrição e CTAs, colocando o produto na parte inferior sem cortar a mensagem principal.
- As áreas clicáveis dos CTAs possuem coordenadas próprias para desktop e mobile.

## Integração com a identidade existente
Foram preservados e reincorporados os efeitos já usados no projeto:
- rotação de tons `flow-tone-*` a cada 5 segundos;
- flutuação orgânica dos elementos decorativos;
- frutas flutuantes;
- animação de entrada do banner;
- transições suaves e respeito a `prefers-reduced-motion`.

A variação de tons agora atua de forma sutil também sobre a própria arte do banner, mantendo a leitura dos textos e a identidade visual.

## Acessibilidade
A arte contém o conteúdo visual completo, mas o hero também mantém uma camada semântica para leitores de tela, com título, descrição e os dois links de ação.

## Validações realizadas
- Todos os arquivos PHP: `php -l` sem erros.
- Todos os arquivos JavaScript: `node --check` sem erros.
- CSS principal, responsivo e tema: chaves balanceadas.
- Home: HTTP 200.
- Os quatro assets do banner: HTTP 200.
