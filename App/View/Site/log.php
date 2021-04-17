<?php
View::tplInclude('Public/header'); ?>
    <style>
        .tab-content {
            padding: 30px 0;
        }
    </style>
    <main class="bs-docs-masthead" id="content" role="main">
        <div class="container">
            <ul class="nav nav-tabs">
                <li><a href="index.php?c=site&a=config&tab=profile">文件存储分发</a></li>
                <li><a href="index.php?c=site&a=config&tab=developer">开发者账号</a></li>
                <li><a href="index.php?c=site&a=config&tab=safe">安全</a></li>
                <li class="active"><a href="index.php?c=site&a=log" data-toggle="tab">错误与日志</a></li>
            </ul>
            <form method="post">
                <div class="tab-content">

                        <textarea class="form-control" name="" id="" cols="60" rows="30" readonly>
                            <?= $data ?>
                        </textarea>

                    <a href="javascript:;" onclick="location.reload()" class="btn btn-primary">刷新</a>
                </div>


            </form>
        </div>
    </main>
    <script>
        <?php if(!empty($tips)){ ?>
        <?php if(!empty($tips['error'])){ ?>
        toastr.error("<?=implode('，', $tips['error'])?>");
        <?php }else{ ?>
        toastr.success("<?=implode('，', $tips['success'])?>");
        <?php } ?>
        <?php } ?>

    </script>
<?php View::tplInclude('Public/footer'); ?>