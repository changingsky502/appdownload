<?php


class Sign
{

    public $test;
    public $device_product;
    public $resign;
    public $create;
    public $overlap;
    public $appId;
    public $app;
    public $udid;
    public $signPath;
    public $outputLocalIpa;
    public $outPutPath;



    public function run($param)
    {
        //初始化参数
        $initStatus = $this->initParam($param);
        if (is_int($initStatus))
            return $this->returnRes($initStatus);
        //拉描述文件
        $sysParam = Basic::getSiteCache();
        $signResult = SignApi::getUdidProfile($this->udid, $sysParam['apiAccessKey'], $this->app['use_vpn']);
        if ($signResult == false)
            return $this->returnRes(401);
        //安装记录
        $installLog = $this->getAppInstallLog();
        //扣量标识
        if ($signResult['expend']) { //直接扣量
            $this->overlap = 1;
        } else {
            $this->overlap = 0;
        }
        //是否重签
        if ($installLog) {
            if ($this->overlap) {
                $this->resign = 1;
            } else {
                if ($installLog['version_sign'] == $this->app['version_code'] && (time() - $installLog['ctime'] < 6000)) {
                    //返回旧包
                    if(empty($_SESSION['retry'])){
                        $is_exist = $this->checkOldIpaExist($sysParam);
                        if($is_exist){
                            $ipaUrl = $this->createUrlLocation($sysParam);
                            return $this->returnRes(1, ['ipa'=>$ipaUrl, 'bundle_id'=>$this->app['bundle_id']]);
                        }
                    }
                }

            }
        }
        //记录数据
        $this->setSignData();
        //签包
        list($profile, $p12) = $this->combileProfileP12($signResult);
        $signRes = $this->signIpa($profile, $p12);
        if ($signRes !== true)
            return $this->returnRes($signRes);
        //返回签包下载地址
        $uploadRes = $this->ipaUploadUrl($sysParam);
        if (is_int($uploadRes))
            return $this->returnRes($uploadRes);
        else
            return $this->returnRes(1, ['ipa' => $uploadRes, 'bundle_id' => $this->app['bundle_id']]);
    }

    #初始化参数
    public function initParam($param)
    {
        if (empty($param['app']) || empty($param['udid']))
            return 400;

        $this->app = $param['app'];
        $this->appId = $param['app']['id'];
        $this->udid = $param['udid'];
        $this->device_product = $param['device_product'];
        $this->resign = 0;
        $this->overlap = 0;
        $this->signPath = C('ROOT_PATH') . 'public/sign';
        $this->outPutPath = $this->signPath . '/output/';
        return true;
    }

    #是否测试机
    public function isTester()
    {
        return false;
    }

    #版本号-1
    public function subAppVersionSign()
    {
        $_SESSION['retry'] = 1;
    }

    public function resetAppVersionSign()
    {
        $_SESSION['retry'] = null;
    }

    #获取安装记录
    public function getAppInstallLog()
    {
        $contents = SoloAppDataLog::find('', $this->appId, $this->udid);
        if (empty($contents)) {
            return false;
        } else {
            return json_decode(end($contents), true);
        }
    }

    #检查本地的ipa包
    public function getLocalIpa($oriIpa)
    {
        if(empty($oriIpa))
            return false;

        $parse_res = parse_url($oriIpa);
        $localIpa = C('ROOT_PATH') . $parse_res['path'];
        if(file_exists($localIpa))
            return $localIpa;
        $ossLocalIpa = C('UPLOAD_PATH') . $parse_res['path'];
        if (file_exists($ossLocalIpa))
            return $ossLocalIpa;

//        if(!is_dir(dirname($localIpa))){
//            mkdir(dirname($localIpa), 755, true);
//        }
//        set_time_limit(24 * 60 * 60);
//        $file = fopen($oriIpa, "rb");
//        if ($file) {
//            $newf = fopen($localIpa, "wb");
//            if ($newf)
//                while (!feof($file)) {
//                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
//                }
//        }
//        if ($file) {
//            fclose($file);
//        }
//        if ($newf) {
//            fclose($newf);
//        }
        $downloadResult = cmd('wget -P ' . dirname($ossLocalIpa) . ' ' . $oriIpa, true, true);
        if (!$downloadResult) {
            Log::warn($this->app['name'].'源包下载出错！');
            return ['code' => -8];
        }

        return $ossLocalIpa;
    }

