<?php
/*
 * qimingdao App基类
 * @version ST1.0
 */

include('Log.class.php');

class App
{
    /**
     * App初始化
     * @access public
     * @return void
     */
	static public function init() {

        if(@$_GET['debug']){
            $GLOBALS['debug']['mem_run_start'] = memory_get_usage();
            $GLOBALS['debug']['time_run_start'] =  microtime(TRUE);
        }
        // 设定错误和异常处理
        set_error_handler(array('App','appError'));
        set_exception_handler(array('App','appException'));
		// 读取站点配置. 时区、语言包、应用列表、插件列表等等   
        header("Content-Type:text/html;charset=utf-8");
	    date_default_timezone_set(C('DEFAULT_TIMEZONE'));
        
        
        if(!defined('RUNTIME_MODEL')){
            App::build();
        }
        
    }

    /**
     * 运行控制器
     * @access public
     * @return void
     */
    static public function run() {
		App::init();
        App::execApp();
        App::printDebug();
        static_cache(null,null,true);
        return ;
    }

    /**
     * 执行App控制器
     * @access public
     * @return void
     */
	static public function execApp() {


        
        //创建Action控制器实例
		$className =  MODULE_NAME.'Action';
		qmload(APP_ACTION_PATH.'/'.$className.'.class.php');

		if(!class_exists($className)) {
          
			$className	=	'EmptyAction';
            qmload(APP_ACTION_PATH.'/EmptyAction.class.php');
            if(!class_exists($className)){
              
                throw_exception( L('_MODULE_NOT_EXIST_').MODULE_NAME );
            }
		}

        
        $module =   new $className();
		//异常处理
		if(!$module) {
            // 模块不存在 抛出异常
			throw_exception( L('_MODULE_NOT_EXIST_').MODULE_NAME );
        }

        //获取当前操作名
        $action	=	ACTION_NAME;

        //执行当前操作
		call_user_func(array(&$module,$action));

		return ;
    }

    /**
     * 输出调试信息
     * @return [type] [description]
     */
    static public function printDebug(){
        if(isset($_GET['debug'])){
            $GLOBALS['debug']['mem_run_end']  = memory_get_usage();
            $GLOBALS['debug']['time_run_end'] = microtime(TRUE);
            //数据库查询信息
            echo '<pre>';
            print_r(Log::$log);
            //缓存使用情况
            print_r(Cache::$log);
            echo '</pre><hr>';
            echo ' Memories: '."<br/>";
            echo 'ToTal: ',number_format(($GLOBALS['debug']['mem_run_end'] - $GLOBALS['debug']['mem_include_start']) /1024),'k',"<br/>";
            echo 'Include:',number_format(($GLOBALS['debug']['mem_run_start'] - $GLOBALS['debug']['mem_include_start']) /1024),'k',"<br/>";
            echo 'Run:',number_format(($GLOBALS['debug']['mem_run_end'] - $GLOBALS['debug']['mem_run_start'])/1024),'k<br/><hr/>';
            echo 'Time:<br/>'; 
            echo 'ToTal: ',$GLOBALS['debug']['time_run_end'] - $GLOBALS['debug']['time_include_start'],"s<br/>";
            echo 'Include:',$GLOBALS['debug']['time_run_start'] - $GLOBALS['debug']['time_include_start'],'s',"<br/>";
            echo 'Run:',$GLOBALS['debug']['time_run_end'] - $GLOBALS['debug']['time_run_start'],'s<br/><br/>';
            $files = get_included_files();
            dump($files);
        }
    }

    /**
     * 执行Widget控制器
     * @access public
     * @return void
     */
    static public function execWidget() {

        
        if(file_exists(ADDON_PATH.'/widget/'.MODULE_NAME.'Widget/'.MODULE_NAME.'Widget.class.php')){
            qmload(ADDON_PATH.'/widget/'.MODULE_NAME.'Widget/'.MODULE_NAME.'Widget.class.php');
        }else{

            if(file_exists(APP_PATH.'/Widget/'.MODULE_NAME.'Widget/'.MODULE_NAME.'Widget.class.php')){
                qmload(APP_PATH.'/Widget/'.MODULE_NAME.'Widget/'.MODULE_NAME.'Widget.class.php');
            }
        }


        $className = MODULE_NAME.'Widget';

		$module	=	new $className();
      
		//异常处理
		if(!$module) {
            // 模块不存在 抛出异常
			throw_exception( L('_MODULE_NOT_EXIST_').MODULE_NAME );
        }

        //获取当前操作名
        $action	=	ACTION_NAME;

        //执行当前操作
		if($rs = call_user_func(array(&$module,$action))){
			echo $rs;
		}
        return ;
    }

    /**
     * app异常处理
     * @access public
     * @return void
     */
    static public function appException($e) {
        if(C('LOG_RECORD'))  Log::write($e->__toString(),Log::ERR);
    }

    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline)
    {
      switch ($errno) {
          case E_ERROR:
          case E_USER_ERROR:
            $errorStr = "[$errno] $errstr ".basename($errfile)." line :".$errline;
            if(C('LOG_RECORD')) Log::write($errorStr,Log::ERR);
            break;
          case E_STRICT:
          case E_USER_WARNING:
          case E_USER_NOTICE:
          default:
            $errorStr = "[$errno] $errstr ".basename($errfile)." line :".$errline;
            if(C('LOG_RECORD'))  Log::write($errorStr,Log::NOTICE);
            break;
      }
    }

//[RUNTIME]
/**
 +----------------------------------------------------------
 * 读取配置信息 编译项目
 +----------------------------------------------------------
 * @access private
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
static private function build()
{   
    // 核心文件列表
    // 
    $coreFileList = include(CONF_PATH.'/coreFileList.php');

    $runtime = $content = '';    
    foreach($coreFileList as $v){
       $runtime .= compile($v,true);
    }
    file_put_contents(CONF_PATH.'/'.CORE_MODE.'Runtime.php',strip_whitespace('<?php '.$runtime));
    return true;
}
//[/RUNTIME]
};//类定义结束