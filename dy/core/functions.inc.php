<?php
/**
 +----------------------------------------------------------
 * Cookie 设置、获取、清除 (支持数组或对象直接设置) 2009-07-9
 +----------------------------------------------------------
 * 1 获取cookie: cookie('name')
 * 2 清空当前设置前缀的所有cookie: cookie(null)
 * 3 删除指定前缀所有cookie: cookie(null,'think_') | 注：前缀将不区分大小写
 * 4 设置cookie: cookie('name','value') | 指定保存时间: cookie('name','value',3600)
 * 5 删除cookie: cookie('name',null)
 * 6 设置cookie会话期有效，传入option=-1
 * 7 默认为http_only,如不需要请传入 null
 +----------------------------------------------------------
 * $option 可用设置prefix,expire,path,domain
 * 支持数组形式:cookie('name','value',array('expire'=>1,'prefix'=>'think_'))
 * 支持query形式字符串:cookie('name','value','prefix=tp_&expire=10000')
 * 2010-1-17 去掉自动序列化操作，兼容其他语言程序。
 */
function cookie($name,$value='',$option=null, $http_only=1)
{
    // 默认设置
    $config = array(
        'prefix' => C('COOKIE_PREFIX'), // cookie 名称前缀
        'expire' => C('COOKIE_EXPIRE'), // cookie 保存时间
        'path'   => C('COOKIE_PATH'),   // cookie 保存路径
        'domain' => C('COOKIE_DOMAIN'), // cookie 有效域名
    );

    // 参数设置(会覆盖黙认设置)
    if (!empty($option)) {
        if (is_numeric($option)) {
            $option = array('expire'=>$option);
        }else if( is_string($option) ) {
            parse_str($option,$option);
    	}
    	$config	=	array_merge($config,array_change_key_case($option));
    }

    // 清除指定前缀的所有cookie
    if (is_null($name)) {
       if (empty($_COOKIE)) return;
       // 要删除的cookie前缀，不指定则删除config设置的指定前缀
       $prefix = empty($value)? $config['prefix'] : $value;
       if (!empty($prefix))// 如果前缀为空字符串将不作处理直接返回
       {
           foreach($_COOKIE as $key=>$val) {
               if (0 === stripos($key,$prefix)){
                    setcookie($_COOKIE[$key],'',time()-3600,$config['path'],$config['domain'],'',$http_only);
                    unset($_COOKIE[$key]);
               }
           }
       }
       return;
    }
    $name = $config['prefix'].$name;

    if (''===$value){
        return isset($_COOKIE[$name]) ? ($_COOKIE[$name]) : null;// 获取指定Cookie
    }else {
        if (is_null($value)) {
            setcookie($name,'',time()-3600,$config['path'],$config['domain']);
            unset($_COOKIE[$name]);// 删除指定cookie
        }else {
            $expire = !empty($config['expire'])&&$config['expire']>0? 
                        time()+ intval($config['expire'])
                        :$config['expire'];
            setcookie($name,$value,$expire,$config['path'],$config['domain'],'',$http_only);
        }
    }
}

/**
 +----------------------------------------------------------
 * 是否AJAX请求
 +----------------------------------------------------------
 * @access protected
 +----------------------------------------------------------
 * @return bool
 +----------------------------------------------------------
 */
function isAjax() {
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
		if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
			return true;
	}
	if(!empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')]))
		return true;
	return false;
}

/**
 +----------------------------------------------------------
 * 字符串命名风格转换
 * type
 * =0 将Java风格转换为C的风格
 * =1 将C风格转换为Java的风格
 +----------------------------------------------------------
 * @access protected
 +----------------------------------------------------------
 * @param string $name 字符串
 * @param integer $type 转换类型
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function parse_name($name,$type=0) {
    if($type) {
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    }else{
        $name = preg_replace("/[A-Z]/", "_\\0", $name);
        return strtolower(trim($name, "_"));
    }
}


function dump($var, $return=false) {
	ob_start();
	var_dump($var);
	$output = ob_get_clean();
    $output = str_replace(SITE_PATH, '[SITE_PATH]', $output);
	if(!extension_loaded('xdebug')) {
		$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
		$output = '<pre style="text-align:left">'. htmlspecialchars($output, ENT_QUOTES). '</pre>';
	}
    if (!$return) {
    	echo '<pre style="text-align:left">';
        echo($output);
        echo '</pre>';
    }else
        return $output;
}

// 自定义异常处理
function throw_exception($msg,$type='') {
    if(defined('IS_CGI') && IS_CGI)   exit($msg);
    
    $msg = str_replace(SITE_PATH, '[SITE_PATH]', $msg);
    
    //线上运行模式，返回首页
    if(!C('DEV_MOD')){
        Log::write($msg,'ERR');
        header("Location:".SITE_URL);exit();
    } 
    
    if(class_exists($type,false))
        throw new $type($msg,$code,true);
    else
        die($msg);        // 异常类型不存在则输出错误信息字串
}

function halt($text) {
	return dump($text);
}

// 区分大小写的文件存在判断
function file_exists_case($filename) {
    if(is_file($filename)) {
        if(IS_WIN && C('APP_FILE_CASE')) {
            if(basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}

// 根据PHP各种类型变量生成唯一标识号
function to_guid_string($mix)
{
    if(is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    }elseif(is_resource($mix)){
        $mix = get_resource_type($mix).strval($mix);
    }else{
        $mix = serialize($mix);
    }
    return md5($mix);
}

// 取得对象实例 支持调用类的静态方法
function get_instance_of($name,$method='',$args=array())
{
    static $_instance = array();
    $identify   =   empty($args)?$name.$method:$name.$method.to_guid_string($args);
    if (!isset($_instance[$identify])) {
        if(class_exists($name)){
            $o = new $name();
            if(method_exists($o,$method)){
                if(!empty($args)) {
                    $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
                }else {
                    $_instance[$identify] = $o->$method();
                }
            }
            else
                $_instance[$identify] = $o;
        }
        else
            halt(L('_CLASS_NOT_EXIST_').':'.$name);
    }
    return $_instance[$identify];
}

function __autoload($name) {
    // 检查是否存在别名定义
    if(import($name)) return ;
    // 自动加载当前项目的Actioon类和Model类
    if(substr($name,-5)=="Model") {
        import(LIB_PATH.'Model/'.ucfirst($name).'.class.php');
    }elseif(substr($name,-6)=="Action"){
        import(LIB_PATH.'Action/'.ucfirst($name).'.class.php');
    }else {
        // 根据自动加载路径设置进行尝试搜索
        if(C('APP_AUTOLOAD_PATH')) {
            $paths  =   explode(',',C('APP_AUTOLOAD_PATH'));
            foreach ($paths as $path){
                if(import($path.'/'.$name.'.class.php')) {
                    // 如果加载类成功则返回
                    return ;
                }
            }
        }
    }
    return ;
}

function import($filename) {
    static $_importFiles = array();
    global $qm;
	//$filename   =  realpath($filename);
    if (!isset($_importFiles[$filename])) {
		if(file_exists($filename)){
            qmload($filename);
            $_importFiles[$filename] = true;
        }
		else
		{
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}


function C($name=null,$value=null)
{
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
 +----------------------------------------------------------
 * D函数用于实例化Model
 +----------------------------------------------------------
 * @param string name Model名称
 * @param string app Model所在项目
 +----------------------------------------------------------
 * @return Model
 +----------------------------------------------------------
 */
