<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

$secretId = "COS_SECRETID"; //"云 API 密钥 SecretId";
$secretKey = "COS_SECRETKEY"; //"云 API 密钥 SecretKey";
$region = "ap-beijing"; //设置一个默认的存储桶地域
$cosClient = new Qcloud\Cos\Client(
    array(
        'region' => $region,
        'schema' => 'https', //协议头部，默认为http
        'credentials' => array(
            'secretId' => $secretId,
            'secretKey' => $secretKey)));
try {
    $result = $cosClient->getObject(array(
        'Bucket' => 'examplebucket-125000000', //格式：BucketName-APPID
        'Key' => 'exampleobject',
        'SaveAs' => '/data/exampleobject',
        /*
        'Range' => 'bytes=0-10',
        'ResponseCacheControl' => 'string',
        'ResponseContentDisposition' => 'string',
        'ResponseContentEncoding' => 'string',
        'ResponseContentLanguage' => 'string',
        'ResponseContentType' => 'string',
        'ResponseExpires' => 'string',
        */
    ));
    // 请求成功
    print_r($result);
} catch (\Exception $e) {
    // 请求失败
    echo($e);
}

