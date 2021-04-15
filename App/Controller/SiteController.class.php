<?php

class SiteController extends BaseController
{

    public function indexAction()
    {
        $tips = [];
        $sys_param = Basic::getSiteCache();
        $apiData = [
          'total' => 0,
          'surplus' => 0
        ];
        if(!empty($sys_param)){
            $result = SignApi::getProfileCount($sys_param['apiAccessKey']);
            if(isset($result['total'])){
                $apiData = $result;
            }
        }
        list($writeAuth, $excuAuth) = $this->__createFileAuth();
        $this->assign('writeAuth', $writeAuth);
        $this->assign('excuAuth', $excuAuth);
        $this->assign('apiData', $apiData);
        $this->assign('tips', $tips);
        $this->display();
    }

    public function configAction()
    {
        $basicId = Basic::$site_cache_id;
        $tips = [];
        if ($_POST) {
            $data = [
                'id' => $basicId,
                //developer
                'apiAccessKey' => trim($_POST['apiAccessKey']),
                //file-oss-cos
                'fileDriver' => trim($_POST['fileDriver']),
                'accessKeyId' => trim($_POST['accessKeyId']),
                'accessKeySecret' => trim($_POST['accessKeySecret']),
                'bucket' => trim($_POST['bucket']),
                'endpoint' => trim($_POST['endpoint']),
                'endpointInternal' => trim($_POST['endpointInternal']),

                'secretId' => trim($_POST['secretId']),
                'secretKey' => trim($_POST['secretKey']),
                'region' => trim($_POST['region']),
                'cosBucket' => trim($_POST['cosBucket']),
            ];
            $oldData = Basic::getSiteCache();
            Basic::putSiteCache($data);
            //设置accessId CORS
            if (!empty($_POST['fileDriver'])) {
                if ($_POST['fileDriver'] == 'oss') {
                    if (empty($_POST['accessKeyId']) || empty($_POST['accessKeySecret']) || empty($_POST['bucket']) || empty($_POST['endpoint'])) {
                        $tips['error'][] = '存储引擎阿里云参数配置错误！';
                    } else {
                        if (empty($oldData['apiAccessKey']) || ($oldData['bucket'] != $_POST['bucket'])) {
                            $obj = new Oss($data);
//                          $data = $obj->getAllDomainCROS($_POST['bucket']);
                            $domain = Basic::getMyDomain();
                            $result = $obj->addDomainCROS($domain);
                            if ($result !== true)
                                $tips['error'][] = 'OSS跨域政策CORS设置失败，请确认参数或手动设置！';

                        }
                    }
                } else if ($_POST['fileDriver'] == 'cos') {
                    if (empty($_POST['apiAccessKey']) || empty($_POST['region']) || empty($_POST['cosBucket'])) {
                        $tips['error'][] = '存储引擎腾讯云参数配置错误！';
                    } else {
                        if (empty($oldData['apiAccessKey']) || ($oldData['cosBucket'] != $_POST['cosBucket'])) {
                            $obj = new Cos($data);
                            $domain = Basic::getMyDomain();
                            $result = $obj->addDomainCROS($domain);
                            if ($result !== true)
                                $tips['error'][] = 'Cos跨域政策CORS设置失败，请确认参数或手动设置！';

                        }
                    }

                }
            }


            //更新密码
            if (!empty($_POST['newPass']) && !empty($_POST['newPassConfirm'])) {
                if ($_POST['newPass'] != $_POST['newPassConfirm']) {
                    $tips['error'][] = '新密码两次密码不一致，新密码更新失败！';
                } else {
                    $safeId = Basic::$safe_cache_id;
                    $safeOldData = File::search('site', 'id', $safeId);
                    if (!empty($safeOldData['loginPass']) && (trim($_POST['oldPass']) != $safeOldData['loginPass'])) {
                        $tips['error'][] = '旧密码校验不通过，新密码更新失败！';
                    } else {
                        $safeNewData = [
                            'id' => $safeId,
                            'username' => trim($_POST['username']),
                            'loginPass' => trim($_POST['newPassConfirm'])
                        ];
                        Basic::putSiteCache($safeNewData);
                    }

                }

            }
            if (empty($tips))
                $tips['success'][] = '操作成功！';
        }
        $data = Basic::getSiteCache();
        $this->assign('data', $data);
        $this->assign('tips', $tips);
        $this->display();
    }