function D($name='',$app='@')
{
    static $_model = array();
    if(empty($name)) return new Model;
    if(empty($app) || $app=='@')   $app =  APP_NAME;

    $names = explode('_',$name);
    $name  = '';
    
    foreach($names as $v){
        $name .= ucfirst($v); 
    }
    
    	
    if(isset($_model[$app.$name]))
        return $_model[$app.$name];

    $OriClassName = $name;
	$className =  $name.'Model';

    //优先载入核心的 所以不要和核心的model重名
    if(file_exists(ADDON_PATH.'/model/'.$className.'.class.php')){
		qmload(ADDON_PATH.'/model/'.$className.'.class.php');
	}elseif(file_exists(APPS_PATH.'/'.$app.'/Model/'.$className.'.class.php')){
        $common = APPS_PATH.'/'.$app.'/Common/common.php';
        if(file_exists($common)){
            qmload($common);
        }
        qmload(APPS_PATH.'/'.$app.'/Model/'.$className.'.class.php');
    }
    
    if(class_exists($className)) {

        $model = new $className();
    }else{
        $model  = new Model($name);
    }
    $_model[$app.$OriClassName] =  $model;
    return $model;
}

function A($name,$app='@')
{
    static $_action = array();

	if(empty($app) || $app=='@')   $app =  APP_NAME;

    if(isset($_action[$app.$name]))
        return $_action[$app.$name];

    $OriClassName = $name;
    $className =  $name.'Action';
    qmload(APP_ACTION_PATH.'/'.$className.'.class.php');

    if(class_exists($className)) {
        $action = new $className();
        $_action[$app.$OriClassName] = $action;
        return $action;
    }else {
        return false;
    }
}

function L($key,$data = array()){
    $key = strtoupper($key);
     if(!isset($GLOBALS['_lang'][$key])){
          return $key;
     }
     if(empty($data)){
          return $GLOBALS['_lang'][$key];
     }
     $replace = array_keys($data);
     foreach($replace as &$v){
        $v = "{".$v."}";
     }
     return str_replace($replace,$data,$GLOBALS['_lang'][$key]);
}

/**
 * 纯文本过滤
 * @param  [type] $text [description]
 * @param  [type] $sql 是否防止sql注入,默认为true
 * @return [type]       [description]
 */
function t($text, $sql=false){
    $text = str_replace(array('&nbsp;','=',"'",'"','onmousever','prompt'), '', $text);
    $text = htmlspecialchars_decode($text);
    //为防止多次t的情况，先进行一次反转义
    $text  = trim(strip_tags($text));
    $text = stripslashes($text);
    $text  = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    return $text;
}


function ej($info,$json = true){
    if($GLOBALS['TEST'] == 1){
        return $info;
    }else{
        echo $json ? json_encode($info) : $info;
        exit();
    }
}

/** 
 * 只保留一部分合法标签，适用于编辑器中内容输入
 * @param string $text 待过滤的字符串
 * @param string $type 保留的标签格式
 */
function h($text,$type='html'){

    //无标签格式
    $text_tags  =   '';

    //只存在字体样式
    $font_tags  =   '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';

    //标题摘要基本格式
    $base_tags  =   $font_tags.'<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';

    //内容等允许HTML的格式
    $html_tags  =   $base_tags.'<form><input><textarea><button><select><optgroup><option><label><fieldset><legend><ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed>';

    //专题等全HTML格式
    $all_tags   =   $form_tags.$html_tags.'<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';

    //过滤标签
    $text   =   strip_tags($text, ${$type.'_tags'} );

    //过滤攻击代码
    if($type!='all'){
        //过滤危险的属性，如：过滤on事件lang js
        while(preg_match('/(<[^><]+) (onclick|onload|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i',$text,$mat)){
            $text   =   str_ireplace($mat[0],$mat[1].$mat[3],$text);
        }
        while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
            $text   =   str_ireplace($mat[0],$mat[1].$mat[3],$text);
        }
    }
    return $text;
}

