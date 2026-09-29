<?php
declare(strict_types=1);
require_once __DIR__.'/../config/seguranca.php';
exigirAdmin();
$pageTitle='Dashboard';
require __DIR__.'/_header.php';

$pdo=getPDO();
$ordersToday=0;
$salesToday=0.0;
$clients=0;
$ticketToday=0.0;
$recent=[];
$stock=[];
$sales7=[];
$statusData=[];
$topProducts=[];
$errorMessage='';

if(!$pdo){
    $errorMessage='Banco de dados indisponível.';
}else{
    try{
        $ordersToday=(int)$pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(criado_em)=CURDATE() AND status<>'cancelado'")->fetchColumn();
        $salesToday=(float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(criado_em)=CURDATE() AND status<>'cancelado'")->fetchColumn();
        $clients=(int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE ativo=1")->fetchColumn();
        $ticketToday=$ordersToday>0 ? $salesToday/$ordersToday : 0.0;

        $recent=$pdo->query("SELECT id,nome_cliente,total,status,criado_em FROM pedidos ORDER BY id DESC LIMIT 6")->fetchAll();
        $stock=$pdo->query("SELECT nome,estoque FROM produtos WHERE ativo=1 ORDER BY estoque ASC, nome ASC LIMIT 6")->fetchAll();

        $start7=(new DateTimeImmutable('today'))->modify('-6 days')->format('Y-m-d 00:00:00');
        $salesStmt=$pdo->prepare("SELECT DATE(criado_em) dia, COALESCE(SUM(total),0) total FROM pedidos WHERE criado_em>=? AND status<>'cancelado' GROUP BY DATE(criado_em) ORDER BY dia ASC");
        $salesStmt->execute([$start7]);
        foreach($salesStmt->fetchAll() as $row){$sales7[(string)$row['dia']]=(float)$row['total'];}

        $today=new DateTimeImmutable('today');
        for($i=6;$i>=0;$i--){
            $d=$today->modify('-'.$i.' days');
            $key=$d->format('Y-m-d');
            $sales7Chart[]=['label'=>$d->format('d/m'),'value'=>(float)($sales7[$key]??0)];
        }

        $statusRows=$pdo->query("SELECT status, COUNT(*) quantidade FROM pedidos WHERE criado_em>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY status ORDER BY quantidade DESC")->fetchAll();
        foreach($statusRows as $row){$statusData[]=['status'=>(string)$row['status'],'label'=>statusLabel((string)$row['status']),'count'=>(int)$row['quantidade']];}

        $topStmt=$pdo->query("SELECT ip.nome_produto, SUM(ip.quantidade) quantidade, COALESCE(SUM(ip.subtotal),0) valor FROM itens_pedido ip INNER JOIN pedidos p ON p.id=ip.pedido_id WHERE p.status<>'cancelado' AND p.criado_em>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY ip.nome_produto ORDER BY quantidade DESC, valor DESC LIMIT 5");
        $topProducts=$topStmt->fetchAll();
    }catch(Throwable $e){
        error_log('[Açai Flow] Dashboard: '.$e->getMessage());
        $errorMessage='Não foi possível carregar todos os dados do painel.';
    }
}

$sales7Chart=$sales7Chart??[];
$maxSales=max(array_column($sales7Chart,'value') ?: [0]);
$statusTotal=array_sum(array_column($statusData,'count'));
$statusPalette=['recebido'=>'#E1786E','confirmado'=>'#8E3B7A','preparando'=>'#C88D45','pronto'=>'#7A7646','saiu_entrega'=>'#4E8B78','entregue'=>'#6B0F32','cancelado'=>'#9A4B5C'];
?>
<div class="admin-heading dashboard-v2-heading">
    <div><span class="admin-kicker">Visão geral</span><h1>Dashboard</h1><p>Os principais indicadores da Açaí Flow, organizados para leitura rápida.</p></div>
    <div class="admin-actions"><a class="admin-btn" href="<?=url('admin/produtos/cadastrar.php')?>">+ Novo produto</a><a class="admin-btn secondary" href="<?=url('admin/relatorios/index.php')?>">Ver relatórios</a></div>
</div>

<?php if($errorMessage): ?><div class="admin-card admin-alert-card"><?=e($errorMessage)?></div><?php endif; ?>

<div class="admin-kpis dashboard-kpis">
    <div class="kpi"><small>Pedidos hoje</small><strong><?=$ordersToday?></strong><span>Não cancelados</span></div>
    <div class="kpi"><small>Vendas hoje</small><strong><?=formatarMoeda($salesToday)?></strong><span>Faturamento confirmado</span></div>
    <div class="kpi"><small>Clientes ativos</small><strong><?=$clients?></strong><span>Contas cadastradas</span></div>
    <div class="kpi"><small>Ticket médio hoje</small><strong><?=formatarMoeda($ticketToday)?></strong><span>Por pedido</span></div>
</div>

<div class="admin-dashboard-grid dashboard-main-grid">
    <section class="admin-card dashboard-chart-card">
        <div class="admin-card-head"><div><h2>Vendas — últimos 7 dias</h2><p>Faturamento diário, sem pedidos cancelados.</p></div><span class="chart-summary"><?=formatarMoeda(array_sum(array_column($sales7Chart,'value')))?></span></div>
        <div class="line-chart" aria-label="Gráfico de vendas dos últimos 7 dias">
            <?php foreach($sales7Chart as $point):
                $height=$maxSales>0 ? max(6,($point['value']/$maxSales)*100) : 6;
            ?>
                <div class="line-chart-point" title="<?=e($point['label'])?> — <?=formatarMoeda((float)$point['value'])?>">
                    <div class="line-chart-value" style="height:<?=e(number_format($height,2,'.',''))?>%"><span><?=formatarMoeda((float)$point['value'])?></span></div>
                    <small><?=e($point['label'])?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="admin-card dashboard-chart-card">
        <div class="admin-card-head"><div><h2>Status dos pedidos</h2><p>Distribuição dos pedidos nos últimos 30 dias.</p></div></div>
        <?php if($statusTotal>0): ?>
        <div class="status-chart-wrap">
            <div class="status-donut" style="background:conic-gradient(<?php $deg=0; $parts=[]; foreach($statusData as $item){$portion=($item['count']/$statusTotal)*360;$color=$statusPalette[$item['status']]??'#8E3B7A';$parts[]= $color.' '.$deg.'deg '.($deg+$portion).'deg';$deg+=$portion;} echo implode(',', $parts); ?>)"><span><?=$statusTotal?><small>pedidos</small></span></div>
            <div class="status-legend">
                <?php foreach($statusData as $item): ?><div><span class="status-dot" style="background:<?=e($statusPalette[$item['status']]??'#8E3B7A')?>"></span><span><?=e($item['label'])?></span><strong><?=$item['count']?></strong></div><?php endforeach; ?>
            </div>
        </div>
        <?php else: ?><div class="empty-state">Ainda não há pedidos suficientes para montar este gráfico.</div><?php endif; ?>
    </section>
</div>

<div class="admin-dashboard-grid dashboard-secondary-grid">
    <section class="admin-card">
        <div class="admin-card-head"><div><h2>Produtos mais vendidos</h2><p>Últimos 30 dias, por quantidade.</p></div><a class="mini-btn" href="<?=url('admin/produtos/index.php')?>">Cardápio</a></div>
        <?php if($topProducts): ?><div class="rank-list">
            <?php foreach($topProducts as $idx=>$item): ?><div class="rank-row"><span class="rank-number"><?=($idx+1)?></span><div><strong><?=e($item['nome_produto'])?></strong><small><?=e((int)$item['quantidade'])?> unidade(s) • <?=formatarMoeda((float)$item['valor'])?></small></div><div class="rank-bar"><span style="width:<?=e(number_format(((int)$item['quantidade']/max(1,(int)$topProducts[0]['quantidade']))*100,2,'.',''))?>%"></span></div></div><?php endforeach; ?>
        </div><?php else: ?><div class="empty-state">Ainda não há vendas de produtos para analisar.</div><?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card-head"><div><h2>Estoque que merece atenção</h2><p>Produtos ativos com menor quantidade.</p></div><a class="mini-btn" href="<?=url('admin/estoque/index.php')?>">Ver estoque</a></div>
        <?php if($stock): ?><div class="compact-stock-list">
            <?php foreach($stock as $s): $q=(int)$s['estoque']; ?><div class="compact-stock-row"><div><strong><?=e($s['nome'])?></strong><small><?=$q<=0?'Esgotado':($q<=2?'Estoque baixo':'Estoque normal')?></small></div><span class="stock-number <?=$q<=0?'out':($q<=2?'low':'ok')?>"><?=$q?></span></div><?php endforeach; ?>
        </div><?php else: ?><div class="empty-state">Nenhum produto ativo encontrado.</div><?php endif; ?>
    </section>
</div>

<section class="admin-card dashboard-recent-card">
    <div class="admin-card-head"><div><h2>Pedidos recentes</h2><p>Acompanhe rapidamente o que acabou de entrar.</p></div><a class="mini-btn" href="<?=url('admin/pedidos/index.php')?>">Ver todos</a></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Nº</th><th>Cliente</th><th>Valor</th><th>Status</th><th>Data/Hora</th><th></th></tr></thead><tbody>
    <?php foreach($recent as $o): ?><tr><td>#<?=e($o['id'])?></td><td><?=e($o['nome_cliente'])?></td><td><?=formatarMoeda((float)$o['total'])?></td><td><span class="status-pill <?=statusClasse($o['status'])?>"><?=e(statusLabel($o['status']))?></span></td><td><?=formatarData($o['criado_em'])?></td><td><a class="mini-btn" href="<?=url('admin/pedidos/detalhes.php?id='.$o['id'])?>">Detalhes</a></td></tr><?php endforeach; ?>
    <?php if(!$recent): ?><tr><td colspan="6">Nenhum pedido ainda.</td></tr><?php endif; ?></tbody></table></div>
</section>
<?php require __DIR__.'/_footer.php'; ?>
