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
 * MemcacheSession类
 +------------------------------------------------------------------------------
 * @author    yangjiasheng
 +------------------------------------------------------------------------------
 */
class SessionMemcache{

    private $handler;
    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    function __construct($options='') {
        if ( !extension_loaded('memcache') ) {
            throw_exception('不支持memcache，请安装扩展');
        }
        
        $options = array (
            'host'  => C('MEMCACHE_HOST') ? C('MEMCACHE_HOST') : '127.0.0.1:11211',
            'persistent' => true,
            'expire'   =>C('DATA_CACHE_TIME')? C('DATA_CACHE_TIME') : null, //默认缓存有效时间
            'length'   =>0,
        );
        $this->options =  $options;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new Memcache;
        
        $hosts = explode(',', $options['host']);

        foreach($hosts as $v){
           list($host,$post) = explode(':', $v);
           $this->handler->addServer($host,$post);
        }

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
        return $this->handler->get($name);
    }
    
   

    /**
     +----------------------------------------------------------
     * 写入缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function set($name, $value, $expire = null) {
        
        if(is_null($expire)) {
            $expire  =  3600;
        }
        $expire = $expire < 0 || !is_numeric($expire) ? null : $expire;

        if($this->handler->set($name, $value, 0, $expire)) {
            return true;
        }
        return false;
    }

    /**
     +----------------------------------------------------------
     * 删除缓存
     *
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function rm($name) {
        return $this->handler->delete($name);
    }
    
  
}