function U($url,$params=false,$redirect=false,$suffix=true) {

	//普通模式
	if(false==strpos($url,'/')){
		$url	.='//';
	}

	//填充默认参数
	$urls	=	explode('/',$url);
	$app	=	isset($urls[0]) && !empty($urls[0]) ? $urls[0] : APP_NAME;
	$mod	=	isset($urls[1]) && !empty($urls[1]) ? $urls[1] : 'Index';
	$act	=	isset($urls[2]) && !empty($urls[2]) ? $urls[2] : 'index';

	//组合默认路径
	$site_url	=	SITE_URL.'/index.php?app='.$app.'&mod='.$mod.'&act='.$act;

	//填充附加参数
	if($params){
		if(is_array($params)){
			$params	=	http_build_query($params);
			$params	=	urldecode($params);
		}
		$params		=	str_replace('&amp;','&',$params);
		$site_url	.=	'&'.$params;
	}

	//开启路由和Rewrite
	if(C('URL_ROUTER_ON')){

		//载入路由
		$router_ruler	=	C('router');
		$router_key		=	$app.'/'.ucfirst($mod).'/'.$act;

		//路由命中
		if(isset($router_ruler[$router_key])){

			//填充路由参数
			if(false==strpos($router_ruler[$router_key],'://')){
				$site_url	=	SITE_URL.'/'.$router_ruler[$router_key];
			}else{
				$site_url	=	$router_ruler[$router_key];
			}

			//填充附加参数
			if($params){

				//解析替换URL中的参数
				parse_str($params,$r);
				foreach($r as $k=>$v){
					if(strpos($site_url,'['.$k.']')){
						$site_url	=	str_replace('['.$k.']',$v,$site_url);
					}else{
						$lr[$k]	=	$v;
					}
				}

				//填充剩余参数
				if(is_array($lr) && count($lr)>0){
					$site_url	.=	'?'.http_build_query($lr);
				}

			}
		}
	}

	//输出地址或跳转
	if($redirect){
		redirect($site_url);
	}else{
		return $site_url;
	}
}

function redirect($url,$time=0,$msg='')
{
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);
    if(empty($msg))
        $msg    =   L('PUBLIC_SYSTEM_JUMP_TO',array('time'=>$time,'url'=>$url));
    if (!headers_sent()) {
        // redirect
        if(0===$time) {
            header("Location: ".$url);
        }else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if($time!=0)
            $str   .=   $msg;
        exit($str);
    }
}

/**
 * 用来对应用缓存信息的读、写、删除
 *
 * $expire = null/0 表示永久缓存，否则为缓存有效期
 */
function S($name,$value='',$expire=null)
{

	static $_cache = array();	//减少缓存读取

	$cache = model('Cache');


	if('' !== $value) {

		if(is_null($value)) {
			// 删除缓存
			$result =   $cache->rm($name);
			if($result)   unset($_cache[$name]);
			return $result;
		}else{
			// 缓存数据
			$cache->set($name,$value,$expire);
			$_cache[$name]     =   $value;
		}
		return true;
	}
	if(isset($_cache[$name]))
		return $_cache[$name];
	// 获取缓存数据
	$value      =  $cache->get($name);
	$_cache[$name]     =   $value;
	return $value;
}

/**
 * 文件缓存,多用来缓存配置信息
 *
 */
function F($name,$value='',$path=false) {
    static $_cache = array();
    if(!$path) {
    	$path	=	C('F_CACHE_PATH');
    }
    if(!is_dir($path)) {
    	mkdir($path,0777,true);
    }
    $filename   =   $path.'/'.$name.'.php';
    if('' !== $value) {
        if(is_null($value)) {
            // 删除缓存
            return unlink($filename);
        }else{
            // 缓存数据
            $dir   =  dirname($filename);
            // 目录不存在则创建
            if(!is_dir($dir))  mkdir($dir,0777,true);
            return @file_put_contents($filename,"<?php\nreturn ".var_export($value,true).";\n?>");
        }
    }
    if(isset($_cache[$name])) return $_cache[$name];
    // 获取缓存数据
    if(is_file($filename)) {
        $value   =  include $filename;
        $_cache[$name]   =   $value;
    }else{
        $value  =   false;
    }
    return $value;
}

function lunar($date){
    $lunar = new Lunar();
    return $lunar->S2L($date);
}

function festival($date){
    $temp=$f_lunar=$f_solar='';
    $lunar_cls = new Lunar();
    $nl_date  = date("Y-m-d",$lunar_cls->S2LD($date));  //获取农历
    $arr_lunar=array('01-01'=>'春节','01-15'=>'元宵节','05-05'=>'端午节','07-07'=>'七夕情人节','08-15'=>'中秋节','09-09'=>'重阳节');
    $arr_solar=array('01-01'=>'元旦','02-14'=>'情人节','03-08'=>'妇女节','03-12'=>'植树节','04-01'=>'愚人节','04-08'=>'复活节','05-01'=>'劳动节','05-04'=>'青年节','05-12'=>'护士节','05-31'=>'无烟日','06-01'=>'儿童节','07-01'=>'建党节','08-01'=>'建军节','09-10'=>'教师节','10-01'=>'国庆节','10-31'=>'万圣节','12-24'=>'平安夜','12-25'=>'圣诞节');
    $md_lunar=substr_replace($nl_date,'',0,5);
    $md_solar=substr_replace($date,'',0,5);
    $f_lunar=$arr_lunar[$md_lunar];
    $f_solar=$arr_solar[$md_solar];
    if(!empty($f_lunar) && !empty($f_solar)){$temp='/';}
    return trim($f_lunar.$temp.$f_solar);
}
function S2LDATE($date){
    $lunar = new Lunar();
    return $lunar->S2LDATE($date);
}


// 实例化model
function model($name,$params=array()) {
    return X($name,$params,'Model');
}

