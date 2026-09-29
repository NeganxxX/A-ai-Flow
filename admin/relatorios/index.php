<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
$pageTitle='Relatórios';
require __DIR__.'/../_header.php';

$pdo=getPDO();
$period=$_GET['periodo']??'esta_semana';
$allowed=['esta_semana','ultima_semana','ultimas_4_semanas','ultimos_6_meses','ultimo_ano'];
if(!in_array($period,$allowed,true))$period='esta_semana';

$now=new DateTimeImmutable('now');
$today=$now->setTime(0,0,0);
$periodLabels=[
    'esta_semana'=>'Nesta semana',
    'ultima_semana'=>'Última semana',
    'ultimas_4_semanas'=>'Últimas 4 semanas',
    'ultimos_6_meses'=>'Últimos 6 meses',
    'ultimo_ano'=>'Último ano'
];

switch($period){
    case 'ultima_semana':
        $start=$today->modify('monday last week');
        $end=$start->modify('+7 days');
        break;
    case 'ultimas_4_semanas':
        $start=$today->modify('-27 days');
        $end=$now;
        break;
    case 'ultimos_6_meses':
        $start=$today->modify('-6 months');
        $end=$now;
        break;
    case 'ultimo_ano':
        $start=$today->modify('-1 year');
        $end=$now;
        break;
    default:
        $start=$today->modify('monday this week');
        $end=$now;
        break;
}
$startSql=$start->format('Y-m-d H:i:s');
$endSql=$end->format('Y-m-d H:i:s');
$label=$periodLabels[$period];

