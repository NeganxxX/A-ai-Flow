# Banco de Dados — Açaí Flow

## Instalação nova no XAMPP

1. Inicie Apache e MySQL.
2. Abra o phpMyAdmin.
3. Entre em **Importar**.
4. Importe `banco/banco.sql`.
5. O banco `acai_flow` será criado com tabelas, relações, produtos, opções e a conta administrativa inicial.

## Acesso administrativo único

**E-mail:** `admin@acaiflow.local`

**Senha inicial:** `AF-Admin!9mQ2#Lx7V`

A senha não é armazenada em texto puro no MySQL. O `banco.sql` contém somente o hash bcrypt, e o login usa `password_verify()`.

Não foi criado cadastro público de administradores. O painel deve ser acessado somente por `/admin/login.php`.

## Banco já existente

Faça backup antes e use `banco/migracao_segura.sql`.

## Camada adicional de segurança

`banco/usuario_aplicacao.sql` cria um usuário MySQL separado, com apenas `SELECT`, `INSERT`, `UPDATE` e `DELETE`. Isso reduz os privilégios da aplicação em relação ao `root`.

No XAMPP de desenvolvimento, o projeto ainda pode operar com `root` sem senha até que esse usuário separado seja configurado.

## Estrutura principal

- `administradores`: acesso restrito ao painel.
- `usuarios`: clientes.
- `enderecos`: endereços reutilizáveis dos clientes.
- `categorias`: organização do cardápio.
- `produtos`: produtos, estoque, preço e imagem.
- `tamanhos`: copos/barcas e limites de personalização.
- `opcoes`: frutas, cremes, adicionais, caldas e acompanhamentos.
- `produto_opcoes`: vínculo opcional de produtos com opções.
- `pedidos`: pedido e snapshot do endereço/pagamento.
- `itens_pedido`: itens e personalização registrada em JSON.
- `pedido_status_historico`: histórico de acompanhamento.
- `mensagens_contato`: formulário de contato.
- `configuracoes`: dados gerais da loja e Pix.
- `admin_logs`: trilha de ações administrativas.

## Segurança aplicada

- InnoDB + chaves estrangeiras.
- `utf8mb4`.
- E-mails únicos.
- CPF único quando informado.
- Senhas com hash bcrypt pelo PHP.
- Prepared statements na aplicação.
- CSRF nos formulários POST.
- Registro de histórico de pedidos.
- Log de ações administrativas.
- Estoque atualizado dentro de transação e com `FOR UPDATE` no fluxo do pedido.
- Opções e limites validados novamente no servidor.


## Observação de entrega
Para bancos já existentes, execute `banco/migracao_observacoes_contato.sql` uma vez. A coluna `pedidos.observacao_entrega` guarda instruções específicas da entrega, enquanto `pedidos.observacao` preserva a observação geral do pedido.
