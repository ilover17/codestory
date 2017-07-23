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
 * 缓存模型
 +------------------------------------------------------------------------------
 * 
 * @example  
 			setType($type)  				   主动设置缓存类型
		    set($key,$value,$expire=null)   设置缓存key=>value，expire表示有效时间,null表示 永远
		    get($key,$mutex=false)		   获取缓存数据,支持mutex模式
		    getList($prefix,$key)		   批量获取指定前缀下的多个key值的缓存 
		    rm($key) 					   删除缓存                           
 * @author    jason <yangjs17@yeah.net> 
 * @version   1.0
 +------------------------------------------------------------------------------
 */

qmload(CORE_LIB_PATH.'/Cache.class.php');
class CacheModel {

	/**
	 * 缓存的静态变量
	 * 
	 * @var unknown_type
	 */
  	//public static $_cacheHash = array();	
  	
  	
	/**
     +----------------------------------------------------------
	 * 操作句柄
     +----------------------------------------------------------
	 * @var string
	 * @access protected
     +----------------------------------------------------------
	 */
	public $handler;
	
	public $type='FILE';
	
	private static $cacheLock = 5;
  	private static $cacheCheckTime = 10;
  	public static $cacheLockHash = array();	//锁的HASH阵列
	
	public function __construct($type='') {		
		
		$type = empty($type) ? C('DATA_CACHE_TYPE') : $type;
		!$type && $type = $this->type;
		$this->type = strtoupper($type);
		$this->handler = Cache::getInstance($type);
	}
	/**
	 * 链式设置缓存类型
	 * 
	 * @param unknown_type $type
	 * @return obj
	 */
	public function setType($type){
		
		$this->type = strtoupper($type);
		$this->handler = Cache::getInstance($type);
		return $this;
	}

	/**
	 * 设置缓存
	 * 
	 * @param unknown_type $key
	 * @param unknown_type $value
	 * @param int $expire 过期时间
	 * @return boolean
	 */
	public function set($key,$value,$expire = null){
		//接管过期时间设置 -1 表示永远不过期
		$value = array('CacheData'=>$value,'CacheMtime'=>time(),'CacheExpire'=>is_null($expire)?'-1':$expire);	
		$key = C('DATA_CACHE_PREFIX').$key;
		return $this->handler->set($key,$value);
	}
	
	/**
	 * mutex get 设置 ,支持mutex模式
	 * 
	 * $mutex 使用注意
	 * 1.set的时候设置有效时间
	 * 2.get返回false的时候需要主动创建缓存
	 * 
	 * @param unknown_type $key
	 * @param unknown_type $mutex 如果未取到数据,是否会主动创建缓存
	 */
	public function get($_key,$mutex=false){
		$key  = C('DATA_CACHE_PREFIX').$_key;
		
		$data = $this->handler->get($key);
		//未设置过缓存 
		if(!$data){ return false;}

		//不需要mutex模式 
		if(!$mutex){ 
			if($data['CacheExpire']<0 || ($data['CacheMtime'] + $data['CacheExpire'] > time())){
				return $this->returnData($data['CacheData'],$key);
			}else{
				//过期了 清理原始缓存
				$this->rm($_key);	
				return false;
			}
		}
		
		//需要mutex模式
		if( ($data['CacheMtime'] + $data['CacheExpire']) <= time()){
			//正常情况 --有过期时间设置的mutex模式  用的比较多
			if($data['CacheExpire'] > 0){	
				$data['CacheMtime'] = time();
				$this->handler->set($key,$data);
				return false;	
			}else{				
				//异常情况  -- 没有设置有效期的时候,永久有效的时候
				if(!$data['CacheData']){
					$this->rm($_key);
					return false;
				}
				return $this->returnData($data['CacheData'],$key);
			}
		}else{
			return $this->returnData($data['CacheData'],$key);
		}
	}
	
	/**
	 * 删除缓存
	 * 
	 * @param string key 换成key值
	 * @return boolean 
	 */
	public function rm($_key){
		$key  = C('DATA_CACHE_PREFIX').$_key;
		static_cache($key,false);
		return $this->handler->rm($key);
	}

	
	/**
	 * 缓存写入次数
	 * 
	 */
	public function W(){
		return $this->handler->W();
	}
	
	/**
	 * 缓存读取次数
	 * 
	 */
	public function Q(){
		return $this->handler->Q();
	}

