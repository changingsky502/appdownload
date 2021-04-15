<?php


class AppController extends BaseController
{

    public function indexAction()
    {
        if (isset($_POST['name'])) $this->redirect('?c=app&a=index&name=' . $_POST['name']);
        $tips = [];
        $name = !empty($_GET['name']) ? $_GET['name']: null;
        $page = !empty($_GET['page']) ? $_GET['page']: 1;
        $app = File::search('app', 'name', $name, $page);
        $this->assign('data', [
            'app' => $app,
            'name' => $name,
            'tips' => $tips,
        ]);

        $this->display();
    }


    public function editAction()
    {
        $app = File::find('app', 'id', $_GET['id']);
        if ($_POST) {
            $app['status'] = $_POST['status'];
            $app['name'] = $_POST['name'];
            $app['icon'] = $_POST['icon'];
            $app['package'] = $_POST['package'];
            $app['android_link'] = $_POST['android_link'];
            $app['relation'] = $_POST['relation'];
            $app['use_vpn'] = $_POST['use_vpn'];
            $app['bundle_id'] = $_POST['bundle_id'];
            $app['install_day_max'] = $_POST['install_day_max'];
            $app['install_count_max'] = $_POST['install_count_max'];
            $app['summary'] = $_POST['summary'];
            $app['relation_id'] = $_POST['relation_id'];
            $app['install_type'] = $_POST['install_type'];
            $app['utime'] = strtotime('now');
            if($_POST['use_vpn'] != $app['use_vpn'])
                $app['version_code'] = $app['version_code'] +1;
            File::save('app', $app);
            //相互关联
            if ($app['relation_id']) {
                $related_app = File::find('app', 'id', $app['relation_id']);
                if ($related_app['relation_id'] != $app['id']) {
                    $related_app['relation_id'] = $app['id'];
                    File::save('app', $related_app);
                }
            }
            $this->redirect('?c=app&a=index');
        }
        $relation = File::search('app', 'platform', $app['platform'] == 'iOS' ? 'Android' : 'iOS', 1, 999);
        $related_apps = $this->__getRelatedApps($app['platform']);
        $params = Basic::getSiteCache();
        $this->assign('data', [
            'app' => $app,
            'related_apps' => $related_apps,
            'params' => $params,
            'relation' => $relation
        ]);
        $this->display();
    }

    public function deleteAction()
    {
        $id = $_POST['id'];
        if ($id) {
            $result = File::delete("app", "id", $id);
            if ($result)
                $this->ajaxReturn(['code' => 200, 'msg' => '删除成功！']);
            else
                $this->ajaxReturn(['code' => 200, 'msg' => '删除失败！']);
        }
    }