// 调用接口服务
function X($name,$params=array(),$domain='Model') {
    static $_service = array();

    $app =  TRUE_APPNAME;

    if(isset($_service[$domain.'_'.$app.'_'.$name]))
        return $_service[$domain.'_'.$app.'_'.$name];

	$class = $name.$domain;

	if(file_exists(APPS_PATH.'/'.$app.'/'.$domain.'/'.$class.'.class.php')){
		qmload(APPS_PATH.'/'.$app.'/'.$domain.'/'.$class.'.class.php');
	}else{
	 	qmload(ADDON_PATH.'/'.strtolower($domain).'/'.$class.'.class.php');
	}
	//服务不可用时 记录日志 或 抛出异常
	if(class_exists($class)){
		$obj   =  new $class($params);
		$_service[$domain.'_'.$app.'_'.$name] =  $obj;
		return $obj;
	}else{
		throw_exception(L('_CLASS_NOT_EXIST_').':'.$class);
	}
}

// 渲染模板
//$charset 不能是UTF8 否则IE下会乱码
function fetch($templateFile='',$tvar=array(),$charset='utf-8',$contentType='text/html',$display=false) {
	//注入全局变量ts
	global	$qm;
	$tvar['qm'] = $qm;

	if(null===$templateFile)
		// 使用null参数作为模版名直接返回不做任何输出
		return ;

	if(empty($charset))  $charset = C('DEFAULT_CHARSET');

	// 网页字符编码
	header("Content-Type:".$contentType."; charset=".$charset);

	header("Cache-control: private");  //支持页面回跳

	//页面缓存
	ob_start();
	ob_implicit_flush(0);

	// 模版名为空.
	if(''==$templateFile){
		$templateFile	=	APP_TPL_PATH.'/'.MODULE_NAME.'/'.ACTION_NAME.'.html';

	// 模版名为ACTION_NAME
	}elseif(file_exists(APP_TPL_PATH.'/'.MODULE_NAME.'/'.$templateFile.'.html')) {
		$templateFile	=	APP_TPL_PATH.'/'.MODULE_NAME.'/'.$templateFile.'.html';

	// 模版是绝对路径
	}elseif(file_exists($templateFile)){

	// 模版不存在
	}else{
		throw_exception(L('_TEMPLATE_NOT_EXIST_').'['.$templateFile.']');
	}

    //模版缓存文件
	$templateCacheFile	=	C('TMPL_CACHE_PATH').'/'.qm_md5($templateFile).'.php';

	//载入模版缓存
	if(!$qm['_debug'] && file_exists($templateCacheFile)) {
	
		extract($tvar, EXTR_OVERWRITE);

		//载入模版缓存文件
		include $templateCacheFile;

	//重新编译
	}else{

		qmhook('tpl_compile',array('templateFile',$templateFile));

		$tpl	=	Template::getInstance();
		// 编译并加载模板文件
		$tpl->load($templateFile,$tvar,$charset);
	}

	// 获取并清空缓存
	$content = ob_get_clean();

	// 模板内容替换
    $replace =  array(
        '__ROOT__'      =>  SITE_URL,           // 当前网站地址
        '__UPLOAD__'    =>  UPLOAD_URL,         // 上传文件地址
        '__PUBLIC__'    =>  PUBLIC_URL,         // 公共静态地址
        '__THEME__'     =>  THEME_PUBLIC_URL,   // 主题静态地址
        '__APP__'       =>  APP_PUBLIC_URL,     // 应用静态地址
    );

    if(C('TOKEN_ON')) {
        if(strpos($content,'{__TOKEN__}')) {
            // 指定表单令牌隐藏域位置
            $replace['{__TOKEN__}'] =  $this->buildFormToken();
        }elseif(strpos($content,'{__NOTOKEN__}')){
            // 标记为不需要令牌验证
            $replace['{__NOTOKEN__}'] =  '';
        }elseif(preg_match('/<\/form(\s*)>/is',$content,$match)) {
            // 智能生成表单令牌隐藏域
            $replace[$match[0]] = $this->buildFormToken().$match[0];
        }
    }

    // 允许用户自定义模板的字符串替换
    if(is_array(C('TMPL_PARSE_STRING')) )
        $replace =  array_merge($replace,C('TMPL_PARSE_STRING'));

    $content = str_replace(array_keys($replace),array_values($replace),$content);

	// 布局模板解析
	//$content = $this->layout($content,$charset,$contentType);
    // 输出模板文件
	if($display)
		echo $content;
	else
		return $content;
}

// 输出模版
function display($templateFile='',$tvar=array(),$charset='utf-8',$contentType='text/html') {
	fetch($templateFile,$tvar,$charset,$contentType,true);
}

function mk_dir($dir)
{ 
  if (is_dir($dir) || mkdir($dir,0666,true)) return true;
  if (!mk_dir(dirname($dir),0666)) return false;
  return mkdir($dir,0666,true);
}

/**
 +----------------------------------------------------------
 * 字节格式化 把字节数格式为 B K M G T 描述的大小
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function byte_format($size, $dec=2) {
	$a = array("B", "KB", "MB", "GB", "TB", "PB");
	$pos = 0;
	while ($size >= 1024) {
		 $size /= 1024;
		   $pos++;
	}
	return round($size,$dec)." ".$a[$pos];
}

/**
 +------------------------------------------------------------------------------
 * Think扩展函数库 需要手动加载后调用或者放入项目函数库
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   Qiming Chuangxiang  
 * @version  $Id$
 +------------------------------------------------------------------------------
 */

/**
 * 获取客户端IP地址
 */
function get_client_ip() {
    if( isset($GLOBALS['user_ip']) ){
        return $GLOBALS['user_ip'];
    }
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
       $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
       $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
       $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
       $ip = $_SERVER['REMOTE_ADDR'];
    else
       $ip = "unknown";
    if($ip != "unknown"){
        $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
        !preg_match($preg,$ip) && $ip = 'errorIp';
    }
    $GLOBALS['user_ip'] = $ip;
   return($ip);
}

