<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta content="telephone=no" name="format-detection"/>
    <title><?=$app['name']?> 下载</title>
    <link href="public/static/css/install.css" rel="stylesheet">
    <link href="public/static/font-awesome/4.4.0/css/font-awesome.min.css"
          rel="stylesheet" type="text/css">
    <STYLE>
        .install4 a#btn-install-app{
            color: #fff;
        }
        .loading{
            width: 15px;
            height: 15px;
            margin: 0 auto;
            display: inline-block;
            border-radius: 50%;
            vertical-align: middle;
            margin-left: 5px;
            border: 3px solid #BEBEBE;
            border-left: 3px solid #fff;
            animation: load 1s linear infinite;
            -moz-animation:load 1s linear infinite;
            -webkit-animation: load 1s linear infinite;
            -o-animation:load 1s linear infinite;
        }
        @-webkit-keyframes load
        {
            from{-webkit-transform:rotate(0deg);}
            to{-webkit-transform:rotate(360deg);}
        }
        @-moz-keyframes load
        {
            from{-moz-transform:rotate(0deg);}
            to{-moz-transform:rotate(360deg);}
        }
        @-o-keyframes load
        {
            from{-o-transform:rotate(0deg);}
            to{-o-transform:rotate(360deg);}
        }
    </STYLE>
</head>
<body>

<div class="install">
    <div class="install-top"><img src="<?=defaultEcho($app, 'icon')?>"/></div>
    <div class="install-title">
        <?php if ($app['platform'] == 'iOS') { ?>
            <i class="fa fa-apple"></i>
         <?php }else{  ?>
            <i class="fa fa-android"></i>
        <?php } ?>
        <span class="install-details"><?=defaultEcho($app, 'name')?></span>
        <!-- <span class="install-details2">内测版</span> -->
    </div>
</div>
<div class="install2">
    <span>版本 &nbsp;<?=defaultEcho($app, 'version_name')?></span>
    <span>大小 &nbsp;<?=defaultEcho($app, 'size')?></span>
</div>
<div class="install2">更新时间：<?=date('Y-m-d H:i', $app['utime'])?></div>

<?php if(!empty($app) && $app['status'] == 1){ ?>
    <div class="install4">
        <input type="hidden" name="id" id="id" value="<?=defaultEcho($app, 'id')?>"/>
        <?php if (!empty($app) && $app['install_type'] == 1) { ?>
            <a id="btn-install-app" hrefa="<?= $app['install_url'] ?>" data-id="<?= $app['id'] ?>">点击获取</a>
        <?php } else { ?>
            <a id="btn-install-app" hrefa="index.php?c=index&a=getudidmobileconfig" data-id="<?= $app['id'] ?>">点击获取</a>
        <?php } ?>
    </div>

    <div class="install3 erweim" date-url="<?=$result['qrcode_url']?>">
        <div class="erweidws"></div>
    </div>

    <div class="install5">或者用手机扫描二维码安装</div>
<?php } ?>
<?php if(!empty($app['version_desc'])){ ?>
    <div class="installinfo">
        <h2>版本更新说明</h2>
        <p><?=$app['version_desc']?></p>
    </div>
<?php } ?>
<?php if(!empty($app['summary'])){ ?>
    <div class="installinfo">
        <h2>应用介绍</h2>
        <p><?=$app['summary']?></p>
    </div>
<?php } ?>

