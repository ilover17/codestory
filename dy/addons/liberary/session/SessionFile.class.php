<?php
//+----------------------------------------------------------------------
// | QimingDao
// +----------------------------------------------------------------------
// | Copyright (c) 2012 http://www.qimingcx.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Qiming ChuangXiang
// +----------------------------------------------------------------------

/**
 +------------------------------------------------------------------------------
 * 文件Session类
 +------------------------------------------------------------------------------
 * @author    yangjiasheng
 +------------------------------------------------------------------------------
 */
class SessionFile {

    private $baseDir = '';
    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
        $this->baseDir = C('SESSION_BASE_DIR');
        return $this;
    }

    /**
     +----------------------------------------------------------
     * 是否连接
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    private function isConnected() {
        return $this->connected;
    }

    /**
     +----------------------------------------------------------
     * 取得变量的存储文件名
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function filename($name) {
        $name   =   md5($name);
        // 使用子目录
        $dir   ='';
        for($i=0; $i < C('SESSION_PATH_LEVEL'); $i++) {
            $dir    .=  $name{$i}.'/';
        }
        if(!is_dir($this->baseDir.'/'.$dir)) {
            mkdir($this->baseDir.'/'.$dir,0777,true);
        }
        $filename = $name;
        return $this->baseDir.'/'.$dir.$filename;
    }

    /**
     +----------------------------------------------------------
     * 读取缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function get($name) {
        $filename   =   $this->filename($name);
        if (!is_file($filename)) {
           return false;
        }

        $content    =   file_get_contents($filename);

        if( false !== $content) {
            $expire  =  (int)substr($content,0,12);

            if($expire != 0 && time() > filemtime($filename) + $expire) {
                //缓存过期删除缓存文件
                unlink($filename);
                return false;
            }
            $content   =  substr($content,12,strlen($content));
            return $content;
        }else {
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * 写入缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间 0为永久
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function set($name,$value,$expire=null) {
        if(is_null($expire) || empty($expire)) {  //
            $expire =  3600;//默认
        }
        $filename   =   $this->filename($name);
        $data    = sprintf('%012d',$expire).$value;
        $result  =   file_put_contents($filename,$data);
        chmod($filename,0777);
        if($result) {
            clearstatcache();
            return true;
        }else {
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * 删除缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function rm($name) {
        if(file_exists($this->filename($name))){
            return unlink($this->filename($name));
        }else{
            return false;   //文件不存在 by yangjs
        }
    }

}