<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
$pageTitle='Adicionais';
require __DIR__.'/../_header.php';
$pdo=getPDO();$rows=[];$errorMessage='';
if(!$pdo){$errorMessage='Banco de dados indisponível.';}else{
 try{$rows=$pdo->query('SELECT id,tipo,nome,preco_adicional,ativo FROM opcoes ORDER BY tipo,ordem,nome')->fetchAll();}
 catch(Throwable $e){error_log('[Açai Flow] Opções admin: '.$e->getMessage());$errorMessage='Não foi possível carregar as opções.';}
}
?>
<div class="admin-heading"><div><h1>Ingredientes e adicionais</h1><p>Gerencie as opções do “Monte seu açaí”.</p></div><a class="admin-btn" href="<?=url('admin/adicionais/cadastrar.php')?>">+ Nova opção</a></div>
<div class="admin-card"><?php if($errorMessage): ?><div class="empty-state"><?=e($errorMessage)?></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Nome</th><th>Grupo</th><th>Preço extra</th><th>Ativo</th><th>Ação</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?=e($r['nome'])?></td><td><?=e(ucfirst($r['tipo']))?></td><td><?=formatarMoeda((float)$r['preco_adicional'])?></td><td><?=($r['ativo']?'Sim':'Não')?></td><td><a class="mini-btn" href="<?=url('admin/adicionais/editar.php?id='.$r['id'])?>">Editar</a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="5">Nenhuma opção cadastrada.</td></tr><?php endif; ?></tbody></table></div><?php endif; ?></div>
<?php require __DIR__.'/../_footer.php'; ?>
