<?php View::tplInclude('Public/header'); ?>
<div class="container" role="main">
    <ol class="breadcrumb">
        <li><a href="?c=app&a=index">应用管理</a></li>
        <li class="active">上传应用</li>
    </ol>
    <form class="form-horizontal" method="post" action="">
        <div class="form-group">
            <label class="col-sm-2 control-label">状态*</label>
            <div class="col-sm-10">
                <label class="radio-inline">
                    <input type="radio" name="status" value="1" checked> 正常
                </label>
                <label class="radio-inline">
                    <input type="radio" name="status" value="0"> 暂停安装
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">应用名*</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" required placeholder="应用名">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">图标*</label>
            <div class="col-sm-10">
                <input type="file" required name="icon">
                <p class="help-block"></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">安装包*</label>
            <div class="col-sm-10">
                <input type="file" required name="package">
                <p class="help-block"><span class="text-danger">Example block-level help text here.</span></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">关联应用</label>
            <div class="col-sm-10">
                <select name="relation" class="form-control">
                    <option value="0">无</option>
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                    <option>5</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">日限额</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="install_day_max" placeholder="不填不限制">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">总限额</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="install_count_max" placeholder="不填不限制">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">描述</label>
            <div class="col-sm-10">
                <textarea name="summary" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-default">提交</button>
            </div>
        </div>
    </form>
</div>

<?php View::tplInclude('Public/footer'); ?>
