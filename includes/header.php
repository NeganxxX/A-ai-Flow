<?php
require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/../config/conexao.php';
$pageTitle = $pageTitle ?? SITE_NAME;
$activePage = $activePage ?? '';
$extraCss = $extraCss ?? [];
$extraJs = $extraJs ?? [];
$bodyClass = $bodyClass ?? '';
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#6B0F32">
<meta name="description" content="Açaí Flow — Seu sabor, seu ritmo.">
<title><?=e($pageTitle)?> | <?=e(SITE_NAME)?></title>
<link rel="icon" href="<?=asset('img/logo/favicon.svg')?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
<script>(function(){try{if(localStorage.getItem('acaiFlowTheme')==='dark'){document.documentElement.classList.add('dark-mode');}}catch(e){}})();</script>
<link rel="stylesheet" href="<?=asset('css/style.css')?>?v=<?=filemtime(__DIR__.'/../css/style.css')?>">
<link rel="stylesheet" href="<?=asset('css/componentes.css')?>?v=<?=filemtime(__DIR__.'/../css/componentes.css')?>">
<link rel="stylesheet" href="<?=asset('css/responsivo.css')?>?v=<?=filemtime(__DIR__.'/../css/responsivo.css')?>">
<link rel="stylesheet" href="<?=asset('css/tema.css')?>?v=<?=filemtime(__DIR__.'/../css/tema.css')?>">
<?php foreach ($extraCss as $css): ?><link rel="stylesheet" href="<?=asset($css)?>"><?php endforeach; ?>
</head>
<body<?= $bodyClass !== '' ? ' class="'.e($bodyClass).'"' : '' ?>>
<div class="site-flashes">
<?php foreach (['sucesso','erro','info'] as $flashType): foreach (consumeFlash($flashType) as $flashMessage): ?>
    <div class="flash flash-<?=$flashType?>"><?=e($flashMessage)?></div>
<?php endforeach; endforeach; ?>
</div>
<?php require __DIR__ . '/menu.php'; ?>
<main>
