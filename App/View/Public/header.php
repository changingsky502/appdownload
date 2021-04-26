<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>appDownload</title>
    <link href="public/static/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/static/css/toastr.min.css" rel="stylesheet">
    <link href="public/static/css/common.css" rel="stylesheet">
    <script src="public/static/js/jquery.min.js"></script>
    <script src="public/static/js/toastr.min.js"></script>
</head>
<body>
<?php
$controller = !empty($_GET['c']) ? $_GET['c'] : 'Index';
$action = !empty($_GET['a']) ? $_GET['a'] : 'Download';
?>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">切换导航</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">appDownload</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li <?php if ($controller == 'site' && $action=='index'){ ?>class="active" <?php } ?>><a href="?c=site&a=index">控制台</a></li>
                <li <?php if ($controller == 'app'){ ?>class="active" <?php } ?>><a href="?c=app&a=index">应用管理</a></li>
                <li <?php if ($controller == 'site' && $action== 'config'){ ?>class="active" <?php } ?>><a href="?c=site&a=config">系统配置</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="?c=index&a=logOut">退出登陆</a></li>
            </ul>
        </div>
    </div>
</nav>