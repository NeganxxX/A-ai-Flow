<?php
declare(strict_types=1);
require_once __DIR__.'/../config/seguranca.php';
exigirAdmin();
require_once __DIR__.'/../config/conexao.php';
require_once __DIR__.'/../includes/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarCsrf(post('csrf'))) {
    flash('erro','Formulário inválido.');
    redirect('admin/pedidos/index.php');
}

$pdo=getPDO();
if (!$pdo) {
    flash('erro','Banco indisponível.');
    redirect('admin/pedidos/index.php');
}

$id=(int)post('pedido_id');
$status=(string)post('status');
$allowed=['recebido','confirmado','preparando','pronto','saiu_entrega','entregue','cancelado'];
if($id<=0 || !in_array($status,$allowed,true)) {
    flash('erro','Pedido ou status inválido.');
    redirect('admin/pedidos/index.php');
}

try {
    $pdo->beginTransaction();
    $st=$pdo->prepare('SELECT status FROM pedidos WHERE id=? FOR UPDATE');
    $st->execute([$id]);
    $current=$st->fetchColumn();
    if($current===false) throw new RuntimeException('Pedido não encontrado.');

    if($current!==$status) {
        $st=$pdo->prepare('UPDATE pedidos SET status=?,atualizado_em=NOW() WHERE id=?');
        $st->execute([$status,$id]);

        $history=$pdo->prepare('INSERT INTO pedido_status_historico(pedido_id,status,mensagem,alterado_por_admin_id) VALUES(?,?,?,?)');
        $history->execute([$id,$status,'Status alterado para '.statusLabel($status).'.',(int)$_SESSION['admin_id']]);

        registrarLogAdmin($pdo,(int)$_SESSION['admin_id'],'alterar_status','pedidos',$id,'Status: '.$current.' -> '.$status);
    }

    $pdo->commit();
    flash('sucesso','Status do pedido atualizado.');
} catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    flash('erro','Não foi possível atualizar o pedido.');
}

redirect('admin/pedidos/detalhes.php?id='.$id);
