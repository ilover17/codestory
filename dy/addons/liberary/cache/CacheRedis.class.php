<?php
/**
 +------------------------------------------------------------------------------
 * Memcache缓存类
 +------------------------------------------------------------------------------
 * @author    yangjiasheng
 +------------------------------------------------------------------------------
 */
class CacheRedis extends Cache {

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    function __construct($options='') {
        if ( !extension_loaded('redis') ) {
            throw_exception('can\'t load redis extension, perhaps redis extension not installed');
        }
        if(empty($options)) {
            $options = array (
                'host'  => C('redis_host') ? C('redis_host') : '127.0.0.1:6379',
                'port'  => 6379,
                'auth'  => C('redis_auth'),
                'redis_system_db'   =>C('redis_system_db')? C('redis_system_db') : 0
            );
        }

        $this->options =  $options;
        $this->handler  = new Redis();

        $this->handler->connect($this->options['host'], $this->options['port']);
        $this->handler->auth($this->options['auth']);
        $this->handler->select($this->options['redis_system_db']);
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

        $data = $this->handler->get($name);
        $data = unserialize($data);
        return $data;
    }
    
 	public function getMulti( $prefix , $key ){
        $this->N('cache_read',1,$prefix.implode(',',$key));
		foreach( $key as $k=>$v ){
			$namelist[] = $prefix.$v;
		}
		
		$result = $this->handler->mGet ( $namelist );
		
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
    public function set($name, $value) {
        $this->N('cache_write',1,$name);

        $value = serialize($value);
        if($this->handler->set($name, $value)) {
            if(isset($this->options['length']) && $this->options['length']>0) {
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
        return $this->handler->flushDB();
    }
}