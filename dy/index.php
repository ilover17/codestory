<?php
ini_set('display_errors','on');

error_reporting(E_ALL);

//核心路径.核心模式
define('CORE_PATH', getcwd().'/core');

//站点根路径
define('SITE_PATH', getcwd());

//载入核心文件
require(CORE_PATH.'/core.php');

//实例化一个网站应用实例
App::run();