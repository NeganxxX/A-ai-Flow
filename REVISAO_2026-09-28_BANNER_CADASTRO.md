# Revisão — 28/09/2026

## Banner
- Substituída a arte antiga por `img/banners/hero-produto-novo.png`.
- A nova arte é PNG com transparência e recorte baseado no canal alfa.
- Removidas as camadas externas de frutas, círculos e elementos orgânicos que duplicavam visualmente o produto.
- Produto configurado em tamanho médio, sem flutuação; entrada apenas com fade-in.

## Cadastro e área do cliente
- Removido o campo CPF do cadastro.
- CPF deixou de ser recebido e gravado por `processa/cadastro.php`.
- CPF deixou de ser editável/consultável pela área do cliente.
- CPF deixou de ser exibido no painel administrativo de clientes.
- CPF deixou de ser consultado no checkout.
- Removida a função PHP `normalizarCpf()` por não ser mais usada.
- Para bancos antigos, `banco/remover_cpf_clientes.sql` zera os valores e remove a coluna/index.

## Conta de teste
- E-mail: `cliente@acaiflow.local`
- Senha: `AcaiFlow@2026`
- O hash foi validado com `password_verify()` e corresponde à senha informada.
