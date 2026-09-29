<?php
require_once __DIR__.'/../../config/seguranca.php';
require_once __DIR__.'/../../config/catalogo.php';
exigirAdmin();
$pageTitle='Combos';
require __DIR__.'/../_header.php';
$pdo=getPDO();$rows=[];
$hasOfferSchedule=$pdo&&colunaExiste($pdo,'produtos','oferta_inicio')&&colunaExiste($pdo,'produtos','oferta_fim');
$hasComboConfig=$pdo&&tableExists($pdo,'combo_configuracoes');
$comboCategoriaId=0;
if($pdo&&tableExists($pdo,'categorias')){try{$comboCategoriaId=(int)$pdo->query("SELECT id FROM categorias WHERE nome='Combos' LIMIT 1")->fetchColumn();}catch(Throwable $e){$comboCategoriaId=0;}}
if($pdo&&tableExists($pdo,'produtos')){
    $select=$hasOfferSchedule?'p.id,p.nome,p.preco,p.estoque,p.ativo,p.oferta_inicio,p.oferta_fim':'p.id,p.nome,p.preco,p.estoque,p.ativo';
    $rows=$pdo->query("SELECT $select FROM produtos p INNER JOIN categorias c ON c.id=p.categoria_id WHERE c.nome='Combos' ORDER BY p.id DESC")->fetchAll();
    foreach($rows as &$comboRow){ $comboRow['config']=$pdo?getComboConfiguracao($pdo,(int)$comboRow['id'],$comboRow):null; } unset($comboRow);
}
function statusOfertaAdminCombo(array $r): string {
    $now=time();
    if(!empty($r['oferta_inicio']) && strtotime((string)$r['oferta_inicio'])>$now) return 'Agendado';
    if(!empty($r['oferta_fim']) && strtotime((string)$r['oferta_fim'])<$now) return 'Encerrado';
    return !empty($r['ativo']) ? 'Ativo agora' : 'Produto inativo';
}
?>
<div class="admin-heading"><div><h1>Combos</h1><p>Crie combos como produtos e programe quando eles devem aparecer no cardápio.</p></div><a class="admin-btn" href="<?=url('admin/produtos/cadastrar.php'.($comboCategoriaId?'?categoria='.$comboCategoriaId:''))?>">+ Novo combo</a></div>
<div class="admin-card">
    <?php if(!$hasOfferSchedule): ?><div class="notice" style="margin-bottom:14px">A programação por período depende da migração <strong>banco/migracao_periodo_ofertas.sql</strong>.</div><?php endif; ?>
    <?php if(!$hasComboConfig): ?><div class="notice" style="margin-bottom:14px">A montagem personalizada dos combos depende da migração <strong>banco/migracao_combos_personalizaveis.sql</strong>.</div><?php endif; ?>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Nome</th><th>Preço</th><th>Estoque</th><th>Status</th><th>Montagem</th><th>Período</th><th>Ação</th></tr></thead><tbody>
    <?php foreach($rows as $r): ?>
        <tr>
            <td><?=e($r['nome'])?></td><td><?=formatarMoeda((float)$r['preco'])?></td><td><?=e($r['estoque'])?></td><td><?=e(statusOfertaAdminCombo($r))?></td>
            <td><?=!empty($r['config']['porcoes'])?count($r['config']['porcoes']).' porção(ões)':'Não configurado'?></td><td class="admin-offer-period-cell"><?php if(!empty($r['oferta_inicio'])||!empty($r['oferta_fim'])): ?><?=!empty($r['oferta_inicio'])?e(formatarData((string)$r['oferta_inicio'])):'Agora'?> → <?=!empty($r['oferta_fim'])?e(formatarData((string)$r['oferta_fim'])):'sem fim'?><?php else: ?>Sem prazo<?php endif;?></td>
            <td><a class="mini-btn" href="<?=url('admin/produtos/editar.php?id='.(int)$r['id'])?>">Editar</a></td>
        </tr>
    <?php endforeach; if(!$rows):?><tr><td colspan="7">Nenhum combo cadastrado.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php require __DIR__.'/../_footer.php'; ?>
