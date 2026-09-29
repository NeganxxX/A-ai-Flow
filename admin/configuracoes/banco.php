<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
require_once __DIR__.'/../../config/conexao.php';
require_once __DIR__.'/../../includes/funcoes.php';
$pageTitle='Diagnóstico do banco';
require __DIR__.'/../_header.php';
$pdo=getPDO();$dbName=DB_NAME;$connectionOk=$pdo instanceof PDO;
$tables=['administradores','usuarios','categorias','produtos','tamanhos','opcoes','pedidos','itens_pedido','pedido_status_historico','mensagens_contato','configuracoes','admin_logs'];
$counts=[];
if($connectionOk){
 foreach($tables as $table){
  try{
   $st=$pdo->prepare('SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? LIMIT 1');$st->execute([$table]);
   if(!$st->fetchColumn()){$counts[$table]=['exists'=>false,'count'=>null];continue;}
   $st=$pdo->query('SELECT COUNT(*) FROM `'.$table.'`');$counts[$table]=['exists'=>true,'count'=>(int)$st->fetchColumn()];
  }catch(Throwable $e){$counts[$table]=['exists'=>false,'count'=>null];}
 }
}
?>
<div class="admin-heading"><div><h1>Diagnóstico do banco</h1><p>Verificação direta da conexão e das tabelas usadas pelo site.</p></div></div>
<div class="admin-kpis">
<div class="kpi"><small>Banco</small><strong style="font-size:1rem"><?=e($dbName)?></strong></div>
<div class="kpi"><small>Conexão</small><strong style="font-size:1rem"><?=$connectionOk?'OK':'FALHA'?></strong></div>
<div class="kpi"><small>Produtos</small><strong><?=e($counts['produtos']['count']??0)?></strong></div>
<div class="kpi"><small>Pedidos</small><strong><?=e($counts['pedidos']['count']??0)?></strong></div>
</div>
<div class="admin-card"><h2>Estado das tabelas</h2><div class="table-wrap"><table class="data-table"><thead><tr><th>Tabela</th><th>Existe</th><th>Registros</th></tr></thead><tbody>
<?php foreach($counts as $table=>$info): ?><tr><td><?=e($table)?></td><td><?=!empty($info['exists'])?'Sim':'Não'?></td><td><?=$info['count']===null?'—':(int)$info['count']?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="admin-card" style="margin-top:18px"><h2>Últimos pedidos</h2>
<?php $recentOrders=[]; if($connectionOk && !empty($counts['pedidos']['exists'])){try{$recentOrders=$pdo->query('SELECT id,usuario_id,nome_cliente,total,status,criado_em FROM pedidos ORDER BY id DESC LIMIT 10')->fetchAll();}catch(Throwable $e){}} ?>
<?php if(!$recentOrders): ?><div class="empty-state">Nenhum pedido encontrado.</div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Nº</th><th>Cliente ID</th><th>Cliente</th><th>Total</th><th>Status</th><th>Data</th></tr></thead><tbody>
<?php foreach($recentOrders as $o): ?><tr><td>#<?=e($o['id'])?></td><td><?=e($o['usuario_id'])?></td><td><?=e($o['nome_cliente'])?></td><td><?=formatarMoeda((float)$o['total'])?></td><td><?=e(statusLabel($o['status']))?></td><td><?=formatarData($o['criado_em'])?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
</div>
<?php require __DIR__.'/../_footer.php'; ?>
