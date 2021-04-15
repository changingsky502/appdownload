<?php

class IndexController extends BaseController
{

    public function downloadAction()
    {

        $id = @$_GET['id'];
        $result = [];
        $result['safari'] = 1;
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $udid = '';
        $token_last = '';
        if (stripos($agent, 'qq') || stripos($agent, 'android') || !stripos($agent, 'safari')) {
            $result['safari'] = 2;
        }
        $system_param = Basic::getSiteCache();
        if (empty($id) && empty($system_param)) {
            header("location:index.php?c=index&a=login");
            exit;
        }
        $app = File::find('app', 'id', $id);
        if (empty($app)) {
            exit;
        }
        //环境，信息
        $result['is_wx'] = $this->__is_weixin();
        $result['is_qq'] = $this->__is_qq();
        $result['is_ios'] = $this->__get_device_type();
        $result['qrcode_url'] = Basic::getMyDomain() . "/?id=" . $id;
        //查关联应用
        if (($result['is_ios'] != 1 && $app['platform'] == 'iOS') || ($result['is_ios'] == 1 && $app['platform'] == 'Android') && $app['relation_id']) {
            header("location:" . Basic::getMyDomain() . '/?id=' . $app['relation_id']);
            exit;
        }
        //超级签名
        if ($app['install_type'] == 2) {
            if (empty($system_param['apiAccessKey']))
                exit('未配置系统开发者信息！');
            $http_type = Basic::getHttpType();
            if ($http_type == 'http://')
                exit('超级签名需要域名https协议才能使用！');
        }
        //验签
        if (!empty($_GET['udid'])) {
            $token = $_GET['token'];
            $sign = myMd5($_GET['id'] . $_GET['tm'] . $_GET['udid'], $system_param['apiAccessKey']);
            if (time() - $_GET['tm'] > 3600) {
                header("location:{$result['qrcode_url']}");
                exit;
            }
            if ($token != $sign) {
                header("location:{$result['qrcode_url']}");
                exit;
            }
            $udid = $_GET['udid'];
            $token_last = $_GET['token'];
        }
        list($get_udid_url, $token) = $this->__createGetUdidUrlToken($id, $system_param['apiAccessKey']);
        $result['get_udid_url'] = $get_udid_url;
        $result['token'] = $token;
        $result['download_time'] = $this->__calcAppDownTime($app['size']);
        $result['ios_device_file'] = Basic::getMyDomain() . "/public/static/mobileconfig/1.mobileprovision";
        //企业签名
        if ($app['install_type'] != 2) {
            if ($app['platform'] == 'iOS') {
                $obj = new Plist();
                $plist = $obj->getPlistV2($app);
                $app['install_url'] = create_ios_plist_str(Basic::getMyDomain() . '/' . $plist);
            } else {
                $app['install_url'] = $app['file'];
            }
        }
        $this->assign('app', $app);
        $this->assign('udid', $udid);
        $this->assign('token_last', $token_last);
        $this->assign('system_param', $system_param);
        $this->assign('result', $result);
        $this->display();
    }

    public function IndexAction()
    {
        $this->assign('title', 'SinglePHP');
        $this->display();
    }

    public function loginAction()
    {
        $tips = [];
        if ($_POST && $_POST['username'] && $_POST['password']) {
            $safeId = Basic::$safe_cache_id;
            $safeData = File::search('site', 'id', $safeId);
            if (!empty($safeData) && !empty($safeData['loginPass'])) {
                if (($_POST['username'] == $safeData['username']) && ($_POST['password'] == $safeData['password'])) {
                    $this->__toLogin($_POST['username']);
                } else {
                    $tips['error'][] = '账号或密码错误！';
                }
            } else {
                if (($_POST['username'] == 'admin2020') && ($_POST['password'] == 'pass2020')) {
                    $this->__toLogin($_POST['username']);
                } else {
                    $tips['error'][] = '账号或密码错误！';
                }
            }
        }
        $this->assign('tips', $tips);
        $this->display();
    }

