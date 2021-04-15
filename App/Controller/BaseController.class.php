<?php
class BaseController extends Controller{
    protected function _init(){
        header("Content-Type:text/html; charset=utf-8");
        defined('APP_KEY') or exit('Access Deny!');
        session_start();
        $controller = !empty($_GET['c']) ? $_GET['c'] : 'index';
        //登录验证
        $no_login_controllers = [
            'index'
        ];
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!in_array($controller, $no_login_controllers)
            && (empty($_SESSION['username']) || $_SESSION['login_ip'] != $ip)
        ) {
            $this->redirect('index.php?c=index&a=login');
        }
    }
} 