    public function getUploadParamAction()
    {
        $siteData = Basic::getSiteCache();
        $type = $_GET['type'];
        if ($type) {
            switch ($siteData['fileDriver']) {
                case "oss":
                    $obj = new Oss($siteData);
                    $data = $obj->getParam($type);
                    break;
                case "cos":
                    $obj = new Cos($siteData);
                    $mydomain = Basic::getMyDomain();
                    $data = $obj->createStsParams($type, $mydomain);
                    break;
                default:
                    break;
            }
            $this->ajaxReturn($data);
        }

    }

    /**
     * 文件上传
     */
    public function fileUploadAction()
    {
        if($_FILES && !empty($_GET['type'])){
            $up = new FileUpload();
            //设置属性(上传的位置， 大小， 类型， 名是是否要随机生成)
            $path = $this->__getDirByType($_GET['type']);
            $up -> set("path", $path);
            $up -> set("maxsize", 1024*1024*1024);
            $up -> set("allowtype", array("apk", "ipa", "png", "jpg","jpeg"));
            //使用对象中的upload方法， 就可以上传文件， 方法需要传一个上传表单的名子 pic, 如果成功返回true, 失败返回false
            if($up -> upload("file")) {
                $this->ajaxReturn(['code' => 200, 'file' => Basic::getMyDomain() . '/' . str_replace(C('ROOT_PATH'), '', $path . $up->getFileName())]);
            } else {
                $this->ajaxReturn(['code'=>400, 'msg'=>$up->getErrorMsg()]);
            }
        }

    }

    //根据不同类型文件存储不同路径
    private function __getDirByType($type)
    {
        switch ($type) {
            case "app":
                $dir = C('UPLOAD_PATH') . "app/";
                break;
            case "icon":
                $dir = C('UPLOAD_PATH') . "icon/";
                break;
            default:
                $dir = C('UPLOAD_PATH');
                break;
        }
        return $dir;
    }

    private function __createFileAuth()
    {
        $data_path = C('APP_PATH') . 'Data';
        $file_path = C('UPLOAD_PATH');
        $sign_file = C('ROOT_PATH') . 'public/sign/sign';
        $excute = true;
        $read = true;
        if (!is_writable($data_path) || !is_writable($file_path)) {
            return false;
        }

        if (!is_executable($sign_file)) {
            $excute = false;
        }
        return [$read, $excute];
    }

    public function logAction()
    {
        $logPath = C('APP_FULL_PATH') . '/Log/' . date('Ymd') . '.php';
        $data = '';
        if (file_exists($logPath)) {
            $data = $this->__readBySeek($logPath, 500, true);
        }
        $this->assign('data', $data);
        $this->display();
    }

    private function __readBySeek($filepath, $lines, $revers = false)
    {
        $offset = -1;
        $c = '';
        $read = '';
        $i = 0;
        $fp = fopen($filepath, "r");
        while ($lines && fseek($fp, $offset, SEEK_END) >= 0) {
            $c = fgetc($fp);
            if ($c == "\n" || $c == "\r") {
                $lines--;
                if ($revers) {
                    @$read[$i] = strrev($read[$i]);
                    $i++;
                }
            }
            if ($revers) @$read[$i] .= $c;
            else $read .= $c;
            $offset--;
        }
        fclose($fp);
        if ($revers) {
            if ($read[$i] == "\n" || $read[$i] == "\r")
                array_pop($read);
            else $read[$i] = strrev($read[$i]); //反转字符串
            return implode('', $read);
        }
        return strrev(rtrim($read, "\n\r"));
    }
}