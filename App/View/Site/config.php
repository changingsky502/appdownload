<?php
View::tplInclude('Public/header', $data); ?>
    <style>
        .tab-content {
            padding: 30px 0;
        }
    </style>
    <main class="bs-docs-masthead" id="content" role="main">
        <div class="container">
            <ul class="nav nav-tabs">
                <li class="tab-profile active" onclick="tabChange('tab-profile')"><a href="#profile" data-toggle="tab">文件存储分发</a>
                </li>
                <li class="tab-developer" onclick="tabChange('tab-developer')"><a href="#developer" data-toggle="tab">开发者账号</a>
                </li>
                <li class="tab-safe" onclick="tabChange('tab-safe')"><a href="#safe" data-toggle="tab">安全</a></li>
                <li><a href="index.php?c=site&a=log">错误与日志</a></li>
            </ul>
            <form method="post">
                <div class="tab-content">

                    <div class="tab-pane active tab-profile" id="profile">
                        <div class="form-group">
                            <label for="fileDriver">引擎<span class="color-red">*</span></label>
                            <br>
                            <input type="radio" name="fileDriver"
                                   value="local" <?php if (empty($data) || empty($data['fileDriver']) || $data['fileDriver'] == 'local') { ?> checked <?php } ?>
                                   onclick="showDriverFormData('local')">
                            本地存储
                            <small>（本地带宽性能要求较高，建议云存储。采用本地请务必确认服务器上传文件大小限制！）</small>
                            <br>
                            <input type="radio" name="fileDriver"
                                   value="oss" <?php if (!empty($data['fileDriver']) && $data['fileDriver'] == 'oss') { ?> checked <?php } ?>
                                   onclick="showDriverFormData('oss')">
                            阿里云oss
                            <small><a href="https://oss.console.aliyun.com/overview" target="_blank">oss控制台</a></small>
                            <small>
                                <a href="https://help.aliyun.com/learn/learningpath/oss.html?spm=5176.8465980.guide.1.4e701450SjqJXe"
                                   target="_blank">新手入门</a></small>

                            <br>
                            <input type="radio" name="fileDriver"
                                   value="cos" <?php if (!empty($data['fileDriver']) && $data['fileDriver'] == 'cos') { ?> checked <?php } ?>
                                   onclick="showDriverFormData('cos')">
                            腾讯云cos
                            <small><a href="https://console.cloud.tencent.com/cos5" target="_blank">cos控制台</a></small>
                            <small><a href="https://cloud.tencent.com/document/product/436" target="_blank">新手入门</a>
                            </small>
                        </div>
                        <div class="form-group oss-data">
                            <label for="accessKeyId">accessKey Id<span class="color-red">*</span></label>
                            <a href="https://usercenter.console.aliyun.com/#/manage/ak"
                               target="_blank">阿里云accessKey获取指引</a>

                            <input type="text" name="accessKeyId" class="form-control" id="accessKeyId" placeholder=""
                                   value="<?= defaultEcho($data, 'accessKeyId') ?>">
                        </div>
                        <div class="form-group oss-data">
                            <label for="accessKeySecret">accessKey Secret<span class="color-red">*</span></label>
                            <input type="text" name="accessKeySecret" class="form-control" id="accessKeySecret"
                                   placeholder="" value="<?= defaultEcho($data, 'accessKeySecret') ?>">
                        </div>
                        <div class="form-group oss-data">
                            <label for="bucket">bucket name<span class="color-red">*</span></label>
                            <small>存储桶名称</small>
                            <input type="text" name="bucket" class="form-control" id="bucket" placeholder=""
                                   value="<?= defaultEcho($data, 'bucket') ?>">
                        </div>
                        <div class="form-group oss-data">
                            <label for="endpoint">endpoint<span class="color-red">*</span></label>
                            <small>地域节点，建议填写全球加速域名</small>
                            <input type="text" name="endpoint" class="form-control" id="endpoint" placeholder=""
                                   value="<?= defaultEcho($data, 'endpoint') ?>">
                        </div>
                        <div class="form-group oss-data">
                            <label for="endpointInternal">endpoint【内网】</label>
                            <small>填写内网节点，可快速上传。注意：服务器所在区域节点和存储桶区域节点要相同</small>
                            <input type="text" name="endpointInternal" class="form-control" id="endpointInternal" placeholder=""
                                   value="<?= defaultEcho($data, 'endpointInternal') ?>">
                        </div>
                        <div class="form-group cos-data">
                            <label for="secretId">secretId<span class="color-red">*</span></label>
                            <a href="https://console.cloud.tencent.com/cam/capi"
                               target="_blank">腾讯云 API secretId获取指引</a>
                            <input type="text" name="secretId" class="form-control" id="secretId" placeholder=""
                                   value="<?= defaultEcho($data, 'secretId') ?>">
                        </div>
                        <div class="form-group cos-data">
                            <label for="secretKey">secretKey<span class="color-red">*</span></label>
                            <small>腾讯云 API secretKey</small>
                            <input type="text" name="secretKey" class="form-control" id="secretKey" placeholder=""
                                   value="<?= defaultEcho($data, 'secretKey') ?>">
                        </div>
                        <div class="form-group cos-data">
                            <label for="cosBucket">bucket<span class="color-red">*</span></label>
                            <small>存储桶名称</small>
                            <input type="text" name="cosBucket" class="form-control" id="cosBucket" placeholder=""
                                   value="<?= defaultEcho($data, 'cosBucket') ?>">
                        </div>
                        <div class="form-group cos-data">
                            <label for="region">region<span class="color-red">*</span></label>
                            <small>存储桶地域</small>
                            <input type="text" name="region" class="form-control" id="region" placeholder=""
                                   value="<?= defaultEcho($data, 'region') ?>">
                        </div>
                    </div>
                    <div class="tab-pane tab-developer" id="developer">
                        <div class="alert alert-info" role="alert">
                            <a href="#" class="alert-link">注意：超级签名模式，苹果手机安装应用之前，需要使用苹果开发者为应用进行签名，务必要配置开发者秘钥。</a>
                        </div>
                        <div class="form-group">
                            <label for="apiAccessKey">开发者秘钥 <span class="color-red">*</span>
                                <small><a href="https://www.ddqshop.com/product/developer-program/"
                                          target="_blank">获取开发者秘钥</a></small>
                            </label>
                            <input type="text" name="apiAccessKey" class="form-control" id="apiAccessKey"
                                   placeholder="请输入新密码确认" value="<?= defaultEcho($data, 'apiAccessKey') ?>" size="">
                        </div>
                    </div>
                    <div class="tab-pane tab-safe" id="safe">
                        <?php if (empty($data['loginPass'])) { ?>
                            <div class="alert alert-info" role="alert">
                                <a href="#" class="alert-link">注意：当前系统登录账号是默认账号，强烈建议修改。</a>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="username">登录用户名</label>
                            <input type="text" name="username" class="form-control" id="username"
                                   placeholder="请输入您的用户名">
                        </div>
                        <?php if (!empty($data['loginPass'])) { ?>
                            <div class="form-group">
                                <label for="oldPass">登录旧密码</label>
                                <input type="password" name="oldPass" class="form-control" id="oldPass"
                                       placeholder="请输入旧密码进行校验">
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="newPass">登录新密码</label>
                            <input type="password" name="newPass" class="form-control" id="newPass"
                                   placeholder="请输入新密码">
                        </div>
                        <div class="form-group">
                            <label for="newPassConfirm">登录新密码确认</label>
                            <input type="password" name="newPassConfirm" class="form-control" id="newPassConfirm"
                                   placeholder="请输入新密码确认">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">提交</button>

                    <table class="table table-bordered developer-box hidden" style="width: 60%;margin-top: 50px">
                        <tr>
                            <th colspan="3">开发者证书信息剩余：<?= $developer['surplus'] ?>，证书总计：<?= $developer['total'] ?></th>
                        </tr>
                        <tr>
                            <th>udid</th>
                            <th>时间</th>
                            <th>是否扣量</th>
                        </tr>
                        <?php if (empty($developer['data'])) { ?>
                            <tr>
                                <td colspan="3">暂无数据</td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($developer['data'] as $v) { ?>
                                <tr>
                                    <td><?= $v['udid'] ?></td>
                                    <td><?= $v['time'] ?></td>
                                    <td><?= $v['expend'] ? '是' : '否'; ?></td>
                                </tr>
                            <?php }
                        } ?>
                    </table>
                    <nav aria-label="..." class="developer-box hidden" style="width: 60%">
                        <ul class="pager">
                            <?php if ($developer['page'] == 1) { ?>
                                <li class="previous disabled"><a href="#"> 上一页</a></li>
                            <?php } else { ?>
                                <li class="previous"><a
                                            href="index.php?c=site&a=config&tab=developer&page=<?= $developer['page'] - 1; ?>">
                                        上一页</a></li>
                            <?php } ?>
                            <li class="next"><a
                                        href="index.php?c=site&a=config&tab=developer&page=<?= $developer['page'] + 1; ?>">下一页</a>
                            </li>
                        </ul>
                    </nav>
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
    <script>
        function showDriverFormData(driver) {
            if (driver == 'oss') {
                $('.oss-data').show();
                $('.cos-data').hide();
            } else if (driver == 'cos') {
                $('.cos-data').show();
                $('.oss-data').hide();
            } else if (!driver || driver == 'local') {
                $('.oss-data, .cos-data').hide();
            }
        }
        showDriverFormData('<?= defaultEcho($data, 'fileDriver') ?>');
        var tab = getQueryVariable('tab')
        if (tab) {
            $('.nav-tabs li, .tab-pane').removeClass('active')
            $('.tab-' + tab).addClass('active')
            tabChange('tab-' + tab)
        }

        function getQueryVariable(variable) {
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (pair[0] == variable) {
                    return pair[1];
                }
            }
            return (false);
        }

        function tabChange(tab) {
            if (tab === 'tab-developer') {
                $('.developer-box').removeClass('hidden')
            } else {
                $('.developer-box').addClass('hidden')
            }
        }
    </script>

    <script>
        var _content = []; //临时存储li循环内容
        var loading = {
            _default: 5, //默认展示评论个数
            _loading: 5, //每次点击按钮后加载的个数
            init: function () {
                var lis = $(".loading .hidden li");
                $(".loading ul.list").html("");
                for (var n = 0; n < loading._default; n++) {
                    lis.eq(n).appendTo(".loading ul.list");
                }
                for (var i = loading._default; i < lis.length; i++) {
                    _content.push(lis.eq(i));
                }
                $(".loading .hidden").html("");
            },
            loadMore: function () {
                for (var i = 0; i < loading._loading; i++) {
                    var target = _content.shift();
                    if (!target) {
                        $('.loading .more').html("<p>全部加载完毕...</p>");
                        break;
                    }
                    $(".loading ul.list").append(target);
                }
            }
        }
        loading.init();
    </script>
<?php View::tplInclude('Public/footer'); ?>