    private function __toLogin($username)
    {
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'];
        $this->redirect('index.php?c=app&a=index');
    }

    public function logOutAction()
    {
        $_SESSION = null;
        session_destroy();
        $this->redirect('index.php?c=index&a=login');
    }

    public function ossCallBackAction()
    {
        // 1.获取OSS的签名header和公钥url header
        $authorizationBase64 = "";
        $pubKeyUrlBase64 = "";
        /*
         * 注意：如果要使用HTTP_AUTHORIZATION头，你需要先在apache或者nginx中设置rewrite，以apache为例，修改
         * 配置文件/etc/httpd/conf/httpd.conf(以你的apache安装路径为准)，在DirectoryIndex index.php这行下面增加以下两行
            RewriteEngine On
            RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization},last]
         * */
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authorizationBase64 = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (isset($_SERVER['HTTP_X_OSS_PUB_KEY_URL'])) {
            $pubKeyUrlBase64 = $_SERVER['HTTP_X_OSS_PUB_KEY_URL'];
        }

        if ($authorizationBase64 == '' || $pubKeyUrlBase64 == '') {
            header("http/1.1 403 Forbidden");
            exit();
        }
// 2.获取OSS的签名
        $authorization = base64_decode($authorizationBase64);
// 3.获取公钥
        $pubKeyUrl = base64_decode($pubKeyUrlBase64);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $pubKey = curl_exec($ch);
        if ($pubKey == "") {
            //header("http/1.1 403 Forbidden");
            exit();
        }
// 4.获取回调body
        $body = file_get_contents('php://input');
// 5.拼接待签名字符串
        $authStr = '';
        $path = $_SERVER['REQUEST_URI'];
        $pos = strpos($path, '?');
        if ($pos === false) {
            $authStr = urldecode($path) . "\n" . $body;
        } else {
            $authStr = urldecode(substr($path, 0, $pos)) . substr($path, $pos, strlen($path) - $pos) . "\n" . $body;
        }
// 6.验证签名
        $ok = openssl_verify($authStr, $authorization, $pubKey, OPENSSL_ALGO_MD5);
        if ($ok == 1) {
            header("Content-Type: application/json");
            $data = array("Status" => "Ok");
            $this->ajaxReturn($data);
        } else {
            //header("http/1.1 403 Forbidden");
            exit();
        }
    }

