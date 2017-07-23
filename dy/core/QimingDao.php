<?php
/*
 * QimingDao 核心流程控制文件
 * @version ST1.0
 */

/*  全局配置  */

//记录开始运行时间
$GLOBALS['_beginTime'] = microtime(TRUE);

// 记录内存初始使用
!defined('MEMORY_LIMIT_ON') && define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));

//参数处理 If already slashed, strip.
if (get_magic_quotes_gpc()) {
	$_GET    = stripslashes_deep( $_GET    );
	$_POST   = stripslashes_deep( $_POST   );
	$_COOKIE = stripslashes_deep( $_COOKIE );
}

//参数处理 控制不合规格的参数
check_gpc($_GET);
check_gpc($_POST);
check_gpc($_COOKIE);

//解析关键参数 todo:参数过滤 preg_match("/^([a-zA-Z_\/0-9]+)$/i", $qm, $url);
$_REQUEST	=	array_merge($_GET,$_POST);

if(isset($_REQUEST['os']) && !isset($_REQUEST['app'])){
	$qm['_os']  = $_REQUEST['os'];
}else{
	$qm['_app'] = isset($_REQUEST['app']) && !empty($_REQUEST['app'])?$_REQUEST['app']:'index';
	$qm['_mod'] = isset($_REQUEST['mod']) && !empty($_REQUEST['mod'])?$_REQUEST['mod']:'Index';
	$qm['_act'] = isset($_REQUEST['act']) && !empty($_REQUEST['act'])?$_REQUEST['act']:'index';
}

//APP的常量定义
qmdefine('APP_NAME'		, $qm['_app']);
qmdefine('TRUE_APPNAME',!empty($qm['_widget_appname']) ? $qm['_widget_appname']:APP_NAME);
qmdefine('MODULE_NAME'	, $qm['_mod']);
qmdefine('ACTION_NAME'	, $qm['_act']);

//新增一些CODE常量.用于简化判断操作
qmdefine('MODULE_CODE'	, $qm['_app'].'/'.$qm['_mod']);
qmdefine('ACTION_CODE'	, $qm['_app'].'/'.$qm['_mod'].'/'.$qm['_act']);
qmdefine('APP_RUN_PATH'		,	CORE_RUN_PATH.'/~'.TRUE_APPNAME);



/*  应用配置  */
//载入应用配置
qmdefine('APP_PATH'			, APPS_PATH.'/'.TRUE_APPNAME);
qmdefine('APP_URL'			, SITE_URL.'/apps/'.TRUE_APPNAME);
qmdefine('APP_LIB_PATH'		, APP_PATH.'/');
qmdefine('APP_ACTION_PATH'	, APP_PATH);
//默认风格包名称

//默认静态文件、模版文件目录
qmdefine('THEME_PATH'		, ADDON_PATH.'/theme');
qmdefine('THEME_URL'		, ADDON_URL.'/theme');
qmdefine('THEME_PUBLIC_PATH', THEME_PATH.'/_stastic');
qmdefine('THEME_PUBLIC_URL'	, THEME_URL.'/_stastic');
qmdefine('APP_PUBLIC_URL'	, THEME_PUBLIC_URL.'/'.TRUE_APPNAME);
qmdefine('APP_TPL_PATH'		, THEME_PATH.'/'.TRUE_APPNAME);

//根据应用配置信息. 重置一些常量
qmconfig(include CONF_PATH.'/conv.inc.php');
qmconfig(include CONF_PATH.'/config.inc.php');

//[RUNTIME]
qmload(CORE_LIB_PATH.'/functions.inc.php');
qmload(CORE_LIB_PATH.'/Think.class.php');
qmload(CORE_LIB_PATH.'/App.class.php');
qmload(CORE_LIB_PATH.'/Action.class.php');
qmload(CORE_LIB_PATH.'/Widget.class.php');
//[/RUNTIME]

//根据应用配置重定义以下常量
if(C('APP_TPL_PATH')){
	qmdefine('APP_TPL_PATH', 	C('APP_TPL_PATH'));
}

//如果是部署模式、则如下定义
if(C('DEPLOY_STASTIC')){
	qmdefine('THEME_PUBLIC_URL'	, PUBLIC_URL);
	qmdefine('APP_PUBLIC_URL'	, THEME_PUBLIC_URL.'/'.TRUE_APPNAME);
}

//自定义SESSION机制
Session::start();
