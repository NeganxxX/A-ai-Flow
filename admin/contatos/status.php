<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
require_once __DIR__.'/../../config/conexao.php';
require_once __DIR__.'/../../includes/funcoes.php';
if($_SERVER['REQUEST_METHOD']!=='POST'||!validarCsrf(post('csrf'))){flash('erro','Formulário inválido.');redirect('admin/contatos/index.php');}
$pdo=getPDO();$id=(int)post('id');$status=(string)post('status');
$allowed=['nova','lida','respondida','arquivada'];
if(!$pdo||$id<=0||!in_array($status,$allowed,true)){flash('erro','Mensagem ou status inválido.');redirect('admin/contatos/index.php');}
try{$st=$pdo->prepare('UPDATE mensagens_contato SET status=?, atualizado_em=NOW() WHERE id=?');$st->execute([$status,$id]);registrarLogAdmin($pdo,(int)$_SESSION['admin_id'],'alterar_status','mensagens_contato',$id,'Status: '.$status);flash('sucesso','Status da mensagem atualizado.');}catch(Throwable $e){error_log('[Açai Flow] Status contato: '.$e->getMessage());flash('erro','Não foi possível atualizar a mensagem.');}
redirect('admin/contatos/detalhes.php?id='.$id);
