<?php
/**
 +------------------------------------------------------------------------------
 * Memcache缓存类
 +------------------------------------------------------------------------------
 * @author    yangjiasheng
 +------------------------------------------------------------------------------
 */
class CacheMemcache extends Cache {

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    function __construct($options='') {
        if ( !extension_loaded('memcache') ) {
            throw_exception('不支持 memcache，请安装扩展');
        }
        if(empty($options)) {
            $options = array (
                'host'  => C('MEMCACHE_HOST') ? C('MEMCACHE_HOST') : '127.0.0.1:11211',
                'persistent' => true,
                'expire'   =>C('DATA_CACHE_TIME')? C('DATA_CACHE_TIME') : null,	//默认缓存有效时间
                'length'   =>0,
            );
        }
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
     * 是否连接
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    private function isConnected() {
        return $this->connected;
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
        $this->N('cache_read',1,$name);
        return $this->handler->get($name);
    }
    
	public function getMulti( $prefix , $key ){
        $this->N('cache_read',1,$prefix.implode(',',$key));
		foreach( $key as $k=>$v ){
			$namelist[] = $prefix.$v;
		}
		
		$result = $this->handler->get ( $namelist );
		
		foreach ( $result as $k=>$v){
			$k = str_replace( $prefix , '', $k );
			$data[ $k ] = $v;
		}
		unset( $result );
		return $data;
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
        $this->N('cache_write',1,$name);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $expire = $expire < 0 || !is_numeric($expire) ? null : $expire;

        if($this->handler->set($name, $value, 0, $expire)) {
            if($this->options['length']>0) {
                // 记录缓存队列
                $this->queue($name);
            }
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
    public function rm($name, $ttl = false) {
        return $ttl === false ?
            $this->handler->delete($name) :
            $this->handler->delete($name, $ttl);
    }
    
    /**
     +----------------------------------------------------------
     * 清除缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function clear() {
        return $this->handler->flush();
    }
}