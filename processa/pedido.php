<?php
declare(strict_types=1);
require_once __DIR__.'/../config/seguranca.php';
require_once __DIR__.'/../config/conexao.php';
require_once __DIR__.'/../includes/funcoes.php';
require_once __DIR__.'/../config/limites.php';
require_once __DIR__.'/../config/catalogo.php';

exigirLogin();
if($_SERVER['REQUEST_METHOD']!=='POST'||!validarCsrf(post('csrf'))){flash('erro','Não foi possível validar o pedido.');redirect('pedido.php');}
$pdo=getPDO();
if(!$pdo){flash('erro','Banco de dados indisponível.');redirect('pedido.php');}

$cartJson=(string)post('cart_json');$cart=json_decode($cartJson,true);
if(!is_array($cart)||count($cart)<1||count($cart)>50){flash('erro','Carrinho inválido ou vazio.');redirect('pedido.php');}

$forma=(string)post('forma_pagamento');
$formas=['dinheiro','pix','credito','debito'];
if(!in_array($forma,$formas,true)){flash('erro','Selecione uma forma de pagamento.');redirect('pedido.php');}

$telefone=trim((string)post('telefone'));$cep=trim((string)post('cep'));$rua=trim((string)post('rua'));$numero=trim((string)post('numero'));$complemento=trim((string)post('complemento'));$bairro=trim((string)post('bairro'));$cidade=trim((string)post('cidade'));$observacao=trim((string)post('observacao'));$observacaoEntrega=trim((string)post('observacao_entrega'));$troco=post('troco_para','');
if($telefone===''||$cep===''||$rua===''||$numero===''||$bairro===''||$cidade===''){flash('erro','Preencha os dados obrigatórios de entrega.');redirect('pedido.php');}
if($forma==='dinheiro' && $troco!=='' && (float)$troco<0){flash('erro','Informe um valor de troco válido.');redirect('pedido.php');}

