<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
$pageTitle='Mensagem de contato';
require __DIR__.'/../_header.php';
$pdo=getPDO();$id=(int)($_GET['id']??0);$row=null;$error='';
if($pdo){try{$st=$pdo->prepare('SELECT * FROM mensagens_contato WHERE id=? LIMIT 1');$st->execute([$id]);$row=$st->fetch();if($row && $row['status']==='nova'){ $up=$pdo->prepare("UPDATE mensagens_contato SET status='lida', atualizado_em=NOW() WHERE id=?");$up->execute([$id]);$row['status']='lida';}}catch(Throwable $e){error_log('[Açai Flow] Mensagem detalhe: '.$e->getMessage());$error='Não foi possível abrir a mensagem.';}}
?>
<div class="admin-heading"><div><span class="admin-kicker">Contato</span><h1>Mensagem #<?=e($id)?></h1><p>Recebida pelo formulário da página Contato.</p></div><a class="admin-btn secondary" href="<?=url('admin/contatos/index.php')?>">← Mensagens</a></div>
<?php if($error):?><div class="admin-card admin-alert-card"><?=e($error)?></div><?php elseif(!$row):?><div class="admin-card"><div class="empty-state">Mensagem não encontrada.</div></div><?php else:?>
<div class="dashboard-grid"><section class="admin-card"><div class="admin-card-head"><div><h2><?=e($row['assunto']?:'Mensagem sem assunto')?></h2><p><?=formatarData($row['criado_em'])?></p></div><span class="status-pill status-coral"><?=e(ucfirst($row['status']))?></span></div><div class="contact-message-body"><?=nl2br(e($row['mensagem']))?></div></section><section class="admin-card"><h2>Remetente</h2><div class="admin-contact-profile"><strong><?=e($row['nome'])?></strong><a href="mailto:<?=e($row['email'])?>"><?=e($row['email'])?></a></div><hr style="border:0;border-top:1px solid var(--borda);margin:18px 0"><form action="<?=url('admin/contatos/status.php')?>" method="post"><input type="hidden" name="csrf" value="<?=csrfToken()?>"><input type="hidden" name="id" value="<?=$row['id']?>"><label class="form-group"><span style="display:block;margin-bottom:6px;font-size:.62rem;font-weight:800">STATUS</span><select class="form-control" name="status"><option value="nova" <?=$row['status']==='nova'?'selected':''?>>Nova</option><option value="lida" <?=$row['status']==='lida'?'selected':''?>>Lida</option><option value="respondida" <?=$row['status']==='respondida'?'selected':''?>>Respondida</option><option value="arquivada" <?=$row['status']==='arquivada'?'selected':''?>>Arquivada</option></select></label><button class="admin-btn" style="margin-top:12px">SALVAR STATUS</button></form></section></div>
<?php endif; ?>
<?php require __DIR__.'/../_footer.php'; ?>
