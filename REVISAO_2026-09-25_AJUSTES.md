# Açaí Flow — ajustes 25/09/2026

## Banner e identidade
- Banner da Home agora alterna entre 5 tons escuros derivados da identidade da marca a cada 7 segundos.
- Banners internos (`.page-hero`) também recebem a rotação de tons.
- Foram adicionados elementos vetoriais de frutas flutuando suavemente no banner da Home.
- Respeito a `prefers-reduced-motion`.

## Barra principal
- Horário central exibido no desktop: Seg–Sex 10h–22h • Sáb 10h–23h.
- No mobile, o horário aparece dentro do menu.

## Cardápio e produto
- Os cartões de copos/barcas passam a apontar para os produtos reais cadastrados no MySQL.
- `produto.php` passa a aceitar também `?tamanho=ID` e mostra uma página de detalhe para cada tamanho.
- Imagens dos detalhes podem ser ampliadas com lightbox.

## Combo Casal
- Removeu-se o preço fixo da apresentação.
- O total é calculado pela soma dos preços dos dois copos/barcas escolhidos + adicionais pagos.
- O servidor recalcula esse valor antes de gravar o pedido.
- `banco/banco.sql` passa a criar o Combo Casal com preço 0.00.
- Para bancos existentes, executar `banco/migracao_combo_casal_preco.sql`.
- Combo Flow permanece com seu preço base cadastrado.

## Validação
- PHP: todos os arquivos verificados com `php -l`.
- JavaScript: todos os arquivos verificados com `node --check`.
