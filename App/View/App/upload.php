<?php View::tplInclude('Public/header'); ?>
<link href="public/static/font-awesome/4.4.0/css/font-awesome.min.css"
      rel="stylesheet" type="text/css">
<div class="container" role="main">
    <ol class="breadcrumb">
        <li><a href="?c=app&a=index">应用管理</a></li>
        <li class="active">上传应用</li>
    </ol>
    <form class="form-horizontal" method="post" id="appUploadForm">
        <div class="jumbotron">
            <div class="step1" id="container">
                <p><a class="btn btn-primary" href="#" role="button" id="uploadBtn">上传apk文件/ipa包</a></p>
                <input type="file" class="hidden" name="file-selector" id="file-selector">
            </div>
            <div class="step2 hidden">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="1"
                         aria-valuemin="0" aria-valuemax="100" style="width: 1%">
                        <span class="sr-only"></span>
                    </div>
                </div>
                <p id="ossfile">
                </p>
            </div>
            <div class="step3 hidden">
                <div class="loading">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>

                </div>
                <p>文件正在努力解析，请稍等一会儿...</p>
            </div>

        </div>
        <input type="hidden" name="file">
        <input type="hidden" name="id" value="">
        <div class="step4 hidden">
            <div class="form-group">
                <label class="col-sm-2 control-label">应用类型<span class="color-red"></span></label>
                <div class="col-sm-10" style="padding-top: 5px">
                    <i class="fa fa-android hidden" style="font-size: 20px"></i>
                    <i class="fa fa-apple hidden" style="font-size: 20px"></i>
                    <input type="hidden" name="platform" value="">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">应用名<span class="color-red">*</span></label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="name" value="">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label"> Icon<span class="color-red">*</span></label>
                <div class="col-sm-10">
                    <input type="hidden" name="icon" value="">
                    <img src="public/static/images/default-icon.png" alt="" class="appIcon" width="60px">
                    <p class="help-block"><a href="javascript:;" class="btn btn-info btn-xs" id="iconBtnUpload">上传</a>
                    </p>
                    <div class="icon-progress hidden">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="1"
                             aria-valuemin="0" aria-valuemax="100" style="width: 1%">
                            <span class="sr-only"></span>
                        </div>
                    </div>
                    <span id="icon-file"> </span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Bundle Id/Package<span class="color-red">*</span></label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="bundle_id" value="" readonly>
                    <p class="help-block"></p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">文件大小<span class="color-red">*</span></label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="size" value="">
                    <p class="help-block"></p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">版本号<span class="color-red">*</span></label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="version_name" value="">
                    <p class="help-block"></p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">描述</label>
                <div class="col-sm-10">
                    <textarea name="summary" class="form-control"
                              rows="3"><?= defaultEcho($app, 'summary') ?></textarea>
                </div>
            </div>
            <div class="form-group ios-form hidden">
                <label class="col-sm-2 control-label">安装模式<span class="color-red">*</span></label>
                <div class="col-sm-10">
                    <select name="install_type" class="form-control" id="">
                        <option value="1" <?php if (empty($app) || $app['install_type'] == 1) {
                            echo 'selected';
                        } ?> >企业签名
                        </option>
                        <option value="2" <?php if (!empty($app) && $app['install_type'] == 2) {
                            echo 'selected';
                        } ?>>超级签名【需要系统配置开发者秘钥】
                        </option>
                    </select>
                </div>
            </div>
            <div class="form-group ios-form hidden">
                <label class="col-sm-2 control-label">日限额</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="install_day_max" placeholder="不填不限制"
                           value="<?= defaultEcho($app, 'install_day_max') ?>">
                </div>
            </div>
            <div class="form-group ios-form hidden">
                <label class="col-sm-2 control-label">总限额</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="install_count_max" placeholder="不填不限制"
                           value="<?= defaultEcho($app, 'install_count_max') ?>">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">提交</button>
                </div>
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
    <script type="text/javascript" src="public/static/js/cos/upload.js"></script>
<?php } else if (!empty($params['fileDriver']) && $params['fileDriver'] == 'oss'){ ?>
    <script type="text/javascript" src="public/static/js/oss/lib/plupload-2.1.2/js/plupload.full.min.js"></script>
    <script type="text/javascript" src="public/static/js/oss/upload.js"></script>
<?php }else{ ?>
    <script type="text/javascript" src="public/static/js/oss/lib/plupload-2.1.2/js/plupload.full.min.js"></script>
    <script type="text/javascript" src="public/static/js/local/upload.js"></script>
<?php } ?>
<script type="text/javascript" src="public/static/js/app-info-parser.js"></script>
<script type="text/javascript" src="public/static/js/local/upload-icon.js"></script>
<script>
    app = {}
    function fileParse(file) {
        // const files = document.getElementById(id).files
        const parser = new AppInfoParser(file)
        parser.parse().then(result => {
            if (result.package) {
                app.name = result.application.label[0]
                app.bundle_id = result.package
                app.version_name = result.versionName
                $('input[name="platform"]').val('Android');
                $('.fa-android').removeClass('hidden');
            } else {
                app.name = result.CFBundleDisplayName ? result.CFBundleDisplayName : result.CFBundleName
                app.bundle_id = result.CFBundleIdentifier
                app.version_name = result.CFBundleShortVersionString ? result.CFBundleShortVersionString : result.CFBundleVersion
                $('input[name="platform"]').val('iOS');
                $('.fa-apple,.ios-form').removeClass('hidden');
            }
            app.icon = result.icon
        }).catch(err => {
            console.log('err ----> ', err)
        })
    }
</script>
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
