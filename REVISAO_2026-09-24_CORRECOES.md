# Açaí Flow — revisão complementar 24/09/2026

## Observações dos pedidos
- `pedidos.observacao` continua armazenando a observação geral do pedido.
- Nova coluna `pedidos.observacao_entrega` armazena instruções específicas da entrega.
- `monte-seu-acai.php` já envia a observação do item dentro de `personalizacao_json`; o painel agora exibe essa observação em cada item.
- `pedido.php` ganhou campos separados para observação do pedido e observação da entrega.
- `admin/pedidos/detalhes.php` exibe as duas observações e o complemento do endereço.
- `admin/pedidos/index.php` passou a sinalizar visualmente se existem observações de pedido/entrega.
- `cliente/pedido-detalhes.php` também exibe as observações para o próprio cliente.

## Banco
Para bancos existentes, executar uma vez:
`banco/migracao_observacoes_contato.sql`

Para instalações novas, `banco/banco.sql` já contém a coluna `observacao_entrega`.

## Contato no painel
Foi criada a área administrativa:
- `admin/contatos/index.php`
- `admin/contatos/detalhes.php`
- `admin/contatos/status.php`

O menu administrativo agora possui `Mensagens` com contador das mensagens novas.
O formulário público `contato.php` continua gravando em `mensagens_contato`.

## Animações
- Hero com cópia visual da arte do produto como camada de fundo de baixa intensidade.
- Animação suave das formas orgânicas, anéis, imagem do produto e faixa inferior do hero.
- Entrada progressiva do conteúdo do hero.
- Reveal por rolagem em seções e componentes públicos.
- Hover discreto em benefícios, cards, arte de personalização, imagem institucional e contatos.
- `prefers-reduced-motion` respeitado para evitar animações quando o usuário solicita redução de movimento.

## Validação
- PHP: sem erros de sintaxe.
- JavaScript: sem erros de sintaxe nos arquivos alterados.
- Teste HTTP local: `index.php` e `contato.php` carregam; páginas protegidas redirecionam quando não autenticadas.
- O ambiente de revisão não possui driver MySQL ativo, portanto a importação/conexão real deve ser validada no XAMPP do projeto.
