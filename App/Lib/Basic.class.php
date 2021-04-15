<?php

class Basic
{

    static $site_cache_id = "basic";
    static $safe_cache_id = "safe";

    const STATUS_VALID = 1;
    const STATUS_INVALID = 0;

    /**
     * 获取站点缓存
     * @return array
     */
    public static function getSiteCache()
    {
        $result = File::search('site', 'id', self::$site_cache_id);
        return !empty($result['result']) ? $result['result'][0] : [];
    }

    /**
     * 存储站点缓存
     * @param $data
     */
    public static function putSiteCache($data)
    {
        File::save('site', $data);
    }

    public static function getMyDomain()
    {
        $http_type = self::getHttpType();
        return $http_type . $_SERVER['HTTP_HOST'];
    }

    public static function getHttpType()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type;
    }


}