try{
    $pdo->beginTransaction();
    $customer=$pdo->prepare('SELECT id,nome FROM usuarios WHERE id=? AND ativo=1 LIMIT 1');$customer->execute([$_SESSION['cliente_id']]);$user=$customer->fetch();
    if(!$user) throw new RuntimeException('Sua sessão de cliente expirou. Faça login novamente.');

    $total=0.0;$resolved=[];
    foreach($cart as $item){
        $qty=max(1,min(20,(int)($item['quantity']??1)));
        $productId=!empty($item['product_id'])?(int)$item['product_id']:null;
        if($productId){
            $st=$pdo->prepare('SELECT p.id,p.nome,p.preco,p.estoque,p.ativo,p.imagem,c.nome categoria_nome FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? FOR UPDATE');$st->execute([$productId]);$product=$st->fetch();
            if(!$product||!(int)$product['ativo']) throw new RuntimeException('Um dos produtos não está mais disponível.');
            if((int)$product['estoque']<$qty) throw new RuntimeException('Estoque insuficiente para: '.$product['nome'].'.');
            $unit=(float)$product['preco'];$name=$product['nome'];$image=(string)($product['imagem']??($item['image']??''));
            $personalization=$item['personalization']??null;

            $isCombo=mb_strtolower(trim((string)($product['categoria_nome']??'')))==='combos';
            if($isCombo){
                $comboConfig=getComboConfiguracao($pdo,$productId,$product);
                if(!$comboConfig||empty($comboConfig['porcoes'])) throw new RuntimeException('Este combo ainda não foi configurado para montagem.');
                $requestedPortions=is_array($personalization['porcoes']??null)?$personalization['porcoes']:[];
                if(count($requestedPortions)!==count($comboConfig['porcoes'])) throw new RuntimeException('Escolha todas as porções do combo antes de continuar.');

                $sizesById=[];
                foreach(getMonteTamanhos($pdo) as $sizeRow){$sizesById[(int)$sizeRow['id']]=$sizeRow;}

                $allOptionIds=[];
                foreach($requestedPortions as $portion){
                    foreach((array)($portion['opcoes']??[]) as $op){ if(isset($op['id'])) $allOptionIds[]=(int)$op['id']; }
                }
                $allOptionIds=array_values(array_unique($allOptionIds));
                $optionById=[];
                if($allOptionIds){
                    $marks=implode(',',array_fill(0,count($allOptionIds),'?'));
                    $stOpt=$pdo->prepare("SELECT id,tipo,nome,preco_adicional FROM opcoes WHERE ativo=1 AND id IN ($marks)");$stOpt->execute($allOptionIds);$optionRows=$stOpt->fetchAll();
                    if(count($optionRows)!==count($allOptionIds)) throw new RuntimeException('Um dos complementos selecionados não está disponível.');
                    foreach($optionRows as $op)$optionById[(int)$op['id']]=$op;
                }

                $normalizedPortions=[];$comboExtra=0.0;$comboBase=0.0;
                $isDynamicCasal = mb_strtolower(trim((string)$product['nome'])) === 'combo casal';
                foreach($comboConfig['porcoes'] as $index=>$rule){
                    $requested=$requestedPortions[$index]??null;
                    $sizeId=(int)($requested['tamanho_id']??0);
                    $size=$sizesById[$sizeId]??null;
                    if(!$size) throw new RuntimeException('Uma das opções de tamanho do combo não está disponível.');
                    if(!in_array($sizeId,array_map('intval',(array)($rule['tamanhos']??[])),true) || !in_array((string)$size['tipo'],(array)($rule['tipos']??[]),true)) throw new RuntimeException('A configuração deste combo não permite o formato/tamanho escolhido na porção '.($index+1).'.');
                    if($isDynamicCasal) $comboBase += (float)$size['preco'];

                    $selectedIds=[];
                    foreach((array)($requested['opcoes']??[]) as $op){
                        $oid=(int)($op['id']??0);
                        if(!$oid || !isset($optionById[$oid])) throw new RuntimeException('Um complemento do combo não está disponível.');
                        if(!in_array($oid,$selectedIds,true)) $selectedIds[]=$oid;
                    }
                    $counts=['fruta'=>0,'creme'=>0,'adicional'=>0,'calda'=>0,'acompanhamento'=>0];
                    $normalizedOptions=[];
                    foreach($selectedIds as $oid){
                        $op=$optionById[$oid];$tipo=(string)$op['tipo'];
                        if(array_key_exists($tipo,$counts)){
                            $counts[$tipo]++;
                            $limit=(int)($rule['limites'][$tipo]??0);
                            if($counts[$tipo]>$limit) throw new RuntimeException('Limite excedido para '.ucfirst($tipo).' na porção '.($index+1).'.');
                        }
                        if($tipo==='adicional' && abs((float)$op['preco_adicional']-2.00)>0.001) throw new RuntimeException('O preço dos adicionais está inconsistente.');
                        $comboExtra+=(float)$op['preco_adicional'];
                        $normalizedOptions[]=['id'=>(int)$op['id'],'tipo'=>$tipo,'nome'=>$op['nome'],'preco'=>(float)$op['preco_adicional']];
                    }
                    $limits=[
                        'fruta'=>(int)($rule['limites']['fruta']??0),
                        'creme'=>(int)($rule['limites']['creme']??0),
                        'adicional'=>(int)($rule['limites']['adicional']??0),
                        'calda'=>(int)($rule['limites']['calda']??0),
                        'acompanhamento'=>(int)($rule['limites']['acompanhamento']??0)
                    ];
                    $normalizedPortions[]=[
                        'nome'=>(string)($rule['nome']??('Porção '.($index+1))),
                        'tamanho'=>$size['nome'],
                        'tamanho_id'=>(int)$size['id'],
                        'tipo'=>$size['tipo'],
                        'volume_ml'=>isset($size['volume_ml'])&&$size['volume_ml']!==null?(int)$size['volume_ml']:null,
                        'limites'=>$limits,
                        'opcoes'=>$normalizedOptions
                    ];
                }
                $unit = $isDynamicCasal ? ($comboBase + $comboExtra) : ((float)$product['preco'] + $comboExtra);
                $personalization=[
                    'combo'=>true,
                    'combo_id'=>$productId,
                    'porcoes'=>$normalizedPortions,
                    'observacao'=>(string)($personalization['observacao']??'')
                ];
            }
            $pdo->prepare('UPDATE produtos SET estoque=estoque-? WHERE id=?')->execute([$qty,$productId]);
        } else {
            $sizeId=(int)($item['size_id']??0);
            try {
                $st=$pdo->prepare('SELECT id,tipo,nome,preco,volume_ml,limite_frutas,limite_cremes,limite_adicionais,limite_caldas,limite_acompanhamentos FROM tamanhos WHERE id=? AND ativo=1');
                $st->execute([$sizeId]);
                $size=$st->fetch();
            } catch (Throwable $sizeSchemaError) {
                $st=$pdo->prepare('SELECT id,tipo,nome,preco FROM tamanhos WHERE id=? AND ativo=1');
                $st->execute([$sizeId]);
                $size=$st->fetch();
                if($size){
                    $cupLimits = ['180ml'=>[1,1,1,1,1],'200ml'=>[1,1,1,1,2],'300ml'=>[1,1,1,1,2],'400ml'=>[1,1,1,1,3],'500ml'=>[1,1,1,1,3],'700ml'=>[1,1,1,2,4]];
                    $barLimits = ['PP'=>[1,1,1,1,3],'P'=>[2,1,1,2,4],'M'=>[2,1,1,2,4],'G'=>[3,1,1,3,5],'GG'=>[4,1,1,3,5],'XG'=>[5,1,1,4,6]];
                    $limits = $size['tipo']==='copo' ? ($cupLimits[$size['nome']] ?? [1,1,1,1,1]) : ($barLimits[$size['nome']] ?? [1,1,1,1,1]);
                    $size['volume_ml'] = $size['tipo']==='copo' ? (int)preg_replace('/[^0-9]/','',$size['nome']) : (['PP'=>500,'P'=>700,'M'=>1000,'G'=>1500,'GG'=>2000,'XG'=>3000][$size['nome']] ?? null);
                    [$size['limite_frutas'],$size['limite_cremes'],$size['limite_adicionais'],$size['limite_caldas'],$size['limite_acompanhamentos']] = $limits;
                }
            }
            if(!$size) throw new RuntimeException('O tamanho selecionado não está disponível.');

            $optionIds=[];
            if(isset($item['personalization']['opcoes']) && is_array($item['personalization']['opcoes'])) {
                foreach($item['personalization']['opcoes'] as $op) if(isset($op['id'])) $optionIds[]=(int)$op['id'];
            }
            $optionIds=array_values(array_unique($optionIds));
            $optionRows=[];
            $extra=0.0;
            if($optionIds){
                $marks=implode(',',array_fill(0,count($optionIds),'?'));
                $st=$pdo->prepare("SELECT id,tipo,nome,preco_adicional FROM opcoes WHERE ativo=1 AND id IN ($marks)");
                $st->execute($optionIds);
                $optionRows=$st->fetchAll();
                if(count($optionRows)!==count($optionIds)) throw new RuntimeException('Um dos complementos selecionados não está disponível.');
            }

            [$limFruta,$limCreme,$limAdicional,$limCalda,$limAcomp] = limitesPersonalizacao((string)$size['tipo'], (string)$size['nome']);
            $limits=[
                'fruta'=>$limFruta,
                'creme'=>$limCreme,
                'adicional'=>$limAdicional,
                'calda'=>$limCalda,
                'acompanhamento'=>$limAcomp
            ];
            $counts=['fruta'=>0,'creme'=>0,'adicional'=>0,'calda'=>0,'acompanhamento'=>0];
            foreach($optionRows as $op){
                $tipo=(string)$op['tipo'];
                if(array_key_exists($tipo,$counts)){
                    $counts[$tipo]++;
                    if($counts[$tipo]>$limits[$tipo]) throw new RuntimeException('Limite excedido para '.ucfirst($tipo).'. O tamanho '.$size['nome'].' permite no máximo '.$limits[$tipo].'.');
                }
                if($tipo==='adicional' && abs((float)$op['preco_adicional']-2.00)>0.001) throw new RuntimeException('O preço dos adicionais está inconsistente.');
                $extra+=(float)$op['preco_adicional'];
            }

            $unit=(float)$size['preco']+$extra;
            $name='Monte seu Açaí — '.($size['tipo']==='barca'?'Barca ':'').$size['nome'];
            $image = $size['tipo'] === 'barca' ? 'img/produtos/acai-barca.png' : 'img/produtos/acai-copo-real-2x.png';
            $personalization=[
                'tamanho'=>$size['nome'],
                'tamanho_id'=>(int)$size['id'],
                'tipo'=>$size['tipo'],
                'volume_ml'=>$size['volume_ml']!==null?(int)$size['volume_ml']:null,
                'limites'=>$limits,
                'opcoes'=>array_map(fn($op)=>['id'=>(int)$op['id'],'tipo'=>$op['tipo'],'nome'=>$op['nome'],'preco'=>(float)$op['preco_adicional']],$optionRows),
                'observacao'=>(string)($item['personalization']['observacao']??'')
            ];
        }
        $subtotal=$unit*$qty;$total+=$subtotal;$resolved[]=['product_id'=>$productId,'name'=>$name,'unit'=>$unit,'qty'=>$qty,'subtotal'=>$subtotal,'personalization'=>$personalization,'image'=>$image];
    }
    $taxaEntrega = 0.00;
    if (tableExists($pdo,'pedidos') && !colunaExiste($pdo,'pedidos','observacao_entrega')) {
        throw new RuntimeException('A estrutura do banco precisa ser atualizada. Execute banco/migracao_observacoes_contato.sql.');
    }
    $insert=$pdo->prepare('INSERT INTO pedidos(usuario_id,nome_cliente,telefone,cep,rua,numero,complemento,bairro,cidade,observacao,observacao_entrega,forma_pagamento,pagamento_status,troco_para,subtotal,taxa_entrega,total,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $insert->execute([(int)$user['id'],$user['nome'],$telefone,$cep,$rua,$numero,$complemento,$bairro,$cidade,$observacao,$observacaoEntrega,$forma,'pendente',($forma==='dinheiro'&&$troco!=='' ? (float)$troco : null),$total,$taxaEntrega,$total,'recebido']);
    $pedidoId=(int)$pdo->lastInsertId();
    $history=$pdo->prepare('INSERT INTO pedido_status_historico(pedido_id,status,mensagem) VALUES(?,?,?)');
    $history->execute([$pedidoId,'recebido','Pedido recebido pelo sistema.']);
    $itemInsert=$pdo->prepare('INSERT INTO itens_pedido(pedido_id,produto_id,nome_produto,preco_unitario,quantidade,subtotal,personalizacao_json) VALUES(?,?,?,?,?,?,?)');
    foreach($resolved as $row)$itemInsert->execute([$pedidoId,$row['product_id'],$row['name'],$row['unit'],$row['qty'],$row['subtotal'],json_encode($row['personalization'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
    $pdo->commit();
    flash('sucesso','Pedido #'.$pedidoId.' recebido. Acompanhe o status na sua área do cliente.');
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url='.e(url('cliente/pedido-detalhes.php?id='.$pedidoId)).'"><title>Pedido confirmado</title></head><body></body></html>';
    exit;
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('erro',$e->getMessage());redirect('pedido.php');}
