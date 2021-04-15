<?php

class SoloAppDataLog
{
    static $data_files = [];

    public static function getDefaultLogFile($id = '')
    {
        $dir = C('APP_PATH') . 'Data/download/'.$id.'/' . date('Ym');
        if (!is_dir($dir))
            mkdir($dir, 0755, true);
        return $dir . '/' . date('d') . '.php';
    }


    public static function write($data, $id, $file = '')
    {
        if (is_array($data))
            $data = json_encode($data);
        if (empty($file))
            $file = self::getDefaultLogFile($id);
        if (!file_exists($file))
            myf_put_contents($file, "\r\n");
        $fp = fopen($file, 'a+');
        flock($fp, LOCK_EX);
        fwrite($fp, $data . "\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    public static function find($file, $id, $keywords)
    {
        if (empty($file))
            $file = self::getDefaultLogFile($id);
        if(!file_exists($file))
            return false;
        $contents = file_get_contents($file);
        $pattern = preg_quote($keywords, '/');
        $pattern = "/^.*$pattern.*\$/m";
        if (preg_match_all($pattern, $contents, $matches)) {
            return $matches[0];
        } else {
            return false;
        }
    }

    public static function addInstallNum($id)
    {
        $header_str = php_header_str();
        $file = self::getInstallNumFile($id);
        $num = file_get_contents($file);
        $num = str_replace($header_str, '', $num) + 1;
        file_put_contents($file, $header_str . $num, LOCK_EX);
    }

    public static function getInstallNum($id)
    {
        $file = self::getInstallNumFile($id);
        if (!file_exists($file))
            return 0;
        return myf_get_contents($file);
    }

    public static function getInstallNumFile($id)
    {
        return C('APP_PATH') . 'Data/download/' . $id . '/num.php';
    }

    public static function getInstallLogs($id, $page = 1, $limit = 10)
    {

        $allFiles = self::getAllDataFiles($id);
        $data = [];
        $readTotalLine = 0;
        $hadData = 0;
        foreach ($allFiles as $K => $v) {
            list($tmp_data, $read_line_num) = self::readData($v, $hadData, $readTotalLine, $page, $limit);
            $readTotalLine += $read_line_num;
            if ($tmp_data) {
                $hadData = 1;
                $data = array_merge($data, $tmp_data);
            }
            if (count($data) >= $limit) {
                return array_slice($data, 0, $limit);
            }
        }
        return $data;
    }

    public static function readData($file, $hadData, $readTotal, $page = 1, $limit = 10)
    {
        $start = $hadData ? 0 : ($page - 1) * $limit - $readTotal;
        $content = file($file);
        array_shift($content);
        $total_line = count($content) ? count($content) - 1 : 0;
        $data = array_slice(array_reverse($content), $start, $limit);
        return [$data, $total_line];
    }

    public static function getAllDataFiles($id)
    {
        $path = C('APP_PATH') . 'Data/download/' . $id;
        $arr = array();
        if (is_file($path)) {

        } else {
            if (is_dir($path)) {
                $data = scandir($path);
                if (!empty($data)) {
                    foreach ($data as $value) {
                        if ($value != '.' && $value != '..') {
                            $sub_path = $path . "/" . $value;
                            $temp = self::getDirContent($sub_path);
                            $arr = array_merge($temp, $arr);
                        }
                    }

                }
            }
        }
        return $arr;
    }

    public static function getDirContent($path)
    {
        if (!is_dir($path)) {
            return [];
        }
        $arr = array();
        $data = scandir($path);
        foreach ($data as $value) {
            if ($value != '.' && $value != '..') {
                array_unshift($arr, $path . '/' . $value);
            }
        }
        return $arr;
    }


}