$sales=0.0;$orders=0;$newClients=0;$buyers=0;$ticket=0.0;$cancelled=0;$topProducts=[];$series=[];$error='';
if(!$pdo){$error='Banco de dados indisponível.';}else{
    try{
        $st=$pdo->prepare("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE criado_em>=? AND criado_em<? AND status<>'cancelado'");$st->execute([$startSql,$endSql]);$sales=(float)$st->fetchColumn();
        $st=$pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE criado_em>=? AND criado_em<? AND status<>'cancelado'");$st->execute([$startSql,$endSql]);$orders=(int)$st->fetchColumn();
        $st=$pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE criado_em>=? AND criado_em<? AND status='cancelado'");$st->execute([$startSql,$endSql]);$cancelled=(int)$st->fetchColumn();
        $st=$pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE criado_em>=? AND criado_em<? AND ativo=1");$st->execute([$startSql,$endSql]);$newClients=(int)$st->fetchColumn();
        $st=$pdo->prepare("SELECT COUNT(DISTINCT usuario_id) FROM pedidos WHERE criado_em>=? AND criado_em<? AND status<>'cancelado'");$st->execute([$startSql,$endSql]);$buyers=(int)$st->fetchColumn();
        $ticket=$orders>0?$sales/$orders:0.0;

        $top=$pdo->prepare("SELECT ip.nome_produto, SUM(ip.quantidade) quantidade, COALESCE(SUM(ip.subtotal),0) valor FROM itens_pedido ip INNER JOIN pedidos p ON p.id=ip.pedido_id WHERE p.criado_em>=? AND p.criado_em<? AND p.status<>'cancelado' GROUP BY ip.nome_produto ORDER BY quantidade DESC, valor DESC LIMIT 6");
        $top->execute([$startSql,$endSql]);$topProducts=$top->fetchAll();

        if($period==='esta_semana'||$period==='ultima_semana'){
            $days=$start;
            $limitEnd=$end;
            while($days<$limitEnd){
                $dKey=$days->format('Y-m-d');
                $series[$dKey]=['label'=>$days->format('d/m'),'sales'=>0.0,'orders'=>0];
                $days=$days->modify('+1 day');
            }
            $st=$pdo->prepare("SELECT DATE(criado_em) dia, COALESCE(SUM(total),0) sales, COUNT(*) orders FROM pedidos WHERE criado_em>=? AND criado_em<? AND status<>'cancelado' GROUP BY DATE(criado_em) ORDER BY dia");$st->execute([$startSql,$endSql]);
            foreach($st->fetchAll() as $row){$k=(string)$row['dia'];if(isset($series[$k])){$series[$k]['sales']=(float)$row['sales'];$series[$k]['orders']=(int)$row['orders'];}}
        }elseif($period==='ultimas_4_semanas'){
            for($i=3;$i>=0;$i--){
                $weekStart=$today->modify('-'.($i*7).' days')->modify('monday this week');
                $key=$weekStart->format('o-\\W').$weekStart->format('W');
                $series[$key]=['label'=>$weekStart->format('d/m'),'sales'=>0.0,'orders'=>0];
            }
            $st=$pdo->prepare("SELECT YEARWEEK(criado_em,1) wk, COALESCE(SUM(total),0) sales, COUNT(*) orders FROM pedidos WHERE criado_em>=? AND criado_em<? AND status<>'cancelado' GROUP BY YEARWEEK(criado_em,1) ORDER BY wk");
            $st->execute([$startSql,$endSql]);
            foreach($st->fetchAll() as $row){
                $year=(int)substr((string)$row['wk'],0,4);
                $week=(int)substr((string)$row['wk'],4);
                $weekStart=(new DateTimeImmutable())->setISODate($year,$week,1);
                $key=$weekStart->format('o-\\W').$weekStart->format('W');
                if(isset($series[$key])){
                    $series[$key]['sales']=(float)$row['sales'];
                    $series[$key]['orders']=(int)$row['orders'];
                }
            }
        }else{
            $months=[];$cursor=$start->modify('first day of this month');$endMonth=$end->modify('first day of this month');
            while($cursor <= $endMonth){$key=$cursor->format('Y-m');$months[$key]=['label'=>$cursor->format('m/Y'),'sales'=>0.0,'orders'=>0];$cursor=$cursor->modify('+1 month');}
            $st=$pdo->prepare("SELECT DATE_FORMAT(criado_em,'%Y-%m') mes, COALESCE(SUM(total),0) sales, COUNT(*) orders FROM pedidos WHERE criado_em>=? AND criado_em<? AND status<>'cancelado' GROUP BY DATE_FORMAT(criado_em,'%Y-%m') ORDER BY mes");$st->execute([$startSql,$endSql]);
            foreach($st->fetchAll() as $row){$k=(string)$row['mes'];if(isset($months[$k])){$months[$k]['sales']=(float)$row['sales'];$months[$k]['orders']=(int)$row['orders'];}}
            $series=$months;
        }
    }catch(Throwable $e){
        error_log('[Açai Flow] Relatórios: '.$e->getMessage());
        $error='Não foi possível carregar os relatórios agora.';
    }
}
$seriesValues=array_column($series,'sales');$maxSeries=max($seriesValues?:[0]);
$topMax=max(array_column($topProducts,'quantidade')?:[1]);
?>
<div class="admin-heading report-heading"><div><span class="admin-kicker">Análise de desempenho</span><h1>Relatórios</h1><p>Filtre o período e veja vendas, pedidos e clientes com números que ajudam na tomada de decisão.</p></div></div>

<section class="admin-card report-filter-card">
    <form method="get" class="report-filter-form">
        <div><label for="periodo">Período analisado</label><select id="periodo" name="periodo"><option value="esta_semana" <?=$period==='esta_semana'?'selected':''?>>Nesta semana</option><option value="ultima_semana" <?=$period==='ultima_semana'?'selected':''?>>Última semana</option><option value="ultimas_4_semanas" <?=$period==='ultimas_4_semanas'?'selected':''?>>Últimas 4 semanas</option><option value="ultimos_6_meses" <?=$period==='ultimos_6_meses'?'selected':''?>>Últimos 6 meses</option><option value="ultimo_ano" <?=$period==='ultimo_ano'?'selected':''?>>1 ano</option></select></div>
        <div class="report-filter-period"><span>Período:</span><strong><?=e($start->format('d/m/Y'))?> — <?=e(($end->modify('-1 second'))->format('d/m/Y'))?></strong></div>
        <button class="admin-btn" type="submit">Aplicar filtro</button>
    </form>
