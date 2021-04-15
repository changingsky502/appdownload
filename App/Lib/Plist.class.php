<?php

use CFPropertyList\CFPropertyList;

class Plist
{
    public $save_path ;
    public $demo_file ;
    public $access_path;

    public function __construct()
    {
        $this->save_path = C('ROOT_PATH'). 'plist/' ;
        $this->access_path = Basic::getMyDomain() . '/plist/' ;
        $this->demo_file = 'public/plist/demo.plist';
    }

    public function run($param)
    {
        include C('APP_PATH').'Vendor/autoload.php';
        $plist = new CFPropertyList( $this->demo_file );
        foreach( $plist->getValue(true) as $key => $value )
        {
            foreach($value[0]->getValue(true) as $kk=>$vv ){
                if($kk == 'assets'){
                    foreach($vv->getValue(true) as $k=>$v){
                        foreach($v->getValue(true) as $a=>$b){
                            if($k ==0){
                                if($a == 'url'){ //下载地址
                                    $b->setValue( $param['ipa_url'] );
                                }
                            }
                            if($k ==1){
                                if($a == 'url'){ //下载大图地址
                                    $b->setValue( $param['pic'] );
                                }
                            }
                            if($k ==2){
                                if($a == 'url'){ //下载小图地址
                                    $b->setValue( $param['pic'] );
                                }
                            }
                        }
                    }
                }else if($kk == 'metadata'){
                    foreach($vv->getValue(true) as $k=>$v){
                        if($k == 'bundle-identifier'){
                            $v->setValue( $param['bundle_id'] ); //包名
                        }
                        if($k == 'bundle-version'){
                            $v->setValue( $param['version_name'] ); //版本
                        }
                        if($k == 'subtitle'){
                            $v->setValue( $param['name'] ); //副标题
                        }
                        if($k == 'title'){
                            $v->setValue( $param['name'] ); //标题
                        }
                    }
                }

            }
//            if( $value instanceof \Iterator )
//            {
//                // The value is a CFDictionary or CFArray, you may continue down the tree
//            }
        }
        $plist_name = $param['udid'].'.plist';
        $this->extendSavePath($param['id']);
        $out_file_plist_name = $this->save_path.$plist_name;
        $plist->save( $out_file_plist_name, CFPropertyList::FORMAT_XML );
        if(file_exists($out_file_plist_name)){
            return ['status'=>true, 'msg'=> 'plist创建成功！', 'file' => $this->access_path.$plist_name];
        }else{
            Log::warn('plist文件创建失败！');
            return false;
        }
    }

    public function extendSavePath($app_id)
    {
        $this->save_path = $this->save_path.$app_id.'/';
        $this->access_path = $this->access_path.$app_id.'/';
        if(!is_dir($this->save_path)){
            mkdir($this->save_path, 0755, true);
        }
    }

    public function getAccessPath(&$data){
        return $this->access_path.$data['app_id'].'/'.$data['udid'].'.plist';
    }

    public function getPlistV2($app)
    {
        $str = '<?xml version="1.0" encoding="UTF-8"?>
        <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
        <plist version="1.0">
            <dict>
                <key>items</key>
                <array>
                    <dict>
                        <key>assets</key>
                        <array>
                            <dict>
                                <key>kind</key>
                                <string>software-package</string>
                                <key>url</key>
                                <string>' . $app["file"] . '</string>
                            </dict>
                        </array>
                        <key>metadata</key>
                        <dict>
                            <key>bundle-identifier</key>
                            <string>' . $app["bundle_id"] . '</string>
                            <key>bundle-version</key>
                            <string>' . $app["version_name"] . '</string>
                            <key>kind</key>
                            <string>software</string>
                            <key>title</key>
                            <string>' . $app["name"] . '</string>
                        </dict>
                    </dict>
                </array>
            </dict>
        </plist>';

        $filename = C('ROOT_PATH') . 'public/plist/' . md5($app['id']) . '.plist';

        if (!file_exists($filename)) {
            $myfile = fopen($filename, "w") or die("Unable to open file!");
            fwrite($myfile, $str);
            fclose($myfile);
        }
        return str_replace(C('ROOT_PATH'), '', $filename);
    }
}