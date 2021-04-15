<?php View::tplInclude('Public/header'); ?>
<div class="container" role="main">
    <div class="row" style="margin-bottom: 30px;">
        <div class="col-lg-4">
            <form method="post" action="">
                <div class="input-group">
                    <input type="text" name="name" value="<?php echo $data['name']; ?>" class="form-control" placeholder="应用名">
                    <span class="input-group-btn"><button class="btn btn-default" type="submit">搜索</button></span>
                </div>
            </form>
        </div>
        <div class="col-lg-8">
            <a href="?c=app&a=upload" class="btn btn-primary active pull-right" role="button">上传应用</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>状态</th>
                <th>应用</th>
                <th>类型</th>
                <th>图标</th>
                <th>下载</th>
                <th>版本</th>
                <th>总安装</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data['app']['result'] as $app): ?>
                <tr class="tr-app-<?php echo $app['id']; ?>">
                <th scope="row"><?php echo $app['id']; ?></th>
                    <td><?php echo $app['status'] ? '<span class="label label-success">正常</span>' : '<span class="label label-danger">暂停</span>'; ?></td>
                <td><?php echo $app['name']; ?></td>
                    <td><?php echo $app['platform']; ?></td>
                    <td><img src="<?= $app['icon'] ? $app['icon'] : "public/static/images/default-icon.png" ?>" alt=""
                             width="50px"></td>
                    <td>
                        <button type="button" class="btn btn-xs btn-primary" data-toggle="modal"
                                data-target="#exampleModal" data-name="<?= $app['name'] ?>"
                                data-url="<?= Basic::getMyDomain() . '/?id=' . $app['id'] ?>">地址/二维码
                        </button>
                    </td>
                    <td><?php echo $app['size']; ?></td>
                <td><?php echo SoloAppDataLog::getInstallNum($app['id']); ?></td>
                    <td>
                        <a href="?c=app&a=upload&id=<?php echo $app['id']; ?>">[更新]</a>
                        |
                        <a href="?c=app&a=edit&id=<?php echo $app['id']; ?>">[编辑]</a>
                        |
                        <a href="?c=app&a=log&id=<?php echo $app['id']; ?>" >[下载记录]</a>
                        |
                        <a href="javascript:;" onclick='return appDelete("<?php echo $app['id']; ?>")'>[删除]</a>
                    </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <nav class="pull-right">
        <ul class="pagination">
            <?php if ($data['app']['page']==1): ?>
            <li class="disabled">
                <span><span aria-hidden="true">&laquo;</span></span>
            </li>
            <?php else: ?>
            <li><a href="?c=app&a=index&name=<?php echo $data['name']; ?>&page=<?php echo $data['app']['page']-1; ?>">&laquo;</a></li>
            <?php endif; ?>

            <?php $start = $data['app']['page']-2;
            $maxPage=ceil($data['app']['count']/$data['app']['limit']);
            $showPage = $maxPage<=5? $maxPage:5;
            if ($data['app']['page']+2>$maxPage) $start = $maxPage-4;
            if ($start<1) $start=1;
            for ($i=0; $i<$showPage; $i++): ?>
            <?php if ($i+$start==$data['app']['page']): ?>
            <li class="active">
                <span><?php echo $start+$i; ?> <span class="sr-only"></span></span>
            </li>
            <?php else: ?>
            <li><a href="?c=app&a=index&name=<?php echo $data['name']; ?>&page=<?php echo $i+$start; ?>"><?php echo $i+$start; ?></a></li>
            <?php endif; ?>
            <?php endfor; ?>

            <?php if ($data['app']['page']==$maxPage): ?>
            <li class="disabled">
                <span><span aria-hidden="true">&raquo;</span></span>
            </li>
            <?php else: ?>
            <li>
                <a href="?c=app&a=index&name=<?php echo $data['name']; ?>&page=<?php echo $data['app']['page']+1; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">New message</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        下载链接：<a href="" class="download-link" target="_blank"></a>
                    </div>
                    <div class="form-group ">
                        扫描二维码：<a href="javascript:;" class="download-qrcode" target="_blank" onclick=" downloadClick()">下载</a>
                        <div class="" id="qrcode"></div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<script src="public/static/js/jquery.qrcode.min.js"></script>
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
    function appDelete(id) {
        var r = confirm("确认删除？")
        if (r == true) {
            $.post("index.php?c=app&a=delete", {id, id}, function (r) {
                if (r.code == 200) {
                    toastr.success("删除成功！");
                    $(".tr-app-" + id).hide()
                } else {
                    toastr.error("删除失败！");
                }
            }, "json")
        }
    }
    $('#exampleModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget)
        var name = button.data('name')
        var download_url = button.data('url')
        var modal = $(this)
        modal.find('.modal-title').text(name + '-下载信息')
        $("#qrcode").html('');
        $("#qrcode").qrcode({
            render: "canvas", //table方式
            width: 140, //宽度
            height: 140, //高度
            text: download_url //任意内容
        });
        $('.download-link').html(download_url).attr('href', download_url)
    })
    function downloadClick() {
        var data = $("canvas")[0].toDataURL().replace("image/png", "image/octet-stream;"); //获取二维码值，并修改响应头部。　　
        var filename = "download.png"; //保存的图片名称和格式
        var saveLink= document.createElementNS('http://www.w3.org/1999/xhtml', 'a');
        saveLink.href = data;
        saveLink.download = filename;
        var event = document.createEvent('MouseEvents');
        event.initMouseEvent('click', true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
        saveLink.dispatchEvent(event);
    }
</script>
<?php View::tplInclude('Public/footer'); ?>