</section>

<?php if($error): ?><div class="admin-card admin-alert-card"><?=e($error)?></div><?php endif; ?>

<div class="admin-kpis report-kpis">
    <div class="kpi"><small>Vendas acumuladas</small><strong><?=formatarMoeda($sales)?></strong><span><?=e($label)?></span></div>
    <div class="kpi"><small>Pedidos</small><strong><?=$orders?></strong><span><?=$cancelled?> cancelado(s)</span></div>
    <div class="kpi"><small>Novos clientes</small><strong><?=$newClients?></strong><span>Cadastros ativos no período</span></div>
    <div class="kpi"><small>Clientes compradores</small><strong><?=$buyers?></strong><span>Clientes que fizeram pedidos</span></div>
    <div class="kpi"><small>Ticket médio</small><strong><?=formatarMoeda($ticket)?></strong><span>Vendas ÷ pedidos</span></div>
</div>

<div class="admin-dashboard-grid report-chart-grid">
    <section class="admin-card dashboard-chart-card">
        <div class="admin-card-head"><div><h2>Vendas no período</h2><p><?=e($label)?></p></div><span class="chart-summary"><?=formatarMoeda($sales)?></span></div>
        <?php if($series): ?><div class="report-bars">
            <?php foreach($series as $point): $height=$maxSeries>0?max(7,($point['sales']/$maxSeries)*100):7; ?><div class="report-bar-item" title="<?=e($point['label'])?> — <?=formatarMoeda((float)$point['sales'])?>"><div class="report-bar" style="height:<?=number_format($height,2,'.','')?>%"><span><?=formatarMoeda((float)$point['sales'])?></span></div><small><?=e($point['label'])?></small></div><?php endforeach; ?>
        </div><?php else: ?><div class="empty-state">Não há vendas no período selecionado.</div><?php endif; ?>
    </section>
    <section class="admin-card report-summary-card">
        <div class="admin-card-head"><div><h2>Leitura rápida</h2><p>Indicadores para apoiar a análise do período.</p></div></div>
        <div class="report-summary-list">
            <div><span>Valor por pedido</span><strong><?=formatarMoeda($ticket)?></strong></div>
            <div><span>Pedidos por cliente</span><strong><?= $buyers>0 ? number_format($orders/$buyers,1,',','.') : '0,0' ?></strong></div>
            <div><span>Taxa de cancelamento</span><strong><?= ($orders+$cancelled)>0 ? number_format(($cancelled/($orders+$cancelled))*100,1,',','.') : '0,0' ?>%</strong></div>
        </div>
    </section>
</div>

<section class="admin-card">
    <div class="admin-card-head"><div><h2>Produtos mais vendidos</h2><p>Ranking do período selecionado.</p></div></div>
    <?php if($topProducts): ?><div class="rank-list report-rank-list">
        <?php foreach($topProducts as $idx=>$item): ?><div class="rank-row"><span class="rank-number"><?=($idx+1)?></span><div><strong><?=e($item['nome_produto'])?></strong><small><?=e((int)$item['quantidade'])?> unidade(s) • <?=formatarMoeda((float)$item['valor'])?></small></div><div class="rank-bar"><span style="width:<?=number_format(((int)$item['quantidade']/$topMax)*100,2,'.','')?>%"></span></div></div><?php endforeach; ?>
    </div><?php else: ?><div class="empty-state">Não há itens vendidos no período selecionado.</div><?php endif; ?>
</section>
<?php require __DIR__.'/../_footer.php'; ?>
