<?php
    /**
     * 自定义session托管机制，将session保存到缓存中
     */
    class Session
    {   
        public static $instance = null;

        public static $maxlifetime = 3600;

        public static $handler;

        public static function getInstance(){
            if(!empty(self::$instance)){
              return self::$instance;
            }
            self::$instance = new self;
            self::getHandler();
            //设置自定义session
            session_set_save_handler(
                array(self::$instance, 'open'),
                array(self::$instance, 'close'),
                array(self::$instance, 'read'),
                array(self::$instance, 'write'),
                array(self::$instance, 'destroy'),
                array(self::$instance, 'gc')
            );

            register_shutdown_function('session_write_close');

            //最大时间
            self::$maxlifetime = C('SESSION_GC_MAXLIFETIME');

            if(!self::$maxlifetime){
                self::$maxlifetime = intval(ini_get("session.gc_maxlifetime"));
            }   

            session_start();
            session_name(C('SESSION_NAME'));

            return self::$instance;  
        }

        static function getHandler(){
            $path = ADDON_PATH.'/liberary/session/';
            $sessionClass = 'Session'.ucfirst(C('SESSION_HANDER'));
            qmload($path.$sessionClass.'.class.php');
            
            if(class_exists($sessionClass)){
               self::$handler =  new $sessionClass();
            }else{
                throw_exception(L('Session Hander not Found').':'.$sessionClass);
            }
        }
        public static function start(){
            self::getInstance();
        }

        public function open(){
            //开启
            return true;
        }

        public function close(){
            //TODO  关闭链接
            return true;
        }

        public function read($id){
            $data = self::$handler->get($id);
            !$data && $data = '';
            return $data;
        }

        public function write($id,$data){
            return self::$handler->set($id,$data,self::$maxlifetime);
        }

        public static function destroy($id){
            session_write_close();
            return self::$handler->rm($id);
        }

        public function gc(){
            return true;
        }
        
        //清空缓存
        public static function get($name){
          return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
        }

        public static function set($name,$value){
          if(null === $value){
               unset($_SESSION[$name]);
          }else{
               $_SESSION[$name] = $value;
          }
        }

        public static function clear(){
            $_SESSION = array();
        }
        //设置缓存时间
        public static function setExpire($gcMaxLifetime = null){
            $return = ini_get('session.gc_maxlifetime');
            if (isset($gcMaxLifetime) && is_int($gcMaxLifetime) && $gcMaxLifetime >= 1) {
                ini_set('session.gc_maxlifetime', $gcMaxLifetime);
                self::$maxlifetime = $gcMaxLifetime;
            }else{
                self::$maxlifetime = $return;
            }
            return self::$maxlifetime;
        }
    }
