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
                <li class="active"><a href="#" data-toggle="tab">数据备份</a></li>
                <li><a href="index.php?c=site&a=log">错误日志</a></li>
            </ul>
            <form method="post">
                <div class="tab-content">
                    <div class="form-group">
                        <a href="index.php?c=site&a=data&op=backup" class="btn btn-info" target="_blank">数据备份</a>
                    </div>
                    <div class="form-group" id="container">
                        <a href="javascript:;" class="btn btn-primary " id="uploadBtn">数据恢复</a>

                        <div class="icon-progress hidden">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="1"
                                 aria-valuemin="0" aria-valuemax="100" style="width: 1%">
                                <span class="sr-only"></span>
                            </div>
                        </div>
                        <span id="icon-file"></span>
                        <span id="console"></span>
                    </div>
                </div>
            </form>
        </div>
    </main>
    <script type="text/javascript" src="public/static/js/oss/lib/plupload-2.1.2/js/plupload.full.min.js"></script>
    <script>
        <?php if(!empty($tips)){ ?>
        <?php if(!empty($tips['error'])){ ?>
        toastr.error("<?=implode('，', $tips['error'])?>");
        <?php }else{ ?>
        toastr.success("<?=implode('，', $tips['success'])?>");
        <?php } ?>
        <?php } ?>
        window.history.pushState({}, 0, '?c=site&a=data');

        //实例化一个plupload上传对象
        var uploaderpro = new plupload.Uploader({
            browse_button: 'uploadBtn', //触发文件选择对话框的按钮，为那个元素id
            url: 'index.php?c=site&a=recover', //服务器端的上传页面地址
            max_file_size: '100mb',//用来限制单个文件大小的
            multi_selection: false,//默认支持多文件上传,false不支持
            filters: {
                mime_types: [{
                    //限制仅可上传压缩文件
                    title: "Zip files",
                    extensions: "zip"
                }],
            },
            flash_swf_url: 'public/static/js/oss/lib/plupload-2.1.2/js/Moxie.swf', //swf文件，当需要使用swf方式进行上传时需要配置该参数
            silverlight_xap_url: 'public/static/js/oss/lib/plupload-2.1.2/js/Moxie.xap' //silverlight文件，当需要使用silverlight方式进行上传时需要配置该参数
        });

        //在实例对象上调用init()方法进行初始化
        uploaderpro.init();

        //绑定各种事件，并在事件监听函数中做你想做的事
        uploaderpro.bind('FilesAdded', function (uploaderpro, files) {
            //每个事件监听函数都会传入一些很有用的参数，
            //我们可以利用这些参数提供的信息来做比如更新UI，提示上传进度等操作
            plupload.each(files, function (file) {
                document.getElementById('icon-file').innerHTML += file.name + ' (' + plupload.formatSize(file.size) + ') 进度：<span id="percent-txt">1%</span>';
            });
            $('.icon-progress').removeClass('hidden');

            uploaderpro.start();
        });
        uploaderpro.bind('UploadProgress', function (uploaderpro, file) {
            //每个事件监听函数都会传入一些很有用的参数，
            //我们可以利用这些参数提供的信息来做比如更新UI，提示上传进度等操作
            $('.icon-progress .progress-bar').css('width', file.percent + '%');
            $('#icon-file #percent-txt').text(file.percent + '%');
        });
        uploaderpro.bind('FileUploaded', function (up, file, info) {
            if (info.status == 200) {
                var response = JSON.parse(info.response);
                $('.icon-progress, #icon-file').addClass('hidden');
                if (response.code != 200) {
                    toastr.error(response.msg);
                } else {
                    toastr.success('恢复成功！');
                }
            }
            else if (info.status == 203) {
                toastr.error(info.response);
                // document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '上传到OSS成功，但是oss访问用户设置的上传回调服务器失败，失败原因是:' + info.response;
            }
            else {
                toastr.error(info.response);
                // document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = info.response;
            }
        });
        uploaderpro.bind('Error', function (up, err) {
            if (err.code == -600) {
                toastr.error("选择的文件太大了!");
            }
            else if (err.code == -601) {
                toastr.error("选择的文件后缀不对!");
            }
            else if (err.code == -602) {
                toastr.error("这个文件已经上传过一遍了!");
            }
            else {
                document.getElementById('console').appendChild(document.createTextNode("\nError xml:" + err.response));
            }
        });

    </script>
<?php View::tplInclude('Public/footer'); ?>