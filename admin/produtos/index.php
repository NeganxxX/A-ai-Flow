<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
$pageTitle='Produtos';
require __DIR__.'/../_header.php';
$pdo=getPDO();$products=[];$errorMessage='';
if(!$pdo){$errorMessage='Banco de dados indisponível.';}else{
 try{$products=$pdo->query("SELECT p.*,c.nome categoria FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id ORDER BY p.id DESC")->fetchAll();}
 catch(Throwable $e){error_log('[Açai Flow] Produtos admin: '.$e->getMessage());$errorMessage='Não foi possível carregar o cardápio.';}
}
?>
<div class="admin-heading"><div><h1>Cardápio</h1><p>Cadastre produtos, preços e estoque da Açaí Flow.</p></div><div class="admin-actions"><a class="admin-btn secondary" href="<?=url('admin/categorias/index.php')?>">Categorias</a><a class="admin-btn" href="<?=url('admin/produtos/cadastrar.php')?>">+ Novo produto</a></div></div>
<div class="admin-card"><?php if($errorMessage): ?><div class="empty-state"><?=e($errorMessage)?></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th>Ações</th></tr></thead><tbody>
<?php foreach($products as $p): ?><tr><td><strong><?=e($p['nome'])?></strong></td><td><?=e($p['categoria']??'Sem categoria')?></td><td><?=mb_strtolower(trim((string)$p['nome']))==='combo casal' ? 'Conforme escolhas' : formatarMoeda((float)$p['preco'])?></td><td><?=e($p['estoque'])?></td><td><?=((int)$p['ativo']?'Ativo':'Inativo')?></td><td><div class="admin-table-actions"><a class="mini-btn" href="<?=url('produto.php?id='.$p['id'])?>">Ver</a><a class="mini-btn" href="<?=url('admin/produtos/editar.php?id='.$p['id'])?>">Editar</a><form action="<?=url('admin/produtos/excluir.php')?>" method="post" style="display:inline" onsubmit="return window.confirm('Excluir este produto?');"><input type="hidden" name="csrf" value="<?=csrfToken()?>"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="mini-btn" type="submit">Excluir</button></form></div></td></tr><?php endforeach; ?>
<?php if(!$products): ?><tr><td colspan="6">Nenhum produto cadastrado.</td></tr><?php endif; ?></tbody></table></div><?php endif; ?></div>
<?php require __DIR__.'/../_footer.php'; ?>