/**
 * 记录日志
 * Enter description here ...
 * @param unknown_type $group
 * @param unknown_type $action
 * @param unknown_type $data
 */
function LogRecord($group,$action,$data){
	static $log = null;
	if($log == null){
		$log = model('Logs');
	}
	return $log->load($group)->action($action)->record($data);
}

/**
 * 去一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
 * @param $pArray 一个二维数组
 * @param $pKey 数组的键的名称
 * @return 返回新的一维数组
 */
function getSubByKey($pArray, $pKey=""){
    $result = array();
    if(!is_array($pArray)){
        return $result;
    }
    foreach($pArray as $temp_array){
    	if(is_object($temp_array)){
    		$temp_array = (array) $temp_array;
    	}
        $result[] = (""==$pKey) ? $temp_array : isset($temp_array[$pKey])
                    ? $temp_array[$pKey] : "";

    }
    return $result;
}

/**
 * 获取字符串的长度
 *
 * 计算时, 汉字或全角字符占1个长度, 英文字符占0.5个长度
 *
 * @param string  $str
 * @param boolean $filter 是否过滤html标签
 * @return int 字符串的长度
 */
function get_str_length($str, $filter = false)
{
	if ($filter) {
		$str = html_entity_decode($str, ENT_QUOTES);
		$str = strip_tags($str);
	}
	return (strlen($str) + mb_strlen($str, 'UTF8')) / 4;
}

/**
 * 截取字符串
 * @param  [type]  $str    [description]
 * @param  integer $length [description]
 * @param  string  $ext    [description]
 * @return [type]          [description]
 */
function getShort($str, $length = 40, $ext = '') {
	$str	=	htmlspecialchars($str);
	$str	=	strip_tags($str);
	$str	=	htmlspecialchars_decode($str);
	$strlenth	=	0;
	$out		=	'';
	preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match);
	foreach($match[0] as $v){
		preg_match("/[\xe0-\xef][\x80-\xbf]{2}/",$v, $matchs);
		if(!empty($matchs[0])){
			$strlenth	+=	1;
		}elseif(is_numeric($v)){
			//$strlenth	+=	0.545;  // 字符像素宽度比例 汉字为1
			$strlenth	+=	0.5;    // 字符字节长度比例 汉字为1
		}else{
			//$strlenth	+=	0.475;  // 字符像素宽度比例 汉字为1
			$strlenth	+=	0.5;    // 字符字节长度比例 汉字为1
		}

		if ($strlenth > $length) {
			$output .= $ext;
			break;
		}

		$output	.=	$v;
	}
	return $output;
}

/**
 +----------------------------------------------------------
 * 检查字符串是否是UTF8编码
 +----------------------------------------------------------
 * @param string $string 字符串
 +----------------------------------------------------------
 * @return Boolean
 +----------------------------------------------------------
 */
function is_utf8($string) {
    return preg_match('%^(?:
         [\x09\x0A\x0D\x20-\x7E]            # ASCII
       | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
       |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
       | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
       |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
       |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
       | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
       |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $string);
}
// 自动转换字符集 支持数组转换
function auto_charset($fContents,$from,$to){
    $from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
    $to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
    if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) ){
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if(is_string($fContents) ) {
        if(function_exists('mb_convert_encoding')){
            return mb_convert_encoding ($fContents, $to, $from);
        }elseif(function_exists('iconv')){
            return iconv($from,$to,$fContents);
        }else{
            return $fContents;
        }
    }
    elseif(is_array($fContents)){
        foreach ( $fContents as $key => $val ) {
            $_key =     auto_charset($key,$from,$to);
            $fContents[$_key] = auto_charset($val,$from,$to);
            if($key != $_key )
                unset($fContents[$key]);
        }
        return $fContents;
    }
    else{
        return $fContents;
    }
}

/**
 * 友好的时间显示
 * @param int    $sTime 待显示的时间
 * @param string $format 时间格式
 * @return string
 */
function friendlyDate($sTime, $format=''){
    //sTime=源时间，cTime=当前时间，dTime=时间差
    if(empty($sTime)) return '';
    $cTime      =   time();
    $dTime      =   $cTime - $sTime;

    if($dTime > 0 && $dTime < 60 ){
        return $dTime . ' ' . '秒前';
    } else if($dTime > 0 && $dTime < 3600 ){
        return intval($dTime/60) . ' ' . '分前';
    } else {
        if( 'Ymd' == $format){
            $time_format = array(
                'en'    => 'M d,Y',
                'zh-cn' => 'Y-m-d',
                'zh-tw' => 'Y-m-d'
            );
        }else{
             $time_format = array(
                'en'    => 'M d,Y H:i',
                'zh-cn' => 'Y-m-d H:i',
                'zh-tw' => 'Y-m-d H:i'
            );
        }
//        $lang_set = getLang();
        $lang_set = 'zh-cn';
        $format = $time_format[$lang_set];

        return date($format, $sTime);
    }
}

/**
 * 敏感词过滤
 */
function filter_keyword($html){
    static $audit  =null;
    static $auditSet = null;
    if($audit == null){ //第一次
        $audit = model('Xdata')->get('keywordConfig');
        $audit = explode(',',$audit);
        $auditSet =  model('Xdata')->get('admin_Config:audit');
    }
    // 不需要替换
    if(empty($audit) || $auditSet['open'] == '0'){
        return $html;
    }
    
    return str_replace($audit, $auditSet['replace'], $html);
}

/**
 * @see desencrypt()
 */
function pkcs5_pad($text, $blocksize) {
	$pad = $blocksize - (strlen($text) % $blocksize);
	return $text . str_repeat(chr($pad), $pad);
}