    public function uploadAction()
    {
        $params = Basic::getSiteCache();
        $tips = [];
        $id = 0;
        $app = [];
        if (empty($params['fileDriver'])) {
            $tips['error'][] = '没有正确配置文件存储引擎或者开发者秘钥，请前往系统配置';
        }
        //旧app
        if (!empty($_GET['id']) || !empty($_POST['id'])) {
            $id = !empty($_POST['id']) ? $_POST['id'] : $_GET['id'];
            $app = File::find('app', 'id', $id);
        }
        //新增或更新
        if ($_POST) {
            if ($id && $app) {
                $data = [
                    'id' => $id,
                    'status' => $app['status'],
                    'name' => $_POST['name'],
                    'icon' => $_POST['icon'],
                    'bundle_id' => $_POST['bundle_id'],
                    'file' => $_POST['file'],
                    'install_day_max' => $_POST['install_day_max'],
                    'install_count_max' => $_POST['install_count_max'],
                    'summary' => $_POST['summary'],
                    'version_name' => $_POST['version_name'],
                    'version_code' => $app['version_code'] + 1,
                    'use_vpn' => $app['use_vpn'],
                    'platform' => $_POST['platform'],
                    'install_type' => $_POST['install_type'],
                    'size' => $_POST['size'],
                    'install_count' => $app['install_count'],
                    'ctime' => $app['ctime'],
                    'utime' => strtotime('now'),
                ];
            } else {
                $data = [
                    'id' => get_nonce(4),
                    'status' => Basic::STATUS_VALID,
                    'name' => $_POST['name'],
                    'icon' => $_POST['icon'],
                    'file' => $_POST['file'],
                    'bundle_id' => $_POST['bundle_id'],
                    'install_day_max' => $_POST['install_day_max'],
                    'install_count_max' => $_POST['install_count_max'],
                    'summary' => $_POST['summary'],
                    'version_name' => $_POST['version_name'],
                    'version_code' => 1,
                    'use_vpn' => 0,
                    'platform' => $_POST['platform'],
                    'install_type' => $_POST['install_type'],
                    'size' => $_POST['size'],
                    'install_count' => 0,
                    'ctime' => strtotime('now'),
                    'utime' => strtotime('now'),
                ];
            }

            File::save('app', $data);
            //download
            $obj = new Sign();
            $obj->getLocalIpa($data['file']);
            $this->redirect('?c=app&a=index');
            exit;
        }
        $this->assign('tips', $tips);
        $this->assign('app', $app);
        $this->assign('params', $params);
        $this->display();
    }

    public function parseAction()
    {
        $icon = empty($_POST['icon']) ? '' : $_POST['icon'];//base64
        if ($icon) {
            $icon = base64_to_image($icon, C('UPLOAD_PATH'));
            $param = Basic::getSiteCache();
            if ($param['fileDriver'] == 'oss') {
                $ossClass = new Oss($param);
                $file = $ossClass->upload($icon, '', 'icon');
            } else if ($param['fileDriver'] == 'cos') {
                $ossClass = new Cos($param);
                $file = $ossClass->upFile($icon, '', 'icon');
            } else {
                $host = Basic::getMyDomain();
                $file = $host . '/' . str_replace(C('ROOT_PATH'), '', $icon);
            }

            $this->ajaxReturn(['code' => 200, 'icon' => $file]);
        }
        $this->ajaxReturn(['code' => 400, 'icon' => '']);
    }

    public function iconUploadAction()
    {
        if ($_FILES) {
            $up = new FileUpload();
            //设置属性(上传的位置， 大小， 类型， 名是是否要随机生成)
            $path = C('UPLOAD_PATH') . "icon/" . date('Ymd') . '/';
            $up->set("path", $path);
            $up->set("maxsize", 1024 * 1024 * 1024);
            $up->set("allowtype", array("apk", "ipa", "png", "jpg", "jpeg"));
            //使用对象中的upload方法， 就可以上传文件， 方法需要传一个上传表单的名子 pic, 如果成功返回true, 失败返回false
            if ($up->upload("file")) {
                $this->ajaxReturn(['code' => 200, 'file' => Basic::getMyDomain() . '/' . str_replace(C('ROOT_PATH'), '', $path . $up->getFileName())]);
            } else {
                $this->ajaxReturn(['code' => 400, 'msg' => $up->getErrorMsg()]);
            }
        }
        $this->ajaxReturn(['code' => 400, 'icon' => '']);
    }

    public function logAction()
    {
        $id = $_GET['id'];
        $page = !empty($_GET['page']) ?: 1;
        $limit = 10;
        $app = File::find('app', 'id', $id);
        $data = SoloAppDataLog::getInstallLogs($id, $page, $limit);
        $this->assign('limit', $limit);
        $this->assign('page', $page);
        $this->assign('app', $app);
        $this->assign('data', $data);
        $this->display();
    }

    private function __getRelatedApps($type)
    {
        $all_apps = $app = File::all('app');
        $new_data = [];
        foreach ($all_apps as $v) {
            if ($v['platform'] != $type) {
                $new_data[] = $v;
            }
        }
        return $new_data;
    }




}