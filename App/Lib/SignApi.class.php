<?php

class SignApi
{
    CONST GET_UDID_PROFILE_URL  = 'https://api1.ddqshop.com/developer/profile/register';
    CONST GET_PROFILE_COUNT_URL = 'https://api1.ddqshop.com/developer/profile/getcount';

    public static function getUdidProfile($udid, $accessKey, $use_vpn = false)
    {
        $capability= $use_vpn ? "PERSONAL_VPN,NETWORK_EXTENSIONS" : "";
        $request_url = self::GET_UDID_PROFILE_URL."?key={$accessKey}&udid={$udid}&capability={$capability}";
        $result  = self::get($request_url);
        //{"code":1,"msg":"success","time":"timestamp","data":{"key":"(key)","profile":"(profile)","expend":(expend)}}
        if($result_arr = json_decode($result, true)){
            if($result_arr['code'] == 1){
                return $result_arr['data'];
            }
            Log::warn($result_arr['msg']);
        }
        return false;

    }

    public static function getProfileCount($accessKey)
    {
        $request_url = self::GET_PROFILE_COUNT_URL."?key={$accessKey}";
        $result  = self::get($request_url);
        //{"code":1,"msg":"success","time":"timestamp","data":{"total":"(total)","surplus":"(surplus)"}}
        if($result_arr = json_decode($result, true)){
            if($result_arr['code'] == 1){
                return $result_arr['data'];
            }
            Log::warn($result_arr['msg']);
        }
        return false;
    }

    static function get($url)
    {
        //初使化curl
        $ch = curl_init();
        //请求的url，由形参传入
        curl_setopt($ch, CURLOPT_URL, $url);
        //将得到的数据返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //不处理头信息
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //连接超过10秒超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        //执行curl
        $output = curl_exec($ch);
        //关闭资源
        curl_close($ch);
        //返回内容
        return $output;
    }
}