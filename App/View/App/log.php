<?php View::tplInclude('Public/header'); ?>
<div class="container" role="main">
    <div class="row" style="margin-bottom: 30px;">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">应用信息</h3>
            </div>
                <ul class="list-group">
                    <li class="list-group-item">应用名： <?=$app['name']?></li>
                    <li class="list-group-item">ID识别号： <?=$app['id']?></li>
                    <li class="list-group-item">版本号： <?=$app['version_name']?></li>
                </ul>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <?php if ($app['platform'] == 'iOS') { ?>
                    <th>udid</th>
                    <th>是否扣量</th>
                    <th>设备</th>
                <?php } ?>
                <th>版本号</th>
                <th>ip</th>
                <th>下载时间</th>

            </tr>
            </thead>
            <tbody>
            <?php if($data){ ?>
            <?php foreach ($data as $k=>$v): $v=json_decode($v, true); ?>
                <tr class="tr-app-<?php echo $k; ?>">
                    <?php if ($app['platform'] == 'iOS') { ?>
                        <td><?= $v['udid'] ?></td>
                        <td>
                            <?= $v['overlap'] ? '是' : '否'; ?>
                        </td>
                        <td><?= $v['device_product'] ?></td>
                    <?php } ?>
                    <td><?=$v['version_name']?></td>
                    <td><?=$v['ip']?></td>
                    <td><?=date('Y-m-d H:i', $v['ctime'])?></td>

                </tr>
            <?php endforeach; }else{ ?>
                <tr>
                    <td colspan="6">暂无数据</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <nav class="pull-right">
        <ul class="pagination">
            <?php if ($page==1): ?>
                <li class="disabled">
                    <span><span aria-hidden="true">&laquo;</span></span>
                </li>
            <?php else: ?>
                <li><a href="?c=app&a=log&id=<?php echo $data['id']; ?>&page=<?php echo $page-1; ?>">&laquo;</a></li>
            <?php endif; ?>

            <?php $start = $page-2;
            $maxPage=ceil($app['install_count']/$limit);
            $showPage = $maxPage<=5? $maxPage:5;
            if ($page+2>$maxPage) $start = $maxPage-4;
            if ($start<1) $start=1;
            for ($i=0; $i<$showPage; $i++): ?>
                <?php if ($i+$start==$page): ?>
                    <li class="active">
                        <span><?php echo $start+$i; ?> <span class="sr-only"></span></span>
                    </li>
                <?php else: ?>
                    <li><a href="?c=app&a=log&id=<?php echo $app['id']; ?>&page=<?php echo $i+$start; ?>"><?php echo $i+$start; ?></a></li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page==$maxPage || $maxPage==0): ?>
                <li class="disabled">
                    <span><span aria-hidden="true">&raquo;</span></span>
                </li>
            <?php else: ?>
                <li>
                    <a href="?c=app&a=log&id=<?php echo $app['id']; ?>&page=<?php echo $page+1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
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
</script>
<?php View::tplInclude('Public/footer'); ?>
