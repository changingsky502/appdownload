<?php
ini_set("display_errors", "On");
error_reporting(E_ALL);
//ini_set('open_basedir', __DIR__."../");
define('APP_KEY', 'xsign');
include './App/SinglePHP.class.php';
$config = array(
    'ROOT_PATH' => __DIR__."/",
    'APP_PATH' => './App/',
    'UPLOAD_PATH' => __DIR__ . '/public/uploads/',
);
SinglePHP::getInstance($config)->run();
