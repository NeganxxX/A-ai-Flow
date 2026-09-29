# Revisão Açaí Flow — pedidos, área do cliente e painel administrativo

## Problema encontrado
O pacote recebido ainda possuía `tableExists()` baseado em `SHOW TABLES LIKE ?`. Diversas páginas tratavam um falso negativo como se a tabela não existisse.

Isso afetava:
- histórico do cliente;
- dashboard;
- pedidos;
- produtos;
- categorias;
- clientes;
- opções;
- outras áreas administrativas.

## Correção central
`tableExists()` agora consulta `INFORMATION_SCHEMA.TABLES` com `DATABASE()` e prepared statement.

## Pedido
O pedido continua sendo criado com `usuarios.id` como `pedidos.usuario_id`.
A gravação do histórico inicial do pedido agora é feita diretamente, pois essa tabela faz parte do schema obrigatório.

## Área do cliente
`cliente/index.php` e `cliente/meus-pedidos.php` consultam `pedidos` diretamente por `usuario_id` da sessão.

## Painel
Dashboard, pedidos, produtos, categorias, clientes e opções consultam diretamente as tabelas correspondentes, com tratamento de erro.

## Banco
Nenhum novo SQL é necessário. A base existente, confirmada pelo usuário, possui:
- 20 produtos;
- 12 tamanhos;
- 53 opções.

## Validação estática
Todos os PHP do pacote foram verificados com `php -l`.
