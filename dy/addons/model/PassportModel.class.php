<?php
//+----------------------------------------------------------------------
// | QimingDao 
// +----------------------------------------------------------------------
// | Copyright (c) 2012 http://www.qimingcx.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: jason <yangjs17@yeah.net>
// +----------------------------------------------------------------------
// 

/**
 +------------------------------------------------------------------------------
 * 通行证模型
 +------------------------------------------------------------------------------
 * 
 * @example [path|url] description               
 * @todo    description                           
 * @author    jason <yangjs17@yeah.net> 
 * @version   1.0
 +------------------------------------------------------------------------------
 */
class PassportModel extends Modelite
{
	protected $error = null;

	/**
	 * 返回最后的错误信息
	 *
	 * @return string $this->error
	 */
	public function getError()
	{
		return $this->error;
	}
	
	/**
	 * 验证用户是否需要登录
	 *
	 * @return boolean 登陆成功是返回true, 否则返回false
	 */
	public function needLogin()
	{	
		$noNeedLogin = C('DEFAULT_NO_LOGIN');
		if(!is_array($noNeedLogin)){
			$noNeedLogin = array();			
		}
		// 验证本地系统登录
		if ($this->isLogged()){
			return false;
		} elseif ( !in_array(APP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME,$noNeedLogin)
				&& !in_array(APP_NAME.'/'.MODULE_NAME,$noNeedLogin)	
				&& !in_array(APP_NAME,$noNeedLogin)
				){
			//白名单之外 需要登录
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 验证用户是否已登录
	 *
	 * @return boolean 登陆成功是返回true, 否则返回false
	 */
	public function isLogged(){	

		$header = getallheaders();
		$login  = false;
		if($header['username']){
			$login = $header['username'];
		}else if( isset($_SESSION['login']) && !empty($_SESSION['login']) ){
			$login = $_SESSION['login'];
		}
	
		if($login){
			if($login != $_SESSION['login']){
				$_SESSION['login'] = $login;	
			}
			return true;
		}
		return false;
	}

	# 下面的几个方法 只是测试的时候用 上线了统一用单点登录就不需要这个了 #

	/**
	 * 使用本地帐号登陆 (密码为null时不参与验证)
	 *
	 * @param string         $email|$uname
	 * @param string|boolean $password
	 * @return boolean
	 */
	public function loginLocal($login, $password = null)
	{

		$login = t($login);
		if( empty($login) ){
			$this->error = '请输入登录账号';
			return false;
		}
		if(empty($password)){
			$this->error = '请输入登录密码';
			return false;	
		}

		if( model('User')->checkLogin($login,$password)){
			$_SESSION['login'] = $login;
			return true;
		}
		return false;
		
	}

	

	/**
	 * 注销本地登录
	 */
	public function logoutLocal()
	{
		// 注销session
		unset($_SESSION['login']);

		session_unset();
		session_destroy();
	}


}