/**
 * @see desdecrypt()
 */
function pkcs5_unpad($text) {
	$pad = ord($text{strlen($text)-1});

	if ($pad > strlen($text))
		return false;
	if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
		return false;

	return substr($text, 0, -1 * $pad);
}

//获取字串首字母
function getFirstLetter($s0) {
    $firstchar_ord = ord(strtoupper($s0{0}));
    if($firstchar_ord >= 65 and $firstchar_ord <= 91) return strtoupper($s0{0});
    if($firstchar_ord >= 48 and $firstchar_ord <= 57) return '#';
    $s = iconv("UTF-8", "gb2312", $s0);
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if($asc>=-20319 and $asc<=-20284) return "A";
    if($asc>=-20283 and $asc<=-19776) return "B";
    if($asc>=-19775 and $asc<=-19219) return "C";
    if($asc>=-19218 and $asc<=-18711) return "D";
    if($asc>=-18710 and $asc<=-18527) return "E";
    if($asc>=-18526 and $asc<=-18240) return "F";
    if($asc>=-18239 and $asc<=-17923) return "G";
    if($asc>=-17922 and $asc<=-17418) return "H";
    if($asc>=-17417 and $asc<=-16475) return "J";
    if($asc>=-16474 and $asc<=-16213) return "K";
    if($asc>=-16212 and $asc<=-15641) return "L";
    if($asc>=-15640 and $asc<=-15166) return "M";
    if($asc>=-15165 and $asc<=-14923) return "N";
    if($asc>=-14922 and $asc<=-14915) return "O";
    if($asc>=-14914 and $asc<=-14631) return "P";
    if($asc>=-14630 and $asc<=-14150) return "Q";
    if($asc>=-14149 and $asc<=-14091) return "R";
    if($asc>=-14090 and $asc<=-13319) return "S";
    if($asc>=-13318 and $asc<=-12839) return "T";
    if($asc>=-12838 and $asc<=-12557) return "W";
    if($asc>=-12556 and $asc<=-11848) return "X";
    if($asc>=-11847 and $asc<=-11056) return "Y";
    if($asc>=-11055 and $asc<=-10247) return "Z";
    return '#';
}

function getStringList($str){
    preg_match_all("/./us", $str, $match);
    return $match[0];
}

// 区间调试开始
function debug_start($label='')
{
    $GLOBALS[$label]['_beginTime'] = microtime(TRUE);
    $GLOBALS[$label]['_beginMem'] = memory_get_usage();
}

// 区间调试结束，显示指定标记到当前位置的调试
function debug_end($label='')
{   
    
    $GLOBALS[$label]['_endTime'] = microtime(TRUE);
    $log =  'Process '.$label.': Times '.number_format($GLOBALS[$label]['_endTime']-$GLOBALS[$label]['_beginTime'],6).'s ';
    
    $GLOBALS[$label]['_endMem'] = memory_get_usage();
    $log .= ' Memories '.number_format(($GLOBALS[$label]['_endMem']-$GLOBALS[$label]['_beginMem'])/1024).' k';
    
    
    $GLOBALS['logs'][$label] = $log;
} 

// 给指定的附件ID获取相应的附件信息
function getAttachInfo($attach_ids) {
    if(empty($attach_ids)) {
        return array();
    }
    $map['attach_id'] = array('IN', $attach_ids);
    $data = model('Attach')->where($map)->getHashList('attach_id');
    return $data;
}

function exportExcel($data, $filename){
    qmload ( ADDON_PATH.'/liberary/phpExcel/excel/WCExcel.php' );
    $filename = getShort($filename, 30);
    $excel = new WCExcel();
    mkdir( DATA_PATH . '/export', 0777);
    $file  = DATA_PATH . '/export/'.$filename.'.xls';
    $res   = $excel->export($data, $filename, $file);
    $res_url = SITE_URL.'/data/export/'.$filename.'.xls';
    redirect($res_url);
}

// 组装附件信息
function setAttachInfo($attachInfo, $data) {
    if(empty($attachInfo) || empty($data)) {
        return $data;
    }
    $attach_ids = array();
    !empty($data['attach']) && $attach_ids = explode(',', $data['attach']);
    if(!empty($attach_ids)) {
        foreach($attach_ids as $value) {
            $data['attachInfo'][] = $attachInfo[$value];
        }
    }
    return $data;
}

//下载
function download($file,$file_name){
    $file_path ='';
    $filename = '';
    if (is_numeric($file)) {
        $attach =   model('Attach')->getDetail($file);
        if(!$attach){
            echo '附件不存在';
        return false;
        }
        $file_path = UPLOAD_PATH . '/' .$attach['save_path'] . $attach['save_name'];
        $filename = $attach['name'];
    }else{
        $file_path = $file;
        $filename = $file_name;
    }
    //下载函数
    qmload(ADDON_PATH.'/liberary/Http.class.php');
    if(file_exists($file_path)) {
        if(strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')){
            $filename = urlencode($filename);
        }
        Http::download($file_path, $filename);
    }else{
        echo '文件不存在';
        return false;
    }
}

/**
 * 从一段内容中得到图片的src
 * @return array $image_src;
 */
function getImageSrcFromHtml( $content ){
    if(empty($content)) return false;
    
    $doc = new DOMDocument();
    @$doc->loadHTML($content);
    $tags = $doc->getElementsByTagName('img');
    foreach ($tags as $tag) {
        $image_src[] =  $tag->getAttribute('src');
    }

    return $image_src;
}

