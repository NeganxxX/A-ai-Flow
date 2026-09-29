<?php
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
$pageTitle='Promoções';
require __DIR__.'/../_header.php';
$pdo=getPDO();
$rows=[];
$hasOfferSchedule=$pdo&&colunaExiste($pdo,'produtos','oferta_inicio')&&colunaExiste($pdo,'produtos','oferta_fim');
if($pdo&&tableExists($pdo,'produtos')){
    $select=$hasOfferSchedule?'p.id,p.nome,p.preco,p.promocao,p.oferta_inicio,p.oferta_fim,p.ativo,c.nome categoria':'p.id,p.nome,p.preco,p.promocao,p.ativo,c.nome categoria';
    $rows=$pdo->query("SELECT $select FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.promocao=1 ORDER BY p.id DESC")->fetchAll();
}
function statusOfertaAdminPromo(array $r): string {
    $now=time();
    if(!empty($r['oferta_inicio']) && strtotime((string)$r['oferta_inicio'])>$now) return 'Agendada';
    if(!empty($r['oferta_fim']) && strtotime((string)$r['oferta_fim'])<$now) return 'Encerrada';
    return !empty($r['ativo']) ? 'Ativa agora' : 'Produto inativo';
}
?>
<div class="admin-heading"><div><h1>Promoções</h1><p>Marque produtos como promoção e, quando necessário, programe o período em que aparecem no cardápio.</p></div></div>
<div class="admin-card">
    <?php if(!$hasOfferSchedule): ?><div class="notice" style="margin-bottom:14px">A programação por período depende da migração <strong>banco/migracao_periodo_ofertas.sql</strong>.</div><?php endif; ?>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Produto</th><th>Categoria</th><th>Preço</th><th>Status</th><th>Período</th><th>Ação</th></tr></thead><tbody>
    <?php foreach($rows as $r): ?>
        <tr>
            <td><?=e($r['nome'])?></td>
            <td><?=e($r['categoria']??'')?></td>
            <td><?=mb_strtolower(trim((string)$r['nome']))==='combo casal' ? 'Conforme escolhas' : formatarMoeda((float)$r['preco'])?></td>
            <td><?=e(statusOfertaAdminPromo($r))?></td>
            <td class="admin-offer-period-cell"><?php if(!empty($r['oferta_inicio'])||!empty($r['oferta_fim'])): ?><?=!empty($r['oferta_inicio'])?e(formatarData((string)$r['oferta_inicio'])):'Agora'?> → <?=!empty($r['oferta_fim'])?e(formatarData((string)$r['oferta_fim'])):'sem fim'?><?php else: ?>Sem prazo<?php endif;?></td>
            <td><div class="admin-table-actions"><a class="mini-btn" href="<?=url('admin/produtos/editar.php?id='.(int)$r['id'])?>">Editar</a><form action="<?=url('admin/promocoes/toggle.php')?>" method="post" style="display:inline"><input type="hidden" name="csrf" value="<?=csrfToken()?>"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="mini-btn" type="submit">Remover</button></form></div></td>
        </tr>
    <?php endforeach; if(!$rows): ?><tr><td colspan="6">Nenhuma promoção cadastrada.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php require __DIR__.'/../_footer.php'; ?>
