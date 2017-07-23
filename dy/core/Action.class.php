<?php
/*
 * qimingdao Action控制器基类
 * @version 1.0
 */
abstract class Action
{//类定义开始

    // 当前Action名称
    private     $name =  '';

    protected   $tVar =  array();
    protected   $trace = array();
    protected   $templateFile = '';

    protected   $user = array();   //当前登录者在教务平台的用户信息
    protected   $student = array();//当前登录者在学员平台的用户信息
    protected   $isAdmin = 0; //是否为管理员
    protected   $login = '';//登录的工号
    

    /**
     * 架构函数 取得模板对
     * @access public
     */
    public function __construct() {


        $this->initUser();

        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

   

    /**
     * 用户信息初始化
     * @access private
     * @return void
     */
    private function initUser() {
        
        if( model('Passport')->needLogin() ) {
            //去代理地址 让代理验证登录
           U('index/Index/login','',true);exit();
        }
        
        //当前登录者uid
        $GLOBALS['qm']['login'] = $this->login = isset($_SESSION['login']) ? $_SESSION['login'] : '';
        if( !empty( $this->login ) ){
            $this->user = model('User')->getDetailByLogin($this->login);
        }
        
        $this->assign('user', $this->user); //当前登录者在教务平台的用户信息
        $this->assign('login',$this->login);
    }

    /**
     * 魔术方法 有不存在的操作的时候
     * @access public
     * @param string $method 方法名
     * @param array $parms
     * @return mix
     */
    public function __call($method,$parms) {
        if( 0 === strcasecmp($method,ACTION_NAME)) {
            // 检查扩展操作方法
            $_action = C('_actions_');
            if($_action) {
                // 'module:action'=>'callback'
                if(isset($_action[MODULE_NAME.':'.ACTION_NAME])) {
                    $action  =  $_action[MODULE_NAME.':'.ACTION_NAME];
                }elseif(isset($_action[ACTION_NAME])){
                    // 'action'=>'callback'
                    $action  =  $_action[ACTION_NAME];
                }
                if(!empty($action)) {
                    call_user_func($action);
                    return ;
                }
            }
            // 如果定义了_empty操作 则调用
            if(method_exists($this,'_empty')) {
                $this->_empty($method,$parms);
            }else {
                // 检查是否存在默认模版 如果有直接输出模版
                    $this->display();
            }
        }elseif(in_array(strtolower($method),array('ispost','isget','ishead','isdelete','isput'))){
            return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
        }else{
            throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
        }
    }


    /**
     * 模板Title，keyword
     * @access public
     * @param mixed $input 要
     * @return
     */
    public function setTitle($title = '') {
        $this->assign('_title',$title);
    }

    /**
     * 模板变量赋
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的
     * @return voi
     */
    public function assign($name,$value='') {
        if(is_array($name)) {
            $this->tVar   =  array_merge($this->tVar,$name);
        }elseif(is_object($name)){
            foreach($name as $key =>$val)
                $this->tVar[$key] = $val;
        }else {
            $this->tVar[$name] = $value;
        }
    }

    /**
     * 魔术方法：注册模版变量
     * @access protected
     * @param string $name 模版变量
     * @param mix $value 变量值
     * @return mixed
     */
    public function __set($name,$value) {
        $this->assign($name,$value);
    }

    /**
     * 取得模板显示变量的值
     * @access protected
     * @param string $name 模板显示变量
     * @return mixed
     */
    protected function get($name) {
        if(isset($this->tVar[$name]))
            return $this->tVar[$name];
        else
            return false;
    }

    /**
     * Trace变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    protected function trace($name,$value='') {
        if(is_array($name))
            $this->trace   =  array_merge($this->trace,$name);
        else
            $this->trace[$name] = $value;
    }

    /**
     * 模板显示
     * 调用内置的模板引擎显示方法
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类
     * @return voi
     */
    protected function display($templateFile='',$charset='utf-8',$contentType='text/html') {
        echo $this->fetch($templateFile,$charset,$contentType,true);
    }

    /**
     *  获取输出页面内容
     * 调用内置的模板引擎fetch方法
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类
     * @return strin
     */
    protected function fetch($templateFile='',$charset='utf-8',$contentType='text/html',$display=false) {
        //应用下样式
        return fetch($templateFile,$this->tVar,$charset,$contentType,$display);
    }

    /**
     * 操作错误跳转的快捷方
     * @access protected
     * @param string $message 错误信息
     * @param Boolean $ajax 是否为Ajax方
     * @return voi
     */
    protected function error($message,$ajax=false) {
        $this->_dispatch_jump($message,0,$ajax);
    }

    protected function page404($message){
       $this->assign('message',$message);
       $this->display(THEME_PATH.'/page404.html');
    }
    /**
     * 操作成功跳转的快捷方
     * @access protected
     * @param string $message 提示信息
     * @param Boolean $ajax 是否为Ajax方
     * @return voi
     */
    protected function success($message,$ajax=false) {
        $this->_dispatch_jump($message,1,$ajax);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $info 提示信息
     * @param boolean $status 返回状态
     * @param String $status ajax返回类型 JSON XML
     * @return void
     */
    protected function ajaxReturn($data,$info='',$status=1,$type='JSON') {
        // 保证AJAX返回后也能保存日志
        if(C('LOG_RECORD')) Log::save();
        $result  =  array();
        $result['status']  =  $status;
        $result['info'] =  $info;
        $result['data'] = $data;
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        if(strtoupper($type)=='JSON') {
            // 返回JSON数据格式到客户端 包含状态信息
            header("Content-Type:text/html; charset=utf-8");
            exit(json_encode($result));
        }elseif(strtoupper($type)=='XML'){
            // 返回xml格式数据
            header("Content-Type:text/xml; charset=utf-8");
            exit(xml_encode($result));
        }elseif(strtoupper($type)=='EVAL'){
            // 返回可执行的js脚本
            header("Content-Type:text/html; charset=utf-8");
            exit($data);
        }else{
            // TODO 增加其它格式
        }
    }

    /**
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param integer $delay 延时跳转的时间 单位为秒
     * @param string $msg 跳转提示信息
     * @return void
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        if(C('LOG_RECORD')) Log::save();
        $url    =   U($url,$params);
        redirect($url,$delay,$msg);
    }

    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param Boolean $ajax 是否为Ajax方式
     * @access private
     * @return void
     */
    private function _dispatch_jump($message,$status=1,$ajax=false) {
        // 判断是否为AJAX返回
        if($ajax || $this->isAjax()) $this->ajaxReturn('',$message,$status);
        // 提示标题
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // 状态
        empty($message) && ($message = $status==1?L('PUBLIC_ADMIN_OPRETING_SUCCESS'):L('PUBLIC_ADMIN_OPRETING_ERROR'));
        $this->assign('message',$message);// 提示信息
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            // 成功操作后默认停留1秒
            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"2");
            // 默认操作成功自动返回操作前页面
            if(!$this->get('jumpUrl')) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
           
            $this->display(THEME_PATH.'/success.html');

        }else{
            //发生错误时候默认停留3秒
            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"5");
            // 默认发生错误的话自动返回上页
            if(!$this->get('jumpUrl')) $this->assign('jumpUrl',"javascript:history.back(-1);");

            $this->display(THEME_PATH.'/success.html');
            
        }
        if(C('LOG_RECORD')) Log::save();
        // 中止执行  避免出错后继续执行
        exit ;
    }

    /**
     * 是否AJAX请求
     * @access protected
     * @return bool
     */
    protected function isAjax() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
            if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
                return true;
        }
        if(!empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')]))
            // 判断Ajax方式提交
            return true;
        return false;
    }
};//类定义结束