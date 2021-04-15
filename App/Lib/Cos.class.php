<?php

class Cos
{

    public function __construct($param)
    {
        $this->secretId = $param['secretId']; //"云 API 密钥 SecretId";
        $this->secretKey = $param['secretKey']; //"云 API 密钥 SecretKey";
        $this->region = $param['region']; //设置一个默认的存储桶地域
        $this->cosBucket = $param['cosBucket']; //bucket

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
                $dir = " /";
                break;
        }
        return $dir;
    }

    public function createStsParams($type, $host)
    {
        include C('APP_PATH') . 'Extend/Cos/server/qcloud-sts-sdk.php'; // 这里获取 sts.php https://github.com/tencentyun/qcloud-cos-sts-sdk/blob/master/php/sts/sts.php
        $dir = $this->__getDirByType($type);
        $sts = new STS();
// 配置参数
        $config = array(
            'url' => 'https://sts.tencentcloudapi.com/',
            'domain' => 'sts.tencentcloudapi.com',
            'proxy' => '',
            'secretId' => $this->secretId, // 固定密钥
            'secretKey' => $this->secretKey, // 固定密钥
            'bucket' => $this->cosBucket, // 换成你的 bucket
            'region' => $this->region, // 换成 bucket 所在园区
            'durationSeconds' => 1800, // 密钥有效期
            // 允许操作（上传）的对象前缀，可以根据自己网站的用户登录态判断允许上传的目录，例子： user1/* 或者 * 或者a.jpg
            // 请注意当使用 * 时，可能存在安全风险，详情请参阅：https://cloud.tencent.com/document/product/436/40265
            'allowPrefix' => $dir . '*',
            // 密钥的权限列表。简单上传和分片需要以下的权限，其他权限列表请看 https://cloud.tencent.com/document/product/436/31923
            'allowActions' => array(
                // 所有 action 请看文档 https://cloud.tencent.com/document/product/436/31923
                // 简单上传
                'name/cos:PutObject',
                'name/cos:PostObject',
                // 分片上传
                'name/cos:InitiateMultipartUpload',
                'name/cos:ListMultipartUploads',
                'name/cos:ListParts',
                'name/cos:UploadPart',
                'name/cos:CompleteMultipartUpload'
            )
        );
// 获取临时密钥，计算签名
        $tempKeys = $sts->getTempKeys($config);
// 返回数据给前端
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: ' . $host); // 这里修改允许跨域访问的网站
        header('Access-Control-Allow-Headers: origin,accept,content-type');
//        return json_encode($tempKeys);
        return $tempKeys;
    }

    public function addDomainCROS($host = '')
    {
        $cosClient = $this->createClient();
        try {
            $result = $cosClient->putBucketCors(array(
                'Bucket' => $this->cosBucket, //格式：BucketName-APPID
                'CORSRules' => array(
                    array(
                        'AllowedHeaders' => array('*',),
                        'AllowedMethods' => array('PUT', 'POST', 'GET', 'HEAD'),
                        'AllowedOrigins' => array('*', $host),
                        'ExposeHeaders' => array('*',),
                        'MaxAgeSeconds' => 1,
                    ),
                    // ... repeated
                )
            ));
            // 请求成功
            return true;
        } catch (\Exception $e) {
            // 请求失败
            return false;
        }
    }

    public function createClient()
    {
        include C('APP_PATH') . 'Extend/Cos/vendor/autoload.php';
        $cosClient = new Qcloud\Cos\Client(
            array(
                'region' => $this->region,
                'schema' => 'https', //协议头部，默认为http
                'credentials' => array(
                    'secretId' => $this->secretId,
                    'secretKey' => $this->secretKey)));
        return $cosClient;
    }

    public function getHost()
    {
        return 'https://'.$this->cosBucket.'.cos.accelerate.'.$this->region.'.myqcloud.com';
    }

    public function upFile($file, $key = '', $type = '')
    {
        $cosClient = $this->createClient();
        if (empty($key)) {
            $fileinfo = pathinfo($file);
            $dir = $this->__getDirByType($type);          // 用户上传文件时指定的前缀。
            $key = $dir . uniqid() . '.' . $fileinfo['extension'];
        }

        try {
            $result = $cosClient->upload(
                $bucket = $this->cosBucket, //格式：BucketName-APPID
                $key,
                $body = fopen($file, 'rb')
            );
            // 请求成功
            return $this->getHost() . '/' . $key;
        } catch (\Exception $e) {
            // 请求失败
            Log::warn($e);
            return false;
        }
    }

    public function isExist($object)
    {
        $cosClient = $this->createClient();
        try {
            $result = $cosClient->headObject(array(
                'Bucket' => $this->cosBucket,
                'Key' => $object,
            ));
            // 请求成功
            return true;
        } catch (\Exception $e) {
            // 请求失败
            return false;
        }
    }

}