function getImageAllowType(){
    return array(
                    'self',
                    'thumb_1024_786',
                    'thumb_100_100',  
                    'thumb_50_50',
                    'thumb_425_5000',
                    'thumb_570_5000',
                    'thumb_710_5000',
                    'thumb_5000_5000',
                    'thumb_200_200',
                    'cut_200_200',
                    'cut_160_160',
                    'cut_100_100',
                    'cut_120_120',
                    'cut_50_50',
                    'cut_20_20',
                    'cut_140_140',
                    'cut_64_64',
                    'cut_400_248'
                );
}

//[RUNTIME]
// 编译文件
function compile($filename,$runtime=false) {
    $content = file_get_contents($filename);
    if(true === $runtime)
        // 替换预编译指令
    //$content = preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s','',$content);
    $content = substr(trim($content),5);
    if('?>' == substr($content,-2))
        $content = substr($content,0,-2);
    return $content;
}

// 去除代码中的空白和注释
function strip_whitespace($content) {
    $stripStr = '';
    //分析php源码
    $tokens =   token_get_all ($content);
    $last_space = false;
    for ($i = 0, $j = count ($tokens); $i < $j; $i++)
    {
        if (is_string ($tokens[$i]))
        {
            $last_space = false;
            $stripStr .= $tokens[$i];
        }
        else
        {
            switch ($tokens[$i][0])
            {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space)
                    {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

// 根据数组生成常量定义
function array_define($array) {
    $content = '';
    foreach($array as $key=>$val) {
        $key =  strtoupper($key);
        if(is_int($val) || is_float($val)) {
            $content .= "qmdefine('".$key."',".$val.");";
        }elseif(is_bool($val)) {
            $val = ($val)?'true':'false';
            $content .= "qmdefine('".$key."',".$val.");";
        }elseif(is_string($val)) {
            $content .= "qmdefine('".$key."','".addslashes($val)."');";
        }
    }
    return $content;
}

//[/RUNTIME]

function json_to_array($json_data){
    $arr = (array) json_decode($json_data);
    foreach($arr as &$v){
        if(is_object($v)){
            $v=(array)($v);
        }
    }
    return $arr;
}

/**
 * 根据指定的键对数组排序
 *
 * 用法：
 * @code php
 * $rows = array(
 * array('id' => 1, 'value' => '1-1', 'parent' => 1),
 * array('id' => 2, 'value' => '2-1', 'parent' => 1),
 * array('id' => 3, 'value' => '3-1', 'parent' => 1),
 * array('id' => 4, 'value' => '4-1', 'parent' => 2),
 * array('id' => 5, 'value' => '5-1', 'parent' => 2),
 * array('id' => 6, 'value' => '6-1', 'parent' => 3),
 * );
 *
 * $rows = Helper_Array::sortByCol($rows, 'id', SORT_DESC);
 * dump($rows);
 * // 输出结果为：
 * // array(
 * //   array('id' => 6, 'value' => '6-1', 'parent' => 3),
 * //   array('id' => 5, 'value' => '5-1', 'parent' => 2),
 * //   array('id' => 4, 'value' => '4-1', 'parent' => 2),
 * //   array('id' => 3, 'value' => '3-1', 'parent' => 1),
 * //   array('id' => 2, 'value' => '2-1', 'parent' => 1),
 * //   array('id' => 1, 'value' => '1-1', 'parent' => 1),
 * // )
 * @endcode
 *
 * @param array $array 要排序的数组
 * @param string $keyname 排序的键
 * @param int $dir 排序方向
 *
 * @return array 排序后的数组
 */
function sortByCol($array, $keyname, $dir = SORT_ASC) {
    return sortByMultiCols ( $array, array ($keyname => $dir ) );
}

/**
 * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
 *
 * 用法：
 * @code php
 * $rows = Helper_Array::sortByMultiCols($rows, array(
 * 'parent' => SORT_ASC,
 * 'name' => SORT_DESC,
 * ));
 * @endcode
 *
 * @param array $rowset 要排序的数组
 * @param array $args 排序的键
 *
 * @return array 排序后的数组
 */
function sortByMultiCols($rowset, $args) {
    $sortArray = array ();
    $sortRule = '';
    foreach ( $args as $sortField => $sortDir ) {
        foreach ( $rowset as $offset => $row ) {
            $sortArray [$sortField] [$offset] = (int) $row [$sortField];
        }
        $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
    }
    if (empty ( $sortArray ) || empty ( $sortRule )) {
        return $rowset;
    }
    eval ( 'array_multisort(' . $sortRule . '$rowset);' );
    return $rowset;
}

/**
 * 检查Email地址是否合法
 * @return boolean
 */
function isValidEmail($email) {
    return preg_match('/[_a-zA-Z\d\-\.]+(@[_a-zA-Z\d\-]+\.[_a-zA-Z\d\-]+)+(\.[_a-zA-Z\d\-]+)*$/i', $email);
}

/**
 * 根据文件地址显示图片
 * @param  [type] $file_path [description]
 * @param  string $ext       [description]
 * @return [type]            [description]
 */
function showImage($file_path,$ext = 'jpg'){
    $chrono = filemtime($file_path);
    $offset = 60 * 60 * 24 * 7; //图片过期7天
    header ( "cache-control: private" );
    header ( "cache-control: max-age=".$offset );
    header('Last-Modified: '.gmdate(' D,d M Y H:i:s',$chrono).' GMT',true,200);
    header ( "Pragma: max-age=".$offset );
    header ( "Expires:" . gmdate ( "D, d M Y H:i:s", time () + $offset ) . " GMT" );
    set_cache_limit($offset);
    header ( "Content-type: image/" . $ext); 
    readfile( $file_path );
}

function set_cache_limit($second=1)
{
    $second=intval($second); 
    if($second==0) {
        return;
    }
    
    $id = $_SERVER['HTTP_IF_NONE_MATCH'];
    $etag=time()."||".base64_encode( $_SERVER['REQUEST_URI'] );
    if( $id=='' )
    {//无tag，发送新tag
        header("Etag:$etag",true,200);  
        return;
    }
    list( $time , $uri )=explode ( "||" , $id );
    if($time < (time()-$second))
    {//过期了，发送新tag
        header("Etag:$etag",true,200);
    }else
    {//未过期，发送旧tag
        header("Etag:$id",true,304);        
        exit(-1);
    }
} 

/**
 * 纯文本关键词高亮显示
 * @param [type] $string 需要高亮的串
 * @param [type] keywords 搜索关键字,多个用|隔开
 * @return [type] [description]
 */
function highlight($string,$keywords = ''){
    if(empty($keywords)){
        return $string;
    }
    return preg_replace("/($keywords)/si",'<em>$1</em>',$string);
}

/**
 * 加密函数
 * @param  string   $txt
 * @param  string   $key  //加密密钥，默认读取SECURE_CODE配置
 * @return string   $txt  //加密后的字符串
 */
function jiami($txt, $key = null) {
    if (empty ( $key ))
    $key = 'cofco_oam';
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
    $nh = rand ( 0, 64 );
    $ch = $chars [$nh];
    $mdKey = md5 ( $key . $ch );
    $mdKey = substr ( $mdKey, $nh % 8, $nh % 8 + 7 );

    $txt = base64_encode ( $txt );
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for($i = 0; $i < strlen ( $txt ); $i ++) {
        $k = $k == strlen ( $mdKey ) ? 0 : $k;
        $j = ($nh + strpos ( $chars, $txt [$i] ) + ord ( $mdKey [$k ++] )) % 64;
        $tmp .= $chars [$j];
    }
    return str_replace('=','.',$ch . $tmp);
}


//解密
function jiemi($txt, $key = null) {
    $txt = str_replace('.','=',$txt);
    if (empty ( $key ))
        $key = 'cofco_oam';
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
    $ch = $txt [0];
    $nh = strpos ( $chars, $ch );
    $mdKey = md5 ( $key . $ch );
    $mdKey = substr ( $mdKey, $nh % 8, $nh % 8 + 7 );
    $txt = substr ( $txt, 1 );
    $tmp = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for($i = 0; $i < strlen ( $txt ); $i ++) {
        $k = $k == strlen ( $mdKey ) ? 0 : $k;
        $j = strpos ( $chars, $txt [$i] ) - $nh - ord ( $mdKey [$k ++] );
        while ( $j < 0 )
            $j += 64;
        $tmp .= $chars [$j];
    }
    return base64_decode ( $tmp );
}

/**
 +----------------------------------------------------------
 * 对查询结果集进行排序
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 +----------------------------------------------------------
 * @return array
 +----------------------------------------------------------
 */
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}

/**
* 日期展示
* @param  [type] $config [description]
* @return [type]         [description]
*/
function wDate($config){
    $default = array('mod'=>'Ymd','name'=>'rcalendar','id'=>'rcalendar','value'=>'','placeholder'=>'');
    $config = array_merge($default,$config);
    if(!$config['callback']){
        $config['callback'] = '';
    }
    return W('date',$config);
}

/**
* 联想搜索
*/
function wSearch($config){
    $default = array('max'=>0,'model_name'=>'student','app_name'=>'edu','search_method'=>'getSearchList','editable'=>1,'id'=>'',
                'detail_method'=>'getSearchDetail','placeholder'=>'','inputname'=>'','autostart'=>0,'default_ids'=>'',
                'width'=>200,'selectFirst'=>1,'length'=>14);
    $config = array_merge($default,$config);
    if(empty($config['list_width'])){
        $config['list_width'] = $config['width'];
    }
    if(empty($config['id'])){
        $config['id'] = $config['inputname'];
    }
    //默认参数
    if(isset($config['default_ids']) && !empty($config['default_ids'])){
        $config['default_ids']  = is_array($config['default_ids']) ? $config['default_ids'] : explode(',',$config['default_ids']);
        $do = D($config['model_name'],$config['app_name']);
        $config['default_ids'] = array_unique($config['default_ids']);
        foreach($config['default_ids']  as $id){
            //注，查询单条的方法名统一为getSearchDeail
            if(!empty($id)){
                $info = $do->$config['detail_method']($id);
                !empty($info) && $config['search_list'][] = $info;
            }
        }
        $config['default_ids'] = implode(',',$config['default_ids']);
    }else{
        $config['default_ids'] = '';
    }
    if(!empty($config['tpl'])){
        return W($config['tpl'],$config);
    }else{
        return W('search',$config);
    }
}

//简化了的widegt实现
function W($name,$config){
    return fetch(THEME_PATH.'/widget/'.$name.'.html',$config); 
}


function showStudentAvatar($student_id,$attach_id = ''){
    if(empty($attach_id)){
        $map['student_id'] = $student_id;
        $student_info = model('Student')->where($map)->find();
        $attach_id = $student_info['attach_id'];
    }
    if(empty($attach_id)){
        return THEME_PUBLIC_URL.'/image/avatar.jpg';
    }
    return U('edu/Public/showImage',array('attach_id'=>$attach_id,'type'=> 'cut_140_202'));
}

function showImageSrc($attach_id,$type='cut_120_120'){
    if(empty($attach_id)){
        return THEME_PUBLIC_URL.'/image/avatar_user.jpg';
    }
    return U('edu/Public/showImage',array('attach_id'=>$attach_id,'type'=> $type));
}

/**
 * 验证权限 P 的别名
 * @param $action
 */
function P($action){
    return true;
}

//匹配失败，返回对应工号
function checkLoginUname($uname,$login){
    //验证用户名 和工号
    $map['login'] = $login;
    $info = D('login_uname')->where($map)->find();
    if(empty($info)){
        return '1';
    }
    return trim($info['uname']) == trim($uname)?'1':$info['uname'];
}