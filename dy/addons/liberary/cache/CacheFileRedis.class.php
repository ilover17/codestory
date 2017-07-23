<?php
/**
 +------------------------------------------------------------------------------
 * 文件类型缓存类
 *
 +------------------------------------------------------------------------------
 * @author    yangjiasheng
 +------------------------------------------------------------------------------
 */
class CacheFileRedis extends Cache{

    /**
     +----------------------------------------------------------
     * 缓存存储前缀
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $prefix  ='~@';
    public $connected = false;
    public $selected  = 0;
    public $expire    = 0;  //默认失效时间
    public $temp      = '';

    public $options   = array();

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
        $this->select();
        if (!is_dir($this->temp)) {
            if (!mkdir($this->temp,0777,true)){
                   return false;
            }
            chmod($this->temp, 0777);
        }
        $this->connected = is_dir($this->temp) && is_writeable($this->temp);
    }

    public function select($selected = 0){
        $this->selected        = $selected;
        $this->temp            = C('DATA_CACHE_PATH').'/fileredis/'.$this->selected;
    }

    public  function connect(){
        return  $this->connected;
    }

    public function persist(){
        return true;
    }
    public function auth(){
        return true;
    }

    public function hSet($hash,$key,$value){
        return $this->set($hash.$key,$value);
    }

    public function hGet($hash,$key){
        return $this->get($hash.$key);
    }

    public function hGetAll($hash){
        return '';
    }

    public function hDel($hash,$key){
        return $this->del($hash.$key);
    }

    public function hMset($hash,$fields){
        foreach($fields as $k=>$v){
            $this->hSet($hash,$k,$v);
        }
        return true;
    }

    public function hmGet($key,$ids){
        $data = array();
        foreach($ids as $id){
            $data[$id] = $this->hGet($key,$id);
        }
        return $data;
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
        $this->log('get:'.$name,'cache');

        if (!$this->connect() || !is_file($filename)) {
           return false;
        }
        $content    =   file_get_contents($filename);

        if( false !== $content) {
            $expire  =  (int)substr($content,8, 12);
            if($expire != 0 && time() > filemtime($filename) + $expire) {
                //缓存过期删除缓存文件
                unlink($filename);
                return false;
            }

            $content   =  substr($content,20, -3);
            if( function_exists('gzuncompress') ) {
                //启用数据压缩
                $content   =   gzuncompress($content);
            }
            $content    =   unserialize($content);
            return $content;
        }
        else {

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
        if(is_null($expire)) {  //
            $expire =  $this->expire;
        }
        $filename   =   $this->filename($name);
        $this->log('set:'.$name,'cache');
        $data   =   serialize($value);
        if( function_exists('gzcompress')) {
            //数据压缩
            $data   =   gzcompress($data,3);
        }
        //开启数据校验
        $data    = "<?php\n//".sprintf('%012d',$expire).$data."\n?>";
        $result  =   file_put_contents($filename,$data);
        if($result) {
            clearstatcache();
            return true;
        }else {
            return false;
        }
    }

    public function setex($name,$expire=0,$value=''){
        return $this->set($name,$value,$expire);
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
    public function del($name) {
        $file = $this->filename($name);
        if(file_exists($file)){
            $this->log('del:'.$file,'cache');
            return unlink($file);
        }else{
            return false;   //文件不存在 by yangjs
        }
    }

    /**
     +----------------------------------------------------------
     * 清除缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function clear($path = '') {

        empty($path) && $path   =  $this->temp;

        if (!file_exists($path)) {
            return false;
        }

        if (is_file($path) || is_link($path)) {
            return unlink($path);
        }

        $dir = dir($path);
        if($dir){
            while (false !== $entry = $dir->read()) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $this->clear($path . DS . $entry);
            }
        }
        $dir->close();
        return @rmdir($path);
    }

    public function lPush($key,$value){
        return $this->set($key,$value);
    }

    public function flushDB(){
        return $this->clear();
    }

    public function log($msg){
        //Log::record($msg,'CACHE');
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
        if(C('DATA_CACHE_SUBDIR')) {
            // 使用子目录
            $dir   ='';
            for($i=0;$i<C('DATA_PATH_LEVEL');$i++) {
                $dir    .=  $name{$i}.'/';
            }
            if(!is_dir($this->temp.$dir)) {
                mkdir($this->temp.$dir,0777,true);
            }
            $filename   =   $dir.$this->prefix.$name.'.php';
        }else{
            $filename   =   $this->prefix.$name.'.php';
        }

        return $this->temp.$filename;
    }
}