	/**
	 * 根据某个前缀 批量获取多个缓存
	 * 
	 * @param unknown_type $prefix
	 * @param unknown_type $key
	 */
	public function getList($prefix , $key ){
		if($this->type == 'MEMCACHE'){	//memcache 有批量获取缓存的接口
			$prefix = C('DATA_CACHE_PREFIX').$prefix;
			$_data = $this->handler->getMulti( $prefix , $key );
			foreach($_data as $k=>$d){
				$data[$k] = $this->returnData($d['CacheData'],$key);
			}
		}else{
			foreach($key as $k){
				$_k = $prefix.$k;
				$data[$k] = $this->get($_k);
			}

		}
		return $data;
	}
	

	
	/**
	 * 主动锁(时间锁)模式(根据设定的锁定时间进行更新) **不要滥用**
	 * @param unknown_type $key 缓存键
	 * @param unknown_type $data 缓存数据
	 * @param unknown_type $ttl	锁有效时间
	 * @param unknown_type $lockListKey 锁所在的阵列,建议每个app/或者每一种类型 使用自己的阵列标志
	 * 注:使用阵列的原因,可以手动清除缓存
	 */
	public function setTimeData($key,$data,$ttl=60,$lockListKey='TimeKey'){
		
		if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
			self::$cacheLockHash[$lockListKey] = $this->get($lockListKey); 
		}
		self::$cacheLockHash[$lockListKey][$key] = array('setTime'=>time(),'lifeTime'=>$ttl);
		$this->handler->set($lockListKey,self::$cacheLockHash[$lockListKey]);  	
	  	return $this->handler->set( $key , $data );
		
	}
	/**
	 * 获取锁的数据
	 * @param  [type] $key         [description]
	 * @param  string $lockListKey [description]
	 * @return [type]              [description]
	 */
	public function getTimeData($key,$lockListKey='TimeKey'){
		if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
			self::$cacheLockHash[$lockListKey] = $this->handler->get($lockListKey); 
		}
		
		if(!self::$cacheLockHash[$lockListKey][$key]){	//还没有设置过此锁
			return false;
		}
		if(( self::$cacheLockHash[$lockListKey][$key]['setTime'] + self::$cacheLockHash[$lockListKey][$key]['lifeTime'] ) <= time()){
			//过期了,重新设置setTime,并返回false
			self::$cacheLockHash[$lockListKey][$key]['setTime'] = time();
			$this->handler->set($lockListKey,self::$cacheLockHash[$lockListKey]);
			return false;	//返回false 让程序去主动更新缓存
		}
		
		return $this->handler->get( $key );
	
	}
	/**
	 * 主动删除时间锁 (设置过期)
	 * @param  string $key         [description]
	 * @param  string $lockListKey [description]
	 * @return [type]              [description]
	 */
	public function deleteTimeData($key='',$lockListKey='TimeKey'){
		if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
			self::$cacheLockHash[$lockListKey] = $this->get($lockListKey); 
		}
		if(empty($key)){
			foreach(self::$cacheLockHash[$lockListKey] as $k=>$v){
				self::$cacheLockHash[$lockListKey][$k]['setTime'] = time()- self::$cacheLockHash[$lockListKey][$k]['lifeTime']; 									
			}
		}else{
			if(!self::$cacheLockHash[$lockListKey][$key]){	//还没有设置过此锁
				return false;
			}
			self::$cacheLockHash[$lockListKey][$key]['setTime'] = time()- self::$cacheLockHash[$lockListKey][$key]['lifeTime'];
		}
		return $this->handler->set($lockListKey,self::$cacheLockHash[$lockListKey]); 			
		
	}
	
	/**
	 * <被动锁模式,需要程序主动去锁定数据>
	 * @param unknown_type $key 缓存的key值
	 * @param unknown_type $lock 锁定or解锁
	 * @param unknown_type $lockListKey 所在的lockKey列表key值  默认为LockKey 可以按应用自行划分
	 */
	public function lockData($key='',$lock=1,$lockListKey='LockKey'){
		if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
			self::$cacheLockHash[$lockListKey] = $this->get($lockListKey); 
		}
		if(empty($key)){//批量操作
			foreach(self::$cacheLockHash[$lockListKey] as $k=>$v){
				self::$cacheLockHash[$lockListKey][$k] = $lock;
			}
		}else{
			self::$cacheLockHash[$lockListKey][$key] = $lock;	
		}
		return $this->set($lockListKey,self::$cacheLockHash[$lockListKey]);

	}
	
	/**
	 * 获取被动锁锁定的数据
	 * @param unknown_type $key
	 * @param unknown_type $lockListKey
	 */
	public function getLockData($key,$lockListKey='LockKey'){
		
		if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
			self::$cacheLockHash[$lockListKey] = $this->get($lockListKey);	//只有锁定过才有效
		}
		if( self::$cacheLockHash[$lockListKey][$key] == 1 ){ //如果锁定 则解锁
			$this->lockData($key,0,$lockListKey);
			return false;
		}
		if( !isset(self::$cacheLockHash[$lockListKey][$key]) ){
			$this->lockData($key,0,$lockListKey);
		}
		return  $this->get($key);
	} 
	/**
	 * 批量获取被动锁数据 目前只支持Memcache
	 * @param unknown_type $prefix
	 * @param unknown_type $key
	 * @return unknown
	 */
	public function  getLockCacheList($prefix , $key , $lockListKey='LockKey'){
		if(!isset(self::$cacheLockHash[$lockListKey]) || empty(self::$cacheLockHash[$lockListKey])){
			self::$cacheLockHash[$lockListKey] = $this->get($lockListKey);
		}
		$data = $this->getList( $prefix , $key );
		foreach($data as $k=>$v){
			$trueKey = $prefix.$k;
			if( self::$cacheLockHash[$lockListKey][$trueKey] == 1 ){ //如果锁定 则解锁
				$this->lockData($trueKey,0,$lockListKey);
				$data[$k] = false;
			}
		}
		return $data;
	}	

	/**
	 * 返回数据 
	 * 
	 */
	private function returnData($data,$key){
		//TODO 可以在此对空值进行处理判断
		static_cache('cache_'.$key,$data);
		return $data;
	}
}
