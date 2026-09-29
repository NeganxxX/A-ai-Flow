<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
$pageTitle='Mensagens de contato';
require __DIR__.'/../_header.php';
$pdo=getPDO();$rows=[];$error='';
if($pdo){try{$rows=$pdo->query("SELECT id,nome,email,assunto,status,criado_em FROM mensagens_contato ORDER BY FIELD(status,'nova','lida','respondida','arquivada'), id DESC")->fetchAll();}catch(Throwable $e){error_log('[Açai Flow] Mensagens: '.$e->getMessage());$error='Não foi possível carregar as mensagens.';}}else{$error='Banco de dados indisponível.';}
?>
<div class="admin-heading"><div><span class="admin-kicker">Atendimento</span><h1>Mensagens de contato</h1><p>Receba e organize os formulários enviados pela página Contato.</p></div></div>
<div class="admin-kpis admin-kpis-3"><?php
$counts=['nova'=>0,'lida'=>0,'respondida'=>0]; foreach($rows as $r){if(isset($counts[$r['status']]))$counts[$r['status']]++;}
?><div class="kpi"><small>Novas</small><strong><?=$counts['nova']?></strong><span>Aguardando leitura</span></div><div class="kpi"><small>Lidas</small><strong><?=$counts['lida']?></strong><span>Em atendimento</span></div><div class="kpi"><small>Respondidas</small><strong><?=$counts['respondida']?></strong><span>Já tratadas</span></div></div>
<div class="admin-card">
<?php if($error):?><div class="empty-state"><?=e($error)?></div><?php elseif(!$rows):?><div class="empty-state"><strong>Nenhuma mensagem recebida.</strong><br><small>Quando alguém usar o formulário de Contato, a mensagem aparecerá aqui.</small></div>
<?php else:?><div class="table-wrap"><table class="data-table"><thead><tr><th>Data</th><th>Cliente</th><th>Assunto</th><th>Status</th><th>Ação</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=formatarData($r['criado_em'])?></td><td><strong><?=e($r['nome'])?></strong><small style="display:block;color:var(--muted)"><?=e($r['email'])?></small></td><td><?=e($r['assunto']?:'Sem assunto')?></td><td><span class="status-pill <?=match($r['status']){ 'nova'=>'status-amarelo','lida'=>'status-coral','respondida'=>'status-verde','arquivada'=>'status-neutro',default=>'status-neutro'}?>"><?=e(ucfirst($r['status']))?></span></td><td><a class="mini-btn" href="<?=url('admin/contatos/detalhes.php?id='.$r['id'])?>">Abrir</a></td></tr><?php endforeach;?></tbody></table></div><?php endif;?>
</div>
<?php require __DIR__.'/../_footer.php'; ?>