    //判断是否在微信中打开
    private function __is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return 1;
        } else {
            return 0;
        }
    }

    //判断是否在qq打开
    private function __is_qq()
    {
        $sUserAgent = strtolower($_SERVER["HTTP_USER_AGENT"]);
        //echo $sUserAgent;die();
        if (strpos($sUserAgent, "qq") !== false) {
            if (strpos($sUserAgent, "mqqbrowser") !== false && strpos($sUserAgent, "pa qq") === false || (strpos($sUserAgent, "qqbrowser") !== false && strpos($sUserAgent, "mqqbrowser") === false)) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 0;
        }
    }

    //判断手机类型
    private function __get_device_type()
    {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = '3';
        //分别进行判断
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad') || strpos($agent, 'ios') || (strpos($agent, 'macintosh') && !strpos($agent, 'chrome'))) {
            $type = 1;
        }
        if (strpos($agent, 'android') || strpos($agent, 'linux')) {
            $type = 0;
        }
        return $type;
    }

    public function getUdidMobileConfigAction()
    {
        if ($_POST) {
            $system_param = Basic::getSiteCache();
            $sign = myMd5($_GET['id'] . $_GET['tm'], $system_param['apiAccessKey']);
            if ($sign != $_POST['token']) {
                $this->ajaxReturn(['code' => 0, 'msg' => '未知错误!']);
            }
            $app = File::find('app', 'id', $_GET['id']);
            if ($app) {
                $obj = new MobileConfig();
                $result = $obj->run($app, $system_param['apiAccessKey']);
                if ($result !== false) {
                    $this->ajaxReturn(['code' => 200, 'udid_mobile_config' => $result]);
                }
            }
        }
        $this->ajaxReturn(['code' => 400, 'udid_mobile_config' => '']);
    }

    private function __createGetUdidUrlToken($id, $salt)
    {
        $base_url = "index.php?c=index&a=getUdidMobileConfig";
        $tm = time();
        $token = myMd5($id . $tm, $salt);
        $url = $base_url . "&id={$id}&tm={$tm}";
        $_SESSION['download_token'] = $tm;
        return [$url, $token];
    }

    public function getUdidAction()
    {
        $data = file_get_contents('php://input');
        $plistBegin = '<?xml version="1.0"';
        $plistEnd = '</plist>';
        $pos1 = strpos($data, $plistBegin);
        $pos2 = strpos($data, $plistEnd);
        $data2 = substr($data, $pos1, $pos2 - $pos1);
        $xml = xml_parser_create();
        xml_parse_into_struct($xml, $data2, $vs);
        xml_parser_free($xml);

        $UDID = "";
        $CHALLENGE = "";
        $DEVICE_NAME = "";
        $DEVICE_PRODUCT = "";
        $DEVICE_VERSION = "";

        $iterator = 0;
        $arrayCleaned = array();
        foreach ($vs as $v) {
            if ($v['level'] == 3 && $v['type'] == 'complete') {
                $arrayCleaned[] = $v;
            }
            $iterator++;
        }

        $data = "";
        $iterator = 0;
        foreach ($arrayCleaned as $elem) {
            if (!empty($elem['value'])) {
                $data .= "\n==" . $elem['tag'] . " -> " . $elem['value'] . "<br/>";
                switch ($elem['value']) {
                    case "CHALLENGE":
                        $CHALLENGE = $arrayCleaned[$iterator + 1]['value'];
                        break;
                    case "DEVICE_NAME":
                        $DEVICE_NAME = $arrayCleaned[$iterator + 1]['value'];
                        break;
                    case "PRODUCT":
                        $DEVICE_PRODUCT = $arrayCleaned[$iterator + 1]['value'];
                        break;
                    case "UDID":
                        $UDID = $arrayCleaned[$iterator + 1]['value'];
                        break;
                    case "VERSION":
                        $DEVICE_VERSION = $arrayCleaned[$iterator + 1]['value'];
                        break;
                }
            }

            $iterator++;

        }
        if (empty($_GET['id']) || empty($_GET['tm']) || empty($_GET['token'])) {
            header("location:http://127.0.0.1");
            exit;
        }
        $id = $_GET['id'];
        $tm = $_GET['tm'];
        $token = $_GET['token'];
        $system_param = Basic::getSiteCache();
        //验签
        $sign = myMd5($tm . $id, $system_param['apiAccessKey']);
        if (time() - $tm > 300) {
            header("location:http://127.0.0.1");
            exit;
        }
        if ($sign != $token) {
            header("location:http://127.0.0.1");
            exit;
        }

        $app = File::find('app', 'id', $id);
        if (empty($app)) {
            header("location:http://127.0.0.1");
            exit;
        }
        //新签
        $token = myMd5($_GET['id'] . $tm . $UDID, $system_param['apiAccessKey']);
        $params = "id=" . $id . "&udid=" . $UDID . "&device_product=" . $DEVICE_PRODUCT . "&tm=" . $tm . "&token=" . $token;
        $host = 'https://' . $_SERVER['HTTP_HOST'];

        header('HTTP/1.1 301 Moved Permanently');
        header("Location: {$host}/?" . $params);
        exit;
    }

    public function getIpaAction()
    {
        if ($_POST) {
            $id = $_POST['id'];
            $udid = $_POST['udid'];
            $tm = $_POST['tm'];
            $token = $_POST['token'];
            $device_product = $_POST['device_product'];
            if ($id && $udid && $tm && $token) {
                if (strlen($udid) != 40 && strlen($udid) != 25) {
                    $this->ajaxReturn(['code' => 400, 'msg' => '未知错误！']);
                }
                $system_param = Basic::getSiteCache();
                $sign = myMd5($id . $tm . $udid, $system_param['apiAccessKey']);

                if (time() - $_POST['tm'] > 3600) {
                    $this->ajaxReturn(['code' => 400, 'msg' => '未知错误！']);
                }

                if ($token != $sign) {
                    $this->ajaxReturn(['code' => 400, 'msg' => '未知错误！']);
                }
                $downloading_log = File::find('download', 'id', $udid);
                if ($downloading_log && (time() < $downloading_log['expire_time'])) {
                    $this->ajaxReturn(['code' => 300, 'msg' => '正在签包，请稍后...']);
                }
                $app = File::find('app', 'id', $id);
                if (empty($app) || $app['status'] != 1)
                    $this->ajaxReturn(['code' => 400, 'msg' => '应用错误！']);
                //限量检测
                if ($app['install_day_max']) {
                    $installed_day_num = 1;
                    if ($installed_day_num >= $app['install_day_max'])
                        $this->ajaxReturn(['code' => 400, 'msg' => '抱歉，下载量不足，请联系管理员~']);
                }
                if ($app['install_count_max'] && ($app['install_count_max'] >= $app['install_count'])) {
                    $this->ajaxReturn(['code' => 400, 'msg' => '抱歉，下载量不足，请联系管理员！']);
                }
                $all = File::all('download');
                if(count($all)> 5) {
                    File::delete('download', 'id', 'delete_expired');//防堵
                    $this->ajaxReturn(['code' => 300, 'msg' => '队列中...']);
                }
                //下载请求控制
                $cache_data = [
                    'id' => $udid,
                    'expire_time' => time() + 180,
                ];
                File::save('download', $cache_data);
                $obj = new Sign();
                $result = $obj->run(['udid'=>$udid, 'device_product'=>$device_product, 'app'=>$app]);
                //缓存处理
                File::delete('download', 'id', $udid);
                //返回
                if ($result['code'] == 1) {
                    //生层plist文件
                    $obj = new Plist();
                    $plist = $obj->run(array_merge($app, ['ipa_url' => $result['data']['ipa'],'pic'=>$app['icon'], 'udid'=>$udid,  'bundle_id' => $result['data']['bundle_id']]));
                    if(is_array($plist))
                        $this->ajaxReturn(['code' => 200, 'url' => create_ios_plist_str($plist['file'])]);
                    else
                        $this->ajaxReturn(['code'=>400, 'msg'=> 'plist创建失败！']);
                }else{
                    $this->ajaxReturn(['code'=>400, 'msg'=> '']);
                }
            }
        }
    }

    private function __calcAppDownTime($size)
    {
        $sign_seconde_size = 3 * 1024 * 1024;// 8M1秒
        $updload_seconde_size = 10 * 1024 * 1024;// 50M1秒
        $total = 10;
        $size = strtolower($size);
        $app['size'] = strtolower(str_replace(" ","", $size));
        if(strpos($size, 'kb') !== false){
            $app_size = floatval($size) * 1024;
        }else if(strpos($size, 'mb') !== false){
            $app_size = floatval($size) * 1024 * 1024;
        }else if(strpos($size, 'g') !== false){
            $app_size = floatval($size) * 1024 * 1024 * 1024;
        }
        $total += ceil($app_size/ $sign_seconde_size);
        $total += ceil($app_size/ $updload_seconde_size);
        return $total;
    }

    public function apkInstallAction()
    {
        $id = $_POST['id'];
        $download_token = $_SESSION['download_token'];
        if ($id && $download_token && empty($_SESSION["{$id}_counted"])) {
            $app = File::find('app', 'id', $id);
            if (empty($app)) {
                exit;
            }
            $log = [
                'ip' => get_ip(),
                'version_name' => $app['version_name'],
                'ctime' => time()
            ];
            SoloAppDataLog::write($log, $id);
            SoloAppDataLog::addInstallNum($id);
            $_SESSION["{$id}_counted"] = time();
        }
    }

}
