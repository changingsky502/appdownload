<?php

use CFPropertyList\CFPropertyList;

class MobileConfig
{
    public $save_path ;
    public $demo_file ;
    public $demo_en_file ;
    public $api_url;

    public function __construct()
    {
        $this->save_path = C('ROOT_PATH') . 'public/mobileconfig/' . date('Ymd') . "/";
        $this->demo_file = C('ROOT_PATH') . 'public/static/mobileconfig/tpl.mobileconfig';
        $this->demo_en_file = C('ROOT_PATH') . 'public/static/mobileconfig/tpl_en.mobileconfig';
        $this->api_url = 'https://'.$_SERVER['HTTP_HOST'].'/index.php?c=index&a=getUdid';
    }

    public function run($app,$salt)
    {
        include C('APP_PATH').'Vendor/autoload.php';
        if(!is_dir($this->save_path))
            mkdir($this->save_path, 0755, true);
        //拼接签名字串
        $tm = time();
        $token = myMd5($tm.$app['id'], $salt);
        $this->api_url .= "&id={$app['id']}&tm={$tm}&token={$token}";

        $plist = new CFPropertyList( $this->demo_file );
        foreach( $plist->getValue(true) as $key => $value )
        {
            if($key == 'PayloadDisplayName'){
                $value->setValue($app['name']); //名称更换
            }
            if( $value instanceof \Iterator )
            {
                foreach($value->getValue(true) as $k=>$v ) {
                    if($k == 'URL'){
                        $v->setValue( $this->api_url); //回调地址更换
                    }
                }
            }
        }
        $rand_str =  md5($tm.rand(1111,9999));
        $unsigned_file_name = $rand_str."_unsignd.mobileconfig";
        $signed_file_name = $rand_str.".mobileconfig";
        $unsignd_plist_name_last = $this->save_path.$unsigned_file_name;
        $plist->save( $unsignd_plist_name_last, CFPropertyList::FORMAT_XML );
        //生成后签名
        if(file_exists($unsignd_plist_name_last)){
            $signed_result = $this->signMobileconfig($signed_file_name, $unsignd_plist_name_last);
            if($signed_result && file_exists($this->save_path.$signed_file_name)){
                return 'https://'.$_SERVER['HTTP_HOST'].'/'.str_replace(C('ROOT_PATH'), '', $this->save_path.$signed_file_name);
            }else{
                return 'https://'.$_SERVER['HTTP_HOST'].'/'.str_replace(C('ROOT_PATH'), '', $unsignd_plist_name_last);
            }
        }
        Log::warn('UDID获取文件生成失败！');
        return false;
    }

    public function signMobileconfig($signed_file_name, $unsignd_plist_name_last)
    {
        $signd_plist_name = $this->save_path.$signed_file_name;
        $serverCrt = C('APP_PATH').'Data/ssl/default/server.crt';
        $privateKey = C('APP_PATH').'Data/ssl/default/private.key';
        $ca = C('APP_PATH').'Data/ssl/default/ca.crt';
        $output =[];
        $result = '';
        exec("openssl smime -sign -in $unsignd_plist_name_last -out $signd_plist_name -signer $serverCrt -inkey $privateKey  -certfile $ca -outform der -nodetach", $output, $result);
        if($result){
            Log::warn('UDID获取文件签证失败，请检查函数exec是否开启！');
            return false;
        }else{
            @unlink($unsignd_plist_name_last);
            return true;
        }
    }



}