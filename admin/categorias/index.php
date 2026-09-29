<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
$pageTitle='Categorias';
require __DIR__.'/../_header.php';
$pdo=getPDO();$rows=[];$errorMessage='';
if(!$pdo){$errorMessage='Banco de dados indisponível.';}else{
 try{$rows=$pdo->query("SELECT id,nome,ordem,ativo FROM categorias ORDER BY ordem,nome")->fetchAll();}
 catch(Throwable $e){error_log('[Açai Flow] Categorias admin: '.$e->getMessage());$errorMessage='Não foi possível carregar as categorias.';}
}
?>
<div class="admin-heading"><div><h1>Categorias</h1><p>Organize os produtos do cardápio.</p></div><a class="admin-btn" href="<?=url('admin/categorias/cadastrar.php')?>">+ Nova categoria</a></div>
<div class="admin-card"><?php if($errorMessage): ?><div class="empty-state"><?=e($errorMessage)?></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Nome</th><th>Ordem</th><th>Status</th><th>Ação</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=e($r['nome'])?></td><td><?=e($r['ordem'])?></td><td><?=((int)$r['ativo']?'Ativa':'Inativa')?></td><td><a class="mini-btn" href="<?=url('admin/categorias/editar.php?id='.$r['id'])?>">Editar</a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="4">Nenhuma categoria cadastrada.</td></tr><?php endif; ?></tbody></table></div><?php endif; ?></div>
<?php require __DIR__.'/../_footer.php'; ?>
