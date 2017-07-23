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
 * 空间用户模型
 +------------------------------------------------------------------------------
 *                        
 * @author    jason <yangjs17@yeah.net> 
 * @version   1.0
 +------------------------------------------------------------------------------
 */
class UserModel extends Model { 

    
    public function getDetailByLogin($login){
        $map['login'] = t($login);
        return $this->where($map)->find();
    }

    public function checkLogin($login,$password){
    	$user = $this->getDetailByLogin($login);
        if($user['is_del'] == 1){
            return false;
        }
        return true;
        if( $this->encryptPassword($password,$user['salt']) == $user['pass'] ){
            return true;
        }
        return false;
    }

    public function addUser($login,$password,$uname,$isAdmin=0){
        $add['salt'] = rand(10000,99999);
        $add['pass'] = $this->encryptPassword($password,$add['salt']);
        $add['login'] = $login;
        $add['is_admin'] = $isAdmin;
        $add['uname'] = $uname;
        $add['is_del'] = 0;
        return $this->add($add);
    }

    public function encryptPassword($password, $salt='11111'){
        return md5(md5($password).$salt);
    }
}