<?php
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
require_once __DIR__.'/../../includes/funcoes.php';
require_once __DIR__.'/../../config/conexao.php';
require_once __DIR__.'/../../config/catalogo.php';

if($_SERVER['REQUEST_METHOD']!=='POST'||!validarCsrf(post('csrf'))) redirect('admin/produtos/cadastrar.php');
$pdo=getPDO();
if(!$pdo){flash('erro','Banco de dados indisponível.');redirect('admin/produtos/cadastrar.php');}

function normalizarDataOferta(?string $value): ?string {
    $value=trim((string)$value);
    if($value==='') return null;
    $date=DateTime::createFromFormat('Y-m-d\\TH:i',$value);
    if(!$date) throw new InvalidArgumentException('Data de exibição inválida.');
    return $date->format('Y-m-d H:i:s');
}

try{
    $inicio=normalizarDataOferta(post('oferta_inicio'));
    $fim=normalizarDataOferta(post('oferta_fim'));
    if($inicio!==null && $fim!==null && strtotime($fim)<strtotime($inicio)) throw new InvalidArgumentException('O fim da exibição deve ser posterior ao início.');
    $nomeProduto = trim((string)post('nome'));
    $preco = mb_strtolower($nomeProduto) === 'combo casal' ? 0.0 : (float)post('preco');

    $image=processarUploadImagem($_FILES['imagem']??[],'produtos');
    $hasOfferSchedule=colunaExiste($pdo,'produtos','oferta_inicio')&&colunaExiste($pdo,'produtos','oferta_fim');
    if($hasOfferSchedule){
        $st=$pdo->prepare('INSERT INTO produtos(categoria_id,nome,descricao,preco,imagem,estoque,destaque,promocao,oferta_inicio,oferta_fim,ativo) VALUES(?,?,?,?,?,?,?,?,?,?,1)');
        $st->execute([(int)post('categoria_id'),trim((string)post('nome')),trim((string)post('descricao')),$preco,$image,(int)post('estoque'),isset($_POST['destaque'])?1:0,isset($_POST['promocao'])?1:0,$inicio,$fim]);
    }else{
        $st=$pdo->prepare('INSERT INTO produtos(categoria_id,nome,descricao,preco,imagem,estoque,destaque,promocao,ativo) VALUES(?,?,?,?,?,?,?,?,1)');
        $st->execute([(int)post('categoria_id'),trim((string)post('nome')),trim((string)post('descricao')),$preco,$image,(int)post('estoque'),isset($_POST['destaque'])?1:0,isset($_POST['promocao'])?1:0]);
    }
    $produtoId=(int)$pdo->lastInsertId();
    $categoriaNome='';
    $stCat=$pdo->prepare('SELECT nome FROM categorias WHERE id=?'); $stCat->execute([(int)post('categoria_id')]); $categoriaNome=(string)$stCat->fetchColumn();
    if(mb_strtolower($categoriaNome)==='combos'){
        $comboConfig=comboConfiguracaoPost($_POST);
        salvarComboConfiguracao($pdo,$produtoId,$comboConfig);
    } else {
        salvarComboConfiguracao($pdo,$produtoId,null);
    }
    flash('sucesso',$hasOfferSchedule?'Produto cadastrado e período configurado.':'Produto cadastrado.');
}catch(Throwable $e){flash('erro',$e->getMessage());}
redirect('admin/produtos/index.php');
