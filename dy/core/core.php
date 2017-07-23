<?php
/*
 * QimingDao 核心入口文件
 * @version ST1.0
 */

if (!defined('SITE_PATH')) exit();

//设置全局变量
$qm['_debug']	=	isset($_GET['debug']) ? true : false;		//_debug=true时，开启debug,没有模板缓存.
$qm['_debug']	=   true;
$qm['_define']	=	array();	//全局常量
$qm['_config']	=	array();	//全局配置

if($qm['_debug']){
    $GLOBALS['debug']['time_include_start'] = microtime(TRUE);
    $GLOBALS['debug']['mem_include_start']  = memory_get_usage();
}

qmdefine('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
qmdefine('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );

// 当前文件名
if(!defined('_PHP_FILE_')) {
	if(IS_CGI) {
		// CGI/FASTCGI模式下
		$_temp  = explode('.php',$_SERVER["PHP_SELF"]);
		qmdefine('_PHP_FILE_',  rtrim(str_replace($_SERVER["HTTP_HOST"],'',$_temp[0].'.php'),'/'));
	}else {
		qmdefine('_PHP_FILE_',    rtrim($_SERVER["SCRIPT_NAME"],'/'));
	}
}

if(!defined('__ROOT__')) {
	// 网站URL根目录
	$_root = dirname(_PHP_FILE_);
	qmdefine('__ROOT__',  (($_root=='/' || $_root=='\\')?'':rtrim($_root,'/')));
}

//基本常量定义
qmdefine('CORE_PATH'	,	dirname(__FILE__));

qmdefine('SITE_DOMAIN'	,	strip_tags($_SERVER['HTTP_HOST']));
qmdefine('SITE_URL'		,	'http://'.SITE_DOMAIN.__ROOT__);

qmdefine('CONF_PATH'	,	SITE_PATH.'/config');
qmdefine('PAY_PATH', SITE_PATH.'/pay');

qmdefine('APPS_PATH'	,	SITE_PATH.'/apps');
qmdefine('APPS_URL'		,	SITE_URL.'/apps');	// 应用内部图标 等元素

qmdefine('ADDON_PATH'	,	SITE_PATH.'/addons');
qmdefine('ADDON_URL'	,	SITE_URL.'/addons');

qmdefine('DATA_PATH'	,	SITE_PATH.'/data');
qmdefine('DATA_URL'		,	SITE_URL.'/data');
qmdefine('DS' , DIRECTORY_SEPARATOR);

//日志记录
qmdefine('LOG_PATH',	DATA_PATH);

qmdefine('UPLOAD_PATH'	,	SITE_PATH.'/data/upload');
qmdefine('UPLOAD_URL'	,	SITE_URL.'/data/upload');

qmdefine('PUBLIC_PATH'	,	SITE_PATH.'/public');
qmdefine('PUBLIC_URL'	,	SITE_URL.'/public');


// 定义语言缓存文件路径常量
qmdefine('LANG_PATH', SITE_PATH.'/config/lang');
qmdefine('LANG_URL', SITE_URL.'/config/lang');

//载入核心模式: 默认是QimingDao.
if(!defined('CORE_MODE'))	qmdefine('CORE_MODE','QimingDao');

qmdefine('CORE_LIB_PATH'	,	CORE_PATH);
qmdefine('CORE_RUN_PATH'	,	SITE_PATH.'/_runtime');
qmdefine('CLIENT_MULTI_RESULTS', 131072);


//注册AUTOLOAD方法
if(function_exists('spl_autoload_register') ){
	spl_autoload_register('qmautoload'); 
}else{
	exit('function not exist : spl_autoload_register');
}
	

//载入核心运行时文件

$qm['_widget_appname'] = isset($_REQUEST['widget_appname']) && !empty($_REQUEST['widget_appname'])  ? $_REQUEST['widget_appname'] :'';

// if(file_exists(CONF_PATH.'/'.CORE_MODE.'Runtime.php')){
// 	RuntimeModel();
// 	include CONF_PATH.'/'.CORE_MODE.'Runtime.php';
// }else{
	include CORE_LIB_PATH.'/'.CORE_MODE.'.php';
// }

/* 核心方法 */

/**
 * 载入文件 去重\缓存.
 * @param string $filename 载入的文件名
 * @return boolean
 */
function qmload($filename,$set=false) {
	static $_importFiles = array();	//已载入的文件列表缓存
	$key = strtolower($filename);
	if($set){
		$_importFiles[$key] = true;
		return true;
	}
	if(!isset($_importFiles[$key]) ||  !$_importFiles[$key] ){
		if (file_exists($filename)) {
			require_once $filename;
			$_importFiles[$key] = true;
		} elseif(file_exists(CORE_LIB_PATH.'/'.$filename.'.class.php')) {
			require_once CORE_LIB_PATH.'/'.$filename.'.class.php';
			$_importFiles[$key] = true;
		} else {
			$_importFiles[$key] = false;
		}
	}
	return $_importFiles[$key];
}


/**
 * 系统自动加载函数
 * @param string $classname 对象类名
 * @return void
 */
function qmautoload($classname) {
	// 检查是否存在别名定义
	if(qmload($classname)) return ;
	// 自动加载当前项目的Actioon类和Model类
	if(substr($classname,-5)=="Model") {
		if(!$res = qmload(ADDON_PATH.'/model/'.$classname.'.class.php')){
            $res =qmload(APP_LIB_PATH.'/Model/'.$classname.'.class.php');
		}
        $res = true;
	}elseif(substr($classname,-6)=="Action"){
		$res = qmload(APP_LIB_PATH.'/Action/'.$classname.'.class.php');

	}elseif(substr($classname,-6)=="Widget"){
		if(!$res = qmload(ADDON_PATH.'/widget/'.$classname.'.class.php')){
            $res = qmload(APP_LIB_PATH.'/Widget/'.$classname.'.class.php');
        }	

	}elseif(substr($classname,-6)=="Addons"){
		if(!$res = qmload(ADDON_PATH.'/plugin/'.$classname.'.class.php'))
            $res = qmload(APP_LIB_PATH.'/Plugin/'.$classname.'.class.php');
	}else{
		$paths = array(
				ADDON_PATH.'/liberary',
				);
		foreach ($paths as $path){
			
			if(qmload($path.'/'.$classname.'.class.php')){
				// 如果加载类成功则返回
				return ;
			}
		}
	}
    !$res && throw_exception('file '.$classname.' is not exists');
	return ;
}

/**
 * 定义常量,replace==false时只定义不存在的常量.replace==true时重新定义.
 *
 * @param string $name 常量名
 * @param string $value 常量值
 * @param boolean $replace 是否需要重新定义
 * @return string $str 返回16位的字符串
 */
function qmdefine($name,$value,$replace=false) {
	global $qm;
	//定义未定义的常量
	if(!defined($name)){
		//定义新常量
		define($name,$value);
	}elseif(!$replace){
		//返回已定义的值
		$value	=	constant($name);
	}else{
		//需要替换的重新定义
		define($name,$value);
	}
	//缓存已定义常量列表
	$qm['_define'][$name]	=	$value;
	return;
}

/**
 * 返回16位md5值
 *
 * @param string $str 字符串
 * @return string $str 返回16位的字符串
 */
function qm_md5($str) {
	return substr(md5($str),8,16);
}

/**
 * @param string $name 配置名/文件名.
 * @param string|array|object $value 配置赋值
 * @return void|null
 */
function qmconfig($name=null,$value=null) {
    global $qm;
    // 无参数时获取所有
    if(empty($name)) return $qm['_config'];
    // 优先执行设置获取或赋值
    if (is_string($name))
    {
        if (!strpos($name,'.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($qm['_config'][$name])? $qm['_config'][$name] : null;
            $qm['_config'][$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.',$name);
        $name[0]   = strtolower($name[0]);
        if (is_null($value))
            return isset($qm['_config'][$name[0]][$name[1]]) ? $qm['_config'][$name[0]][$name[1]] : null;
        $qm['_config'][$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if(is_array($name))
        return $qm['_config'] = array_merge((array)$qm['_config'],array_change_key_case($name));
    return null;// 避免非法参数
}

/**
 * 执行钩子方法
 *
 * @param string $name 钩子方法名.
 * @param array $params 钩子参数数组.
 * @return array|string Stripped array (or string in the callback).
 */
function qmhook($name,$params=array()) {
	global $qm;
    $hooks	=	$qm['_config']['hooks'][$name];
    if($hooks) {
        foreach ($hooks as $call){
            if(is_callable($call))
                $result = call_user_func_array($call,$params);
        }
        return $result;
    }
    return false;
}

/**
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 * @param array|string $value The array or string to be striped.
 * @return array|string Stripped array (or string in the callback).
 */
function stripslashes_deep($value) {
	if ( is_array($value) ) {
		$value = array_map('stripslashes_deep', $value);
	} elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ($vars as $key=>$data) {
			$value->{$key} = stripslashes_deep( $data );
		}
	} else {
		$value = stripslashes($value);
	}
	return $value;
}

/**
 * GPC参数过滤
 * @param array|string $value The array or string to be striped.
 * @return array|string Stripped array (or string in the callback).
 */
function check_gpc($value=array()) {
	if(is_array($value)){
		foreach ($value as $key=>$data) {
			//对get、post的key值做限制，只允许数字、字母、及部分符号_-[]#@~
			if(!preg_match('/^[a-zA-z0-9_\-#!@~\[\]]+$/i',$key)){
				throw_exception('wrong_parameter:gpc key=>'.htmlspecialchars(strip_tags($key)));
			}

			//如果key值为app\mod\act,对value也做如上限制
			if( ($key=='app' || $key=='mod' || $key=='act') && !empty($data) ){
				if(!preg_match('/^[a-zA-z0-9_]+$/i',$data))
					throw_exception('wrong_parameter:gpc value=>'.htmlspecialchars(strip_tags($key)));
			}else{
				if(!preg_match('/^[a-zA-z0-9_\-#!@~\[\]]+$/i',$key))
					throw_exception('wrong_parameter:gpc key=>'.htmlspecialchars(strip_tags($key)));
			}
		}
	}
}

//全站静态缓存,替换之前每个model类中使用的静态缓存
//类似于s和f函数的使用

$static_cache = array();

function  static_cache($cache_id,$value=null,$clean = false){

    if($clean){ //清空缓存 其实是清不了的 程序执行结束才会自动清理
        unset($GLOBALS['static_cache']);
        $GLOBALS['static_cache'] = array();
        return $GLOBALS['static_cache'];
    }
    if(empty($cache_id)){
        return false;
    }
    if($value === null){
        //获取缓存数据
        return isset($GLOBALS['static_cache'][$cache_id]) ? $GLOBALS['static_cache'][$cache_id] : false;
    }else{
        //设置缓存数据
        $GLOBALS['static_cache'][$cache_id] = $value;
        return $GLOBALS['static_cache'][$cache_id];
    }
}
/**
 * 设定预载文件
 */
function RuntimeModel(){
    qmdefine('RUNTIME_MODEL',true);
    $coreFileList = include(CONF_PATH.'/coreFileList.php');
    //标记载入文件列表
    foreach ($coreFileList as $v) {
       qmload($v,true); 
    } 
}
?>