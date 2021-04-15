<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>XSIGN</title>
    <link href="public/static/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/static/css/toastr.min.css" rel="stylesheet">
    <link href="public/static/css/common.css" rel="stylesheet">
    <script src="public/static/js/jquery.min.js"></script>
    <script src="public/static/js/toastr.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-lg-6">
            <form method="post">
                <h3 class="">xSign管理系统</h3>
                <div class="form-group">
                    <label for="exampleInputEmail1">账号</label>
                    <input type="text" name="username" class="form-control" id="exampleInputEmail1" placeholder="请输入账号">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">密码</label>
                    <input type="password" name="password" required class="form-control" id="exampleInputPassword1"
                           placeholder="请输入密码">
                </div>
                <button type="submit" class="btn btn-default">登录</button>
                <small>温馨提醒：请复制保存或者收藏登录地址，以防丢失。</small>
            </form>
        </div>
    </div>
</div>
<script>
    <?php if(!empty($tips)){ ?>
    <?php if(!empty($tips['error'])){ ?>
    toastr.error("<?=implode('，', $tips['error'])?>");
    <?php }else{ ?>
    toastr.success("<?=implode('，', $tips['success'])?>");
    <?php } ?>
    <?php } ?>
</script>
</body>
</html>