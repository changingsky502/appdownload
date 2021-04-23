<?php View::tplInclude('Public/header'); ?>
<div class="container" role="main">
    <ol class="breadcrumb">
        <li><a href="?c=app&a=index">应用管理</a></li>
        <li class="active">修改应用</li>
        <!--        <li class="pull-right"><a href="?c=app&a=del" class="text-danger">删除应用</a></li>-->
    </ol>
    <form class="form-horizontal" method="post" action="">
        <div class="form-group">
            <label class="col-sm-2 control-label">状态</label>
            <div class="col-sm-10">
                <label class="radio-inline">
                    <input type="radio" name="status" value="1" <?php if ($data['app']['status']==1) echo 'checked'; ?>> 正常
                </label>
                <label class="radio-inline">
                    <input type="radio" name="status" value="0" <?php if ($data['app']['status']==0) echo 'checked'; ?>> 暂停安装
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">应用名*</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" value="<?php echo $data['app']['name']; ?>" required placeholder="应用名">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">图标</label>
            <div class="col-sm-10">
                <img src="<?php echo $data['app']['icon']; ?>" width="50">
                <input type="hidden" name="icon" value="<?php echo $data['app']['icon']; ?>">
                <p class="help-block"></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Bundle Id</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bundle_id"
                       value="<?php echo $data['app']['bundle_id']; ?>">
                <p class="help-block"</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">iOS包地址</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="file" value="<?php echo $data['app']['file']; ?>"
                       readonly>
                <p class="help-block"><a href="<?php echo $data['app']['file']; ?>" target="_blank"
                                         class="btn btn-info btn-xs">下载</a></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">大小</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="install_day_max"
                       value="<?php echo $data['app']['size']; ?>" placeholder="">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">版本</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="install_day_max"
                       value="<?php echo $data['app']['version_name']; ?>" placeholder="">
            </div>
        </div>
        <div class="form-group ">
            <label class="col-sm-2 control-label">关联应用 <span class="color-red">*</span></label>
            <div class="col-sm-10">
                <select name="relation_id" class="form-control" id="">
                    <option value="" <?php if (empty($data['related_apps']) || empty($data['app']['relation_id'])) {
                        echo 'selected';
                    } ?> >无
                    </option>
                    <?php if ($data['related_apps']) {
                        foreach ($data['related_apps'] as $v) { ?>
                            <option value="<?= $v['id'] ?>" <?php if (!empty($data['app']['relation_id']) && $data['app']['relation_id'] == $v['id']) {
                                echo 'selected';
                            } ?> ><?= $v['name'] . '---' . $v['platform'] ?>
                            </option>
                        <?php }
                    } ?>
                </select>
                <p class="help-block"><span class="small">安卓苹果应用相互关联</span></p>
            </div>
        </div>
        <div class="form-group ios-form hidden">
            <label class="col-sm-2 control-label">安装模式 <span class="color-red">*</span></label>
            <div class="col-sm-10">
                <select name="install_type" class="form-control" id="">
                    <option value="2" <?php if (empty($data['app']) || $data['app']['install_type'] == 2) {
                        echo 'selected';
                    } ?>>超级签名【需要系统配置开发者秘钥】
                    </option>
                    <option value="1" <?php if (!empty($data['app']) && $data['app']['install_type'] == 1) {
                        echo 'selected';
                    } ?> >企业签名【ipa包必须是企业签名包】
                    </option>
                </select>
            </div>
        </div>
        <div class="form-group ios-form hidden">
            <label class="col-sm-2 control-label">使用vpn</label>
            <div class="col-sm-10">
                <select name="use_vpn" id="" class="form-control">
                    <option value="0" <?php if (empty($data['app']['use_vpn'])) {
                        echo 'selected';
                    } ?>>否
                    </option>
                    <option value="1" <?php if (!empty($data['app']['use_vpn'])) {
                        echo 'selected';
                    } ?>>是
                    </option>
                </select>
                <p class="help-block"><span class="small">vpn功能只针对超级签名安装模式有效</span></p>
            </div>
        </div>
        <div class="form-group ios-form hidden">
            <label class="col-sm-2 control-label">日限额</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="install_day_max" value="<?php echo $data['app']['install_day_max']; ?>" placeholder="不填不限制">
                <p class="help-block"><span class="small">限额功能只针对超级签名安装模式有效</span></p>
            </div>
        </div>
        <div class="form-group ios-form hidden">
            <label class="col-sm-2 control-label">总限额</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="install_count_max" value="<?php echo $data['app']['install_count_max']; ?>" placeholder="不填不限制">
                <p class="help-block"><span class="small">限额功能只针对超级签名安装模式有效</span></p>
            </div>

        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">描述</label>
            <div class="col-sm-10">
                <textarea name="summary" class="form-control" rows="3"><?php echo $data['app']['summary']; ?></textarea>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary">提交</button>
            </div>
        </div>
    </form>
</div>
<?php if (!empty($params['fileDriver']) && $params['fileDriver'] == 'cos') { ?>
    <script src="public/static/js/cos/cos-js-sdk-v5.min.js"></script>
    <script>
        Bucket = "<?=$params['cosBucket']?>";
        Region = "<?=$params['region']?>";
        /* 存储桶所在地域，必须字段 */
    </script>
    <script type="text/javascript" src="public/static/js/cos/upload-android.js"></script>
<?php } else if (!empty($params['fileDriver']) && $params['fileDriver'] == 'oss') { ?>
    <script type="text/javascript" src="public/static/js/oss/lib/plupload-2.1.2/js/plupload.full.min.js"></script>
    <script type="text/javascript" src="public/static/js/oss/upload-android.js"></script>
<?php }else{ ?>
    <script type="text/javascript" src="public/static/js/oss/lib/plupload-2.1.2/js/plupload.full.min.js"></script>
    <script type="text/javascript" src="public/static/js/local/upload-android.js"></script>
<?php } ?>
<script>
    var app_type = "<?=$data['app']['platform']?>"
    if (app_type == 'iOS') {
        $('.ios-form').removeClass('hidden');
    }
</script>

<?php View::tplInclude('Public/footer'); ?>
