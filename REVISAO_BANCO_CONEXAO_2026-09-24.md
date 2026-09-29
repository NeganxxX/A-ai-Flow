# Revisão de conexão e divergências — Açaí Flow

## Conexão esperada

- Host: `localhost`
- Banco: `acai_flow`
- Desenvolvimento XAMPP: `root` com senha vazia, caso essa seja a configuração do MySQL/MariaDB local.
- Alternativa recomendada: usuário `acai_flow_app`, criado por `banco/usuario_aplicacao.sql`, com privilégios de `SELECT, INSERT, UPDATE, DELETE`.
- Para a alternativa, copiar `config/local.example.php` para `config/local.php` e manter o arquivo fora de repositórios públicos.

## Correções feitas nesta revisão

1. `config/conexao.php` agora registra falhas de conexão no log do PHP sem exibir credenciais ao visitante.
2. Foi criada a página administrativa `admin/configuracoes/banco.php`, acessível apenas após login administrativo, para mostrar o estado da conexão e contagens fundamentais.
3. Foi criado `banco/diagnostico.sql` para conferência no phpMyAdmin.
4. Corrigida uma divergência documental: o `README.md` informava uma senha administrativa diferente da senha realmente gravada no `banco.sql`.
5. A regra comercial de adicionais foi reforçada no painel: qualquer opção cadastrada como `adicional` é salva obrigatoriamente com `R$ 2,00`.
6. A mesma regra foi aplicada na edição de adicionais.

## Dados iniciais presentes no `banco.sql`

- 1 administrador
- 7 categorias
- 12 tamanhos (6 copos + 6 barcas)
- 53 opções (10 frutas, 5 cremes, 6 adicionais, 4 caldas e 28 acompanhamentos)
- 20 produtos
- configurações iniciais da loja

## Divergências/itens de atenção identificados

### 1. `enderecos` existe no banco, mas ainda não é utilizado pelo fluxo de checkout
O pedido mantém um snapshot do endereço diretamente em `pedidos`, o que preserva o histórico, mas a tabela `enderecos` ainda não é preenchida/reutilizada pelo site.

### 2. `produto_opcoes` existe no banco, mas o código atual não consulta essa relação
Hoje o personalizador usa todas as opções ativas de `opcoes`. A tabela está preparada para futura restrição por produto, mas não participa da regra atual.

### 3. Conexão real MySQL não pôde ser executada neste ambiente de revisão
O ambiente usado para esta análise possui `PDO`, mas não possui a extensão `pdo_mysql` nem um servidor MySQL/MariaDB acessível. Por isso, a conexão real deve ser validada no XAMPP do projeto usando a nova página de diagnóstico.

## Testes feitos

- `php -l` executado em todos os arquivos PHP do projeto: sem erros de sintaxe.
- Hash da senha administrativa conferido com `password_verify`: válido para a senha definida no `banco.sql`.
- Tabelas e nomes usados nas consultas PHP foram comparados com a estrutura do `banco.sql`.
- Regras oficiais dos limites permanecem alinhadas entre banco, `config/limites.php` e validação de pedido.
