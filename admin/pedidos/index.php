<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
$pageTitle='Pedidos';
require __DIR__.'/../_header.php';

$pdo=getPDO();$rows=[];$errorMessage='';
if(!$pdo){$errorMessage='Banco de dados indisponível.';}else{
    try{
        $rows=$pdo->query('SELECT id,nome_cliente,telefone,total,forma_pagamento,status,observacao,observacao_entrega,criado_em FROM pedidos ORDER BY id DESC')->fetchAll();
    }catch(Throwable $e){
        error_log('[Açai Flow] Painel de pedidos: '.$e->getMessage());
        $errorMessage='Não foi possível carregar os pedidos.';
    }
}
?>
<div class="admin-heading"><div><h1>Pedidos</h1><p>Acompanhe a fila de atendimento e atualize o status.</p></div><a class="admin-btn secondary" href="<?=url('admin/dashboard.php')?>">Dashboard</a></div>
<div class="admin-card">
<?php if($errorMessage): ?><div class="empty-state"><?=e($errorMessage)?></div><?php else: ?>
<div class="table-wrap"><table class="data-table">
<thead><tr><th>Nº</th><th>Cliente</th><th>Valor</th><th>Pagamento</th><th>Status</th><th>Observações</th><th>Data</th><th>Ação</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td>#<?=e($r['id'])?></td><td><?=e($r['nome_cliente'])?><small style="display:block;color:var(--muted)"><?=e($r['telefone'])?></small></td><td><?=formatarMoeda((float)$r['total'])?></td><td><?=e(ucfirst($r['forma_pagamento']))?></td><td><span class="status-pill <?=statusClasse($r['status'])?>"><?=e(statusLabel($r['status']))?></span></td><td><?php if(trim((string)($r['observacao']??''))!==''):?><span class="obs-badge">Pedido</span><?php endif;?><?php if(trim((string)($r['observacao_entrega']??''))!==''):?><span class="obs-badge obs-delivery">Entrega</span><?php endif;?><?php if(trim((string)($r['observacao']??''))==='' && trim((string)($r['observacao_entrega']??''))===''):?><span style="color:var(--muted);font-size:.6rem">—</span><?php endif;?></td><td><?=formatarData($r['criado_em'])?></td><td><a class="mini-btn" href="<?=url('admin/pedidos/detalhes.php?id='.$r['id'])?>">Detalhes</a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="8">Nenhum pedido registrado.</td></tr><?php endif; ?>
</tbody></table></div>
<?php endif; ?>
</div>
<?php require __DIR__.'/../_footer.php'; ?>
