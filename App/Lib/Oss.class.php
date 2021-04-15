<?php

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\CorsConfig;
use OSS\Model\CorsRule;

class Oss
{
    public $id;
    public $key;
    public $host;
    public $bucket;
    public $point;
    public $point_internal;

    public function __construct($oss_param)
    {
        $this->id = $oss_param['accessKeyId'];          // 请填写您的AccessKeyId。
        $this->key = $oss_param['accessKeySecret'];     // 请填写您的AccessKeySecret。
        // $host的格式为 bucketname.endpoint，请替换为您的真实信息。
        $this->bucket = $oss_param['bucket'];
        $this->point = $oss_param['endpoint'];
        $this->point_internal = !empty($oss_param['endpointInternal']) ? $oss_param['endpointInternal'] : '';
        $this->host = 'https://' . $this->bucket . '.' . $oss_param['endpoint'];
        $mydomain = Basic::getMyDomain();
        $this->callback = $mydomain . "/index.php?c=index&a=ossCallBack";
    }

    //根据不同类型文件存储不同路径
    private function __getDirByType($type)
    {
        switch ($type) {
            case "app":
                $dir = "app/";
                break;
            case "icon":
                $dir = "icon/";
                break;
            default:
                $dir = "uploads/";
                break;
        }
        return $dir;
    }

    public function getParam($type, $host = '')
    {
        $id = $this->id;          // 请填写您的AccessKeyId。
        $key = $this->key;     // 请填写您的AccessKeySecret。
        // $host的格式为 bucketname.endpoint，请替换为您的真实信息。
        if (empty($host))
            $host = $this->host;
        // $callbackUrl为上传回调服务器的URL，请将下面的IP和 Port配置为您自己的真实URL信息。
        $callbackUrl = $this->callback;
        $dir = $this->__getDirByType($type);          // 用户上传文件时指定的前缀。
        $callback_param = array('callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded");
        $callback_string = json_encode($callback_param);
        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 30;  //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问。
        $end = $now + $expire;
        $expiration = $this->gmt_iso8601($end);
        //最大文件大小.用户可以自己设置
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 1048576000);
        $conditions[] = $condition;

        // 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;
        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['callback'] = $base64_callback_body;
        $response['dir'] = $dir;  // 这个参数是设置用户上传文件时指定的前缀。
//        return json_encode($response);
        return $response;
    }

    function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }


    public function upload($file, $object = '', $type = '')
    {
        $fileinfo = pathinfo($file);
// 文件名称
        $dir = $this->__getDirByType($type);          // 用户上传文件时指定的前缀。
        if (empty($object))
            $object = $dir . uniqid() . '.' . $fileinfo['extension'];
// <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt
        try {
            $ossClient = new OssClient($this->id, $this->key, $this->point);
            $ossClient->uploadFile($this->bucket, $object, $file);
        } catch (OssException $e) {
            Log::warn('Oss upload fail: ' . $e->getMessage());
            return false;
        }
        return $this->host . "/" . $object;
    }

    public function copyFile($fileName, $newFileName)
    {
        $from_bucket = $this->bucket;
        $to_bucket = $this->bucketPirvate;
        $point = $this->point;
        $from_object = $fileName;
        $to_object = $newFileName;
        try {
            $ossClient = new OssClient($this->id, $this->key, $point);
            $ossClient->copyObject($from_bucket, $from_object, $to_bucket, $to_object);
        } catch (OssException $e) {
            Log::warn('oss copy fail:' . $e->getMessage());
            return false;
        }
        return true;
    }

    public function addCnameToBucket($bucket, $myDomain)
    {
        try {
            $ossClient = new OssClient($this->id, $this->key, $this->point);
            // 添加CNAME记录。
            $ossClient->addBucketCname($bucket, $myDomain);
        } catch (OssException $e) {
            return $e->getMessage();
        }
        return true;
    }

    public function selectCnameFromBucket($bucket)
    {
        try {
            $ossClient = new OssClient($this->id, $this->key, $this->point);
            // 查看CNAME记录。
            $cnameConfig = $ossClient->getBucketCname($bucket);
        } catch (OssException $e) {
            return $e->getMessage();
        }
        return $cnameConfig;
    }

    public function delCnameFromBucket($bucket, $myDomain)
    {
        // 设置自定义域名。
        try {
            $ossClient = new OssClient($this->id, $this->key, $this->point);
            // 删除CNAME记录。
            $ossClient->deleteBucketCname($bucket, $myDomain);
        } catch (OssException $e) {
            return $e->getMessage();
        }
        return true;
    }

    public function addDomainCROS($myDomain)
    {
        $corsConfig = new CorsConfig();
        $rule = new CorsRule();
// AllowedHeaders和ExposeHeaders不支持通配符。
        $rule->addAllowedHeader("*");
// AllowedOlowedMetho'/'最多支持一个星号（*）通配符。星号（*）表示允许所有的域来源或者操作。
        $rule->addAllowedOrigin($myDomain);
        $rule->addAllowedMethod("POST");
        $rule->setMaxAgeSeconds(10);
// 每个存储空间最多允许10条规则。
        $corsConfig->addRule($rule);

        try {
            $ossClient = new OssClient($this->id, $this->key, $this->point);
            // 已存在的规则将被覆盖。
            $ossClient->putBucketCors($this->bucket, $corsConfig);
        } catch (OssException $e) {
            Log::warn($e->getMessage() . "\n");
            return $e->getMessage();
        }
        return true;
    }

    public function getAllDomainCROS($bucket)
    {
        $corsConfig = null;
        try {
            $ossClient = new OssClient($this->id, $this->key, $this->point);
            $corsConfig = $ossClient->getBucketCors($bucket);
        } catch (OssException $e) {
            return $e->getMessage();
        }
        return $corsConfig->serializeToXml();
    }

    public function isExist($object)
    {
        $accessKeyId = $this->id;
        $accessKeySecret = $this->key;
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $bucket = $this->bucket;
        $endpoint = $this->point;
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $exist = $ossClient->doesObjectExist($bucket, $object);
            return $exist;
        } catch (OssException $e) {
            Log::warn('Oss exist fail: ' . $e->getMessage());
            return false;
        }
    }

    //判断远程文件
    function check_remote_file_exists($url)
    {
        $curl = curl_init($url);
// 不取回数据
        curl_setopt($curl, CURLOPT_NOBODY, true);
// 发送请求
        $result = curl_exec($curl);
        $found = false;
// 如果请求没有发送失败
        if ($result !== false) {
// 再检查http响应码是否为200
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $found = true;
            }
        }
        curl_close($curl);

        return $found;
    }

    public function deleteFile($object)
    {

        $accessKeyId = $this->id;
        $accessKeySecret = $this->key;
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $endpoint = $this->point;
        $bucket = $this->bucket;
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            return $ossClient->deleteObject($bucket, $object);
        } catch (OssException $e) {
            Log::warn('oss delete fail:' . $e->getMessage());
            return false;
        }
    }

    public function upFile($file, $bucket = '', $object = '', $internal=false)
    {
        $point = $this->point;
        if($internal)
            $point = $this->point_internal;
        try {
            $ossClient = new OssClient($this->id, $this->key, $point);
            $bucket = $bucket ? $bucket : $this->bucket;
            $ossClient->uploadFile($bucket, $object, $file);
        } catch (OssException $e) {
            Log::warn('Oss upload fail: ' . $e->getMessage());
            return false;
        }
        return true;
    }

    public function getHost()
    {
        return $this->host;
    }


}