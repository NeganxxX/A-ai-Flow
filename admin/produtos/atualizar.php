<?php
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
require_once __DIR__.'/../../includes/funcoes.php';
require_once __DIR__.'/../../config/conexao.php';
require_once __DIR__.'/../../config/catalogo.php';

if($_SERVER['REQUEST_METHOD']!=='POST'||!validarCsrf(post('csrf'))) redirect('admin/produtos/index.php');
$pdo=getPDO();$id=(int)post('id');
if(!$pdo||!$id){flash('erro','Produto inválido.');redirect('admin/produtos/index.php');}

function normalizarDataOfertaAtualizacao(?string $value): ?string {
    $value=trim((string)$value);
    if($value==='') return null;
    $date=DateTime::createFromFormat('Y-m-d\\TH:i',$value);
    if(!$date) throw new InvalidArgumentException('Data de exibição inválida.');
    return $date->format('Y-m-d H:i:s');
}

try{
    $inicio=normalizarDataOfertaAtualizacao(post('oferta_inicio'));
    $fim=normalizarDataOfertaAtualizacao(post('oferta_fim'));
    if($inicio!==null && $fim!==null && strtotime($fim)<strtotime($inicio)) throw new InvalidArgumentException('O fim da exibição deve ser posterior ao início.');
    $nomeProduto = trim((string)post('nome'));
    $preco = mb_strtolower($nomeProduto) === 'combo casal' ? 0.0 : (float)post('preco');

    $st=$pdo->prepare('SELECT imagem FROM produtos WHERE id=?');$st->execute([$id]);$old=$st->fetchColumn();
    if($old===false){flash('erro','Produto não encontrado.');redirect('admin/produtos/index.php');}
    $image=processarUploadImagem($_FILES['imagem']??[],'produtos')??$old;
    $hasOfferSchedule=colunaExiste($pdo,'produtos','oferta_inicio')&&colunaExiste($pdo,'produtos','oferta_fim');
    if($hasOfferSchedule){
        $st=$pdo->prepare('UPDATE produtos SET categoria_id=?,nome=?,descricao=?,preco=?,imagem=?,estoque=?,destaque=?,promocao=?,oferta_inicio=?,oferta_fim=?,ativo=? WHERE id=?');
        $st->execute([(int)post('categoria_id'),trim((string)post('nome')),trim((string)post('descricao')),$preco,$image,(int)post('estoque'),isset($_POST['destaque'])?1:0,isset($_POST['promocao'])?1:0,$inicio,$fim,isset($_POST['ativo'])?1:0,$id]);
    }else{
        $st=$pdo->prepare('UPDATE produtos SET categoria_id=?,nome=?,descricao=?,preco=?,imagem=?,estoque=?,destaque=?,promocao=?,ativo=? WHERE id=?');
        $st->execute([(int)post('categoria_id'),trim((string)post('nome')),trim((string)post('descricao')),$preco,$image,(int)post('estoque'),isset($_POST['destaque'])?1:0,isset($_POST['promocao'])?1:0,isset($_POST['ativo'])?1:0,$id]);
    }
    if($image!==$old)limparUpload($old);
    $stCat=$pdo->prepare('SELECT nome FROM categorias WHERE id=?'); $stCat->execute([(int)post('categoria_id')]); $categoriaNome=(string)$stCat->fetchColumn();
    if(mb_strtolower($categoriaNome)==='combos'){
        salvarComboConfiguracao($pdo,$id,comboConfiguracaoPost($_POST));
    } else {
        salvarComboConfiguracao($pdo,$id,null);
    }
    flash('sucesso',$hasOfferSchedule?'Produto atualizado e período salvo.':'Produto atualizado.');
}catch(Throwable $e){flash('erro',$e->getMessage());}
redirect('admin/produtos/index.php');