<script src="public/static/js/jquery.min.js"></script>
<script src="public/static/js/jquery.qrcode.min.js"></script>
<link rel="stylesheet" href="public/static/js/layui/css/layui.css" media="all">
<script src="public/static/js/layui/layui.all.js"></script>
<script>
    $(function () {

        var url = $(".erweim").attr("date-url");
        $(".erweidws").qrcode({
            render: "canvas", //table方式
            width: 140, //宽度
            height: 140, //高度
            text: url //任意内容
        });

        var is_wx = <?=$result['is_wx']?>;
        var is_qq = <?=$result['is_qq']?>;
        var is_ios = <?=$result['is_ios']?>;
        var app_type = "<?=defaultEcho($app, 'platform')?>";
        var udid = "<?=$udid?>";
        var safari = <?=$result['safari']?>;
        var get_udid_url = "<?=$result['get_udid_url']?>";
        var ios_device_file = "<?=$result['ios_device_file']?>";
        var token = "<?=$result['token']?>";
        var token_last = "<?=$token_last?>";
        var app_id = "<?=$app['id']?>"
        var download_time = "<?=$result['download_time']?>"
        var install_type = "<?=defaultEcho($app, 'install_type')?>"
        $(".install4 a#btn-install-app").click(function () {

            if (is_wx || is_qq) {
                layer.confirm('如果您使用微信或QQ打开的本链接，请点击右上角按钮，然后在弹出的菜单中，点击在浏览器中打开，即可安装', {
                    btn: ['我知道'] //按钮
                });
                return;
            }
            if( is_ios == 1 && safari == 2){
                layer.confirm('请用safari（苹果）浏览器打开！', {
                    btn: ['我知道'] //按钮
                });
                return;
            }
            if (is_ios != 1) {
                if (app_type == 'iOS') {
                    layer.confirm('暂不支持安卓类型应用下载！', {
                        btn: ['我知道'] //按钮
                    });
                    return;
                }
                var install_url = $(this).attr('hrefa')
                location.href = install_url;
                $.ajax({
                    type: 'POST',
                    url: 'index.php?c=index&a=apkInstall',
                    data: {token: token, id: app_id},
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function (data) {
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {

                    }
                });
                return;
            }
            if (is_ios == 1) {
                $(this).css("pointer-events","none");
                if (app_type == 'Android') {
                    layer.confirm('暂不支持苹果类型应用下载！', {
                        btn: ['我知道'] //按钮
                    });
                    return;
                }
                //企业签名
                if (install_type == 1) {
                    var install_url = $(this).attr('hrefa')
                    layer.confirm('安装完成后， 需要在【设置】-【描述文件】中信任企业证书！', {
                        btn: ['我知道'] //按钮
                    }, function () {
                        location.href = install_url;
                        $('#btn-install-app').html('返回桌面查看。')
                    });
                    return;
                }
                //超级签名
                if(udid){
                    startDownload()
                }else{

                    $.ajax({
                        type: 'POST',
                        url: get_udid_url,
                        data: {token :token},
                        dataType:'json',
                        beforeSend: function(){
                            $('#btn-install-app').html('获取中...')
                        },
                        success: function (data) {
                            if (data.code == '200') {
                                //下载描述文件
                                window.location.href = data.udid_mobile_config;
                                $('body').append('<iframe class="down-frame" id="iframedownload" width="1" height="1" src="'+data.udid_mobile_config+'" ></iframe>');
                                var timer = setInterval(function () {
                                    iframe = document.getElementById('iframedownload');
                                    var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                                    // Check if loading is complete
                                    if (iframeDoc.readyState == 'complete' || iframeDoc.readyState == 'interactive') {
                                        location.href = ios_device_file;
                                        clearInterval(timer);
                                        return;
                                    }
                                }, 2000);
                            } else {
                                layer.msg(data.msg, {icon: 2, time: 1000});
                            }
                            $(this).css("pointer-events","auto");
                            $('#btn-install-app').html('点击获取')
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            alert('error');
                        }
                    });
                }
            }
        })
        //自动下载
        if(udid && token_last){
            startDownload();
        }
        function startDownload()
        {
            $.ajax({
                type: 'POST',
                url: 'index.php?c=index&a=getIpa',
                data: {token :token_last, id:app_id, udid: udid, tm:getQueryVariable('tm'), device_product:getQueryVariable('device_product')},
                dataType:'json',
                beforeSend: function(){
                    $(this).css("pointer-events","none");
                    $('#btn-install-app').html('准备中,预计'+download_time+'秒...<div class="loading"></div>')
                },
                success: function (data) {
                    console.error(data)
                    if (data.code == '200') {
                        window.location.href = data.url;
                        setTimeout(function(){
                            $('#btn-install-app').html('安装中，返回桌面查看!');
                        },2000)
                    } else if(data.code == '300'){
                        setTimeout(function(){
                            startDownload();
                        }, 3000)
                    }else {
                        if(data.msg){
                            layer.msg(data.msg, {icon: 2, time: 1000});
                        }else{
                            layer.msg('请求出错，请稍后再试！', {icon: 2, time: 1000});
                        }
                        $('#btn-install-app').html('点击获取');
                    }
                    $(this).css("pointer-events","auto");
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('error');
                    $(this).css("pointer-events","auto");
                }
            });
        }
    });
    function getQueryVariable(variable)
    {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if(pair[0] == variable){return pair[1];}
        }
        return(false);
    }
</script>
</body>
</html>
