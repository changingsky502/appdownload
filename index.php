<?php
//ini_set("display_errors", "On");
//error_reporting(E_ALL);
define('APP_KEY', 'geekApp');
include './App/SinglePHP.class.php';
$config = array(
    'ROOT_PATH' => __DIR__."/",
    'APP_PATH' => './App/',
    'UPLOAD_PATH' => __DIR__ . '/public/uploads/',
);
SinglePHP::getInstance($config)->run();