    #安装记录
    public function setSignData()
    {
        $log = [
            'udid' => $this->udid,
            'device_product' => $this->device_product,
            'version_name' => $this->app['version_name'],
            'version_sign' => $this->app['version_code'],
            'ip' => get_ip(),
            'ctime' => time(),
            'overlap' => $this->overlap ? 1 : 0
        ];
        SoloAppDataLog::write($log, $this->appId);
        SoloAppDataLog::addInstallNum($this->appId);
    }

    #签包
    public function signIpa($profile, $p12)
    {
        //输出路径
        $this->outputLocalIpa = $this->outPutPath . $this->createIpaLocation();
        if (!is_dir(dirname($this->outputLocalIpa)))
            mkdir(dirname($this->outputLocalIpa), 755, true);
        //查本地源包
        $ipa = $this->getLocalIpa($this->app['file']);
        if (is_array($ipa)) {
            return 504;
        }
        //开始签名
        $signResult = $this->app['use_vpn'] ?
            cmd('sh ' . $this->signPath . '/' . 'vpn/vpn_new.sh ' . $ipa . ' ' . $this->app['bundle_id'] . '.vpn ' . $p12 . ' ' . $profile . ' ' . $this->outputLocalIpa, false, true)
            : cmd($this->signPath . '/sign -f -q -k ' . $p12 . ' -m ' . $profile . ' -o ' . $this->outputLocalIpa . ' -z 1 ' . $ipa, false, true);
        if (strpos($signResult, 'Signed OK!') === false || strpos($signResult, 'Archive OK!') === false) { //签名失败
            Log::warn('app id:' . $this->appId . ', udid:' . $this->udid . ', name:' . $this->app['name'] . ', sign fail:' . $signResult);
            $this->subAppVersionSign();
            if (strpos($signResult, "Can't Load P12 or PrivateKey File") !== false || strpos($signResult, "Can't Find Paired Certificate And PrivateKey") !== false || strpos($signResult, "Can't Find TeamId") !== false) {
                return 501;
            }
            if (strpos($signResult, "zip")) {
                Log::warn('zip fail, please install extension zip');
                return 502;
            }
            if (strpos($signResult, "Can't Find Provision File")) {
                Log::warn('udid: ' . $this->udid . ', unset and retry..');
                return 503;
            }
            Log::warn($signResult);
            return 504;
        }
        $this->resetAppVersionSign();
        return true;
    }

    #上传包到oss
    public function ipaUploadUrl($sysParam)
    {
        if ($sysParam['fileDriver'] == 'local') {
            $url = Basic::getMyDomain() . '/' . str_replace(C('ROOT_PATH'), '', $this->outputLocalIpa);
        }else if($sysParam['fileDriver'] == 'oss'){
            //上传到OSS
            $obj = new Oss($sysParam);
            $internal = !empty($sysParam['endpointInternal']) ? true : false;
            $result = $obj->upFile($this->outputLocalIpa, '', $this->createIpaLocation(), $internal);
            $is_exist =$obj->isExist($this->createIpaLocation());
            if (!$is_exist) {
                Log::warn('ipa_upload_oss_error');
                $result = false;
            }
            if (!$result) {
                $this->subAppVersionSign();
                return 600;
            }
            if (file_exists($this->outputLocalIpa)) { //删除本地文件, 防并发, 目录不能删
                unlink($this->outputLocalIpa);
            }
            $url = $obj->getHost() . '/' . str_replace($this->outPutPath, '', $this->outputLocalIpa);
        }else if($sysParam['fileDriver'] == 'cos'){
            $obj = new Cos($sysParam);
            $result = $obj->upFile($this->outputLocalIpa, $this->createIpaLocation());
            if (!$result) {
                $this->subAppVersionSign();
                return 600;
            }
            if (file_exists($this->outputLocalIpa)) { //删除本地文件, 防并发, 目录不能删
                unlink($this->outputLocalIpa);
            }
            $url = $obj->getHost() . '/' . str_replace($this->outPutPath, '', $this->outputLocalIpa);
        }
        return $url;
    }

    #返回结果
    public function returnRes($code, $data = '')
    {
        $list_msg = [
            1 => '签包成功',
            100 => '签包接口错误',
            400 => '参数错误',
            401 => '接口请求出错',
            500 => '记录签包数据错误',
            501 => 'p12等配置文件异常',
            502 => 'zip功能异常，请参阅部署指引第五条。',
            503 => '包描述文件异常',
            504 => '签名权限错误，请参阅部署指引第二条。',
            600 => 'oss传包错误',
        ];
        $msg = empty($list_msg[$code]) ? '未知错误' : $list_msg[$code];
        if ($code !== 1) {
            Log::warn($list_msg[$code]);
        }
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }



    /**
     * 签名
     * @param $data
     * @return string
     */
    public function createSign($data, $salt)
    {
        ksort($data);
        $m_str = "";
        foreach ($data as $k => $v) {
            $m_str .= $k . '=' . $v . '&';
        }
        $m_str = rtrim($m_str, '&');
        return md5(md5($m_str) . $salt);
    }

    /**
     * 发送Post请求
     * @param String $url 请求的地址
     * @param Array $header 自定义的header数据
     * @param Array $content POST的数据
     * @param bool $headerReturn 是否返回header
     * @param string $referer
     * @return String
     */
    function curlPost($url, $header, $content, $headerReturn = false, $referer = '', $timeout = 300)
    {
        $ch = curl_init();
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ch, CURLOPT_HEADER, $headerReturn);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
        if ($referer)
            curl_setopt($ch, CURLOPT_REFERER, $referer);

        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            return json_encode(['code' => 400, 'msg' => 'sign server error']);
        }
        curl_close($ch);

        return $response;
    }


    public function createUrlLocation($sysParam)
    {
        $ipaPath = $this->createIpaLocation();
        if($sysParam['fileDriver'] == 'local'){
            $url = Basic::getMyDomain() . '/public/sign/output/' . $ipaPath;
        }else if($sysParam['fileDriver'] == 'oss'){
            $obj = new Oss($sysParam);
            $host = $obj->getHost();
            $url = $host.'/'.$ipaPath;
        }else if($sysParam['fileDriver'] == 'cos'){
            $obj = new Cos($sysParam);
            $host = $obj->getHost();
            $url = $host.'/'.$ipaPath;
        }
        return $url;
    }

    public function createIpaLocation()
    {
        return 'app/'.$this->appId .'/'. date('Ymd') .'/'. $this->app['version_code'] . '-' . $this->udid . '.ipa';
    }

    public function combileProfileP12($data)
    {
        $profile_dir = $this->signPath . '/' . 'profile' . '/' . $this->appId;
        if (!is_dir($profile_dir))
            mkdir($profile_dir, 755, true);

        $profile = $profile_dir . '/'   . $this->udid . '.mobileprovision';
        $p12 = $profile_dir . '/'  . $this->udid . '.p12';
        file_put_contents($profile, base64_decode($data['profile']));
        file_put_contents($p12, $data['key']);
        return [$profile, $p12];
    }

    public function checkOldIpaExist($sysParam){
        $result = false;
        if($sysParam['fileDriver'] == 'local'){
            if (file_exists($this->outPutPath . $this->createIpaLocation()))
                $result = true;
        }else if($sysParam['fileDriver'] == 'oss'){
            $obj = new Oss($sysParam);
            $is_exist = $obj->isExist($this->createIpaLocation());
            if($is_exist)
                $result =true;
        }else{
            $obj = new Cos($sysParam);
            $is_exist = $obj->isExist($this->createIpaLocation());
            if($is_exist)
                $result =true;
        }
        return $result;
    }

}