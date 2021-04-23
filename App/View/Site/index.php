<?php
View::tplInclude('Public/header'); ?>
    <link href="public/static/font-awesome/4.4.0/css/font-awesome.min.css"
          rel="stylesheet" type="text/css">
    <style>
        .tab-content {
            padding: 30px 0;
        }
    </style>
    <main class="bs-docs-masthead" id="content" role="main">
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">系统信息</div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <tr>
                            <td>开发者证书信息剩余：<span class="<?php if (empty($apiData) || $apiData['surplus'] < 50) {
                                    echo 'color-red';
                                } else {
                                    echo 'color-green';
                                } ?>"><?= $apiData['surplus'] ?></span>, 总计：<?= $apiData['total'] ?></td>
                            <td>PHP版本号：<?= PHP_VERSION ?></td>
                            <td>服务器：<?= check_system_type() ?></td>
                        </tr>
                        <tr>
                            <td>
                                PHP处理上传文件的最大值： <?= ini_get('upload_max_filesize') ?>【upload_max_filesize】
                            </td>
                            <td>POST方法传输最大限制： <?= ini_get('post_max_size') ?>【post_max_size】</td>
                            <td>磁盘总容量：<?= get_disk_total(disk_total_space('.')) ?>
                                ，剩余空间：<?= get_disk_total(disk_free_space('.')) ?></td>
                        </tr>
                        <tr>
                            <td>exec函数状态：<?php $log = [];
                                $status = 1;
                                @exec('ls', $log, $status);
                                echo !$status ? '<span class="color-green">开启</span>' : '<span class="color-red">关闭【需要前往php.ini开启】</span>'; ?></td>
                            <td>
                                文件可写权限：<?php echo $writeAuth ? '<span class="color-green">可写</span>' : '<span class="color-red">不可写【需手动授权public/uploads目录，App/Data目录】</span>'; ?></td>
                            <td>
                                签包执行权限：<?php echo $excuAuth ? '<span class="color-green">可执行</span>' : '<span class="color-red">已禁止【需手动授权sign文件】</span>'; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="panel-footer">
                    开源项目 <a href="https://github.com/changingsky502/appdownload" target="_blank"><span
                                class="fa fa-github" style="color: #000"></span></a>
                </div>
            </div>

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