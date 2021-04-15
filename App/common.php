<?php

/**
 * 获取随机数
 * @param $length
 * @param bool $number
 * @return string
 */
function get_nonce($length, $number = false)
{

    if ($number) {
        $pattern = '1234567890';
        $rand = 9;
    } else {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $rand = strlen($pattern)-1;
    }
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_result = mt_rand(0, $rand);
        $key .= substr($pattern, $rand_result, 1);
    }
    return $key;
}

/**
 * 视图输出函数
 * @param $data
 */
function defaultEcho($data, $key)
{
    if (empty($data))
        return '';
    return !empty($data[$key]) ? $data[$key] : '';
}

/**
 * 调试打印
 * @param $data
 * @param bool $exit
 */
function p($data, $exit = true)
{

    echo "<pre>";
    var_dump($data);
    echo "</pre>";

    if ($exit)
        exit;
}

/**
 * base64格式编码转换为图片并保存对应文件夹
 * @param $base64_image_content
 * @param $path
 * @return bool|string
 */
function base64_to_image($base64_image_content, $path)
{
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
        $type = $result[2];
        $new_file = $path . "/" . date('Ymd', time()) . "/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }
        $new_file = $new_file . time() . ".{$type}";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
            return $new_file;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 图片转base64
 * @param $image_file
 * @return string
 */
function image_to_base64($image_file)
{
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}

function myMd5($data, $salt = 'eqdwz89b')
{
    return md5($data.$salt);
}

/**
 * 获取客户端Ip
 * @return array|false|string
 */
function get_ip()
{
    if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false){
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if(strpos($ips[0], ':') !== false){
//                return $_SERVER['HTTP_REMOTEIP'];
                return '';
            }else{
                return $ips[0];
            }

        }else{
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;

}

/**
 * 执行cmd命令
 * @param $cmd
 * @param bool $show
 * @param bool $r
 * @return mixed
 */
function cmd($cmd, $show=true, $r=false) {
    if ($show) debug('cmd: '.$cmd);
    exec($cmd, $result);
    if (is_array($result))
        $result = implode("\n", $result);
    if ($r && $result) debug('result: '.$result);
    return $result;
}

/**
 * 调试日志再次封闭
 * @param $content
 * @param string $l
 */
function debug($content, $l='') {
    if (is_array($content))
        $content = implode(',', $content);
    switch ($l) {
        case 'r':
            $content = "\e[31m$content\e[0m";
            break;
        case 'g':
            $content = "\e[32m$content\e[0m";
            break;
        case 'y':
            $content = "\e[33m$content\e[0m";
            break;
    }
    if (!$content) $content = 'no result';
    $content = '[ '.date('H:i:s').' ] '.$content;
    Log::warn($content);
}

function get_msectime() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;

}

function create_ios_plist_str($plist)
{
    return 'itms-services:///?action=download-manifest&url='.$plist;
}

function get_disk_total($total)
{
    $config = [
        '3' => 'GB',
        '2' => 'MB',
        '1' => 'KB'
    ];
    foreach($config as $key => $value){
        if($total > pow(1024, $key)){
            return round($total / pow(1024,$key)).$value;
        }
        return $total . 'B';
    }
}

function check_system_type()
{
    if (!isset($_SERVER['SERVER_SOFTWARE'])) {
        return '未检测到服务器类型';
    }
    $webServer = strtolower($_SERVER['SERVER_SOFTWARE']);
    if (strpos($webServer, 'apache') !== false) {
        $s =  'Apache';
    } elseif (strpos($webServer, 'microsoft-iis') !== false) {
        $s = 'Iis';
    } elseif (strpos($webServer, 'nginx') !== false) {
        $s = 'Nginx';
    } elseif (strpos($webServer, 'lighttpd') !== false) {
        $s = 'Lighttpd';
    } elseif (strpos($webServer, 'kangle') !== false) {
        $s = 'Kangle';
    } elseif (strpos($webServer, 'caddy') !== false) {
        $s = 'Caddy';
    } elseif (strpos($webServer, 'development server') !== false) {
        $s = 'Development server';
    } else {
        $s = $webServer;
    }
    return $s;
}

function myf_put_contents($file, $data)
{
    $header_str = php_header_str();
    $data = $header_str . $data;
    file_put_contents($file, $data, LOCK_EX);
}

function myf_get_contents($file)
{
    $data = file_get_contents($file);
    return substr($data, 13);
}

function php_header_str()
{
    return "<?php exit;?>";
}

