<?php
    class DB{
        private $DBConfig = ['database'=>'kmc_genzon_prd','localhost'=>'mysql','port'=>3306,'user'=>'root','pwd'=>'123456','charset'=>'utf8mb4'];
        // 返回或者影响记录数
        protected $numRows        = 0;
        // 返回字段数
        protected $numCols          = 0;
        protected $linkID            =   null;
        // 当前查询ID
        protected $queryID          = null;
        protected $connected       = false;

        public function __construct($config = []){
            is_array($config) && $this->DBConfig = array_merge($this->DBConfig,$config);
        }

        public function connect(){
            if( !$this->connected) {
                $this->linkID = mysqli_connect( $this->DBConfig['host'],  $this->DBConfig['user'], $this->DBConfig['pwd'],
                $this->DBConfig['database'], $this->DBConfig['port']) or $this->error('MYSQL 链接失败');

                $dbVersion = mysqli_get_server_info($this->linkID);
                if ($dbVersion >= "4.1") {
                    //使用UTF8存取数据库 需要mysql 4.1.0以上支持
                    mysqli_query($this->linkID,"SET NAMES '".$this->DBConfig['charset']."'");
                }
                //设置 sql_model
                if($dbVersion >'5.0.1'){
                    mysqli_query($this->linkID,"SET sql_mode=''");
                    mysqli_query($this->linkID,"SET time_zone = '+8:00'");
                }
                $this->connected = true;
            }
        }

        public function error($info = ''){
            $error = mysqli_error($this->linkID);
            echo '<div>MYSQL ERROR: ',$info,'<br/><pre>';
            var_export($error);
            echo '</pre>';
            die(); 
        }

          /**
         +----------------------------------------------------------
         * 执行查询 主要针对 SELECT, SHOW 等指令
         * 返回数据集
         +----------------------------------------------------------
         * @access protected
         +----------------------------------------------------------
         * @param string $str  sql指令
         +----------------------------------------------------------
         * @return mixed
         +----------------------------------------------------------
         * @throws ThinkExecption
         +----------------------------------------------------------
         */
        public function query($sql='') {
            $this->connect();
            if ( !$this->linkID ) return false;
            //释放前次的查询结果
            if ( $this->queryID ) {    $this->free();    }
            
            $this->queryID = mysqli_query( $this->linkID , $sql);
            
            if ( !$this->queryID ) {
                $this->error();
                return false;
            } else {
                $this->numRows = mysqli_num_rows($this->queryID);
                return $this->getAll();
            }
        }

        /**
         +----------------------------------------------------------
         * 获得所有的查询数据
         +----------------------------------------------------------
         * @access public
         +----------------------------------------------------------
         * @return array
         +----------------------------------------------------------
         * @throws ThinkExecption
         +----------------------------------------------------------
         */
        public function getAll() {
            if ( !$this->queryID ) {
                $this->error();
                return false;
            }
            //返回数据集
            $result = array();
            if($this->numRows >0) {
                while($row = mysqli_fetch_assoc($this->queryID)){
                    $result[]   =   $row;
                }
                mysqli_data_seek($this->queryID,0);
            }
            return $result;
        }
        
         /**
         +----------------------------------------------------------
         * 释放查询结果
         +----------------------------------------------------------
         * @access public
         +----------------------------------------------------------
         */
        public function free() {
            mysqli_free_result($this->queryID);
            $this->queryID = 0;
        }

        public function close() {
            if (!empty($this->queryID))
                mysqli_free_result($this->queryID);
            if ($this->linkID && !mysqli_close($this->linkID)){
                $this->error('MYSQL 关闭失败');
            }
            $this->linkID = 0;
        }
        public function __destruct()
        {
            // 关闭连接
            $this->close();
        }
    }
    
    class DBA{
        public $db = null;
        public function init($dbConfig=[]){
            $this->db = new DB($dbConfig);
        }
        //导出数据结构
        public function exportDataStruct($db_name,$tables = [],$outType='word'){
            $sql = "select TABLE_NAME,ENGINE,TABLE_COLLATION,TABLE_COMMENT from information_schema.`TABLES` where TABLE_SCHEMA = '".$db_name."'";
            if(!empty($tables)){
                $sql .= " AND table_name in ('".implode("','",$tables)."')";
            }
            $table_list = $this->db->query($sql);
            $table_hash = [];
            foreach($table_list as $v){
                $table_hash[$v['TABLE_NAME']] = ['name'=>$v['TABLE_NAME'],'collation'=>$v['TABLE_COLLATION'],'alias'=>$v['TABLE_COMMENT']];
            }
            $sql = "select TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,COLUMN_COMMENT from information_schema.columns where TABLE_SCHEMA = '".$db_name."'";
            if(!empty($tables)){
                $sql .= " AND table_name in ('".implode("','",$tables)."')";
            }
            $struct_list = $this->db->query($sql);
            foreach($struct_list  as $k=>$v){
                $table_hash[$v['TABLE_NAME']]['cols'][] = ['name'=>$v['COLUMN_NAME'],'type'=>$v['COLUMN_TYPE'],'comment'=>$v['COLUMN_COMMENT']];
            }
            if( $outType == 'word'){
                $this->_exportWord($table_hash);
            }else{
                //EXCEL
            }
            
        }

        public function _exportWord($data){
            $html = '<div style="width:100%;text-align:center"><h1>数据字典</h1></div>';
            $border_cls = "style='border:1px solid #ccc;text-align:center;padding:2px'";
            foreach($data as $tables){
                $html .="<div style='width:100%'><h2>{$tables['alias']} {$tables['name']} <h2><table style='width:100%;border:1px solid #ccc' cellspacing='0' cellpadding='0'>";
                $html .="<tr ><th {$border_cls} width='30%'>字段名</th><th {$border_cls} width='20%'>字段类型</th><th {$border_cls}>字段说明</th></tr>";
                foreach( $tables['cols'] as $col){
                    $html .="<tr><td {$border_cls}>".$col['name']."</td><td {$border_cls}>".$col['type']."</td><td {$border_cls}>".$col['comment']."</td></tr>";
                }
                $html.="</table></div>";
            }
            header("Cache-Control: public");
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            if (strpos($_SERVER["HTTP_USER_AGENT"],'MSIE')) {
                header('Content-Disposition: attachment; filename=数据字典.doc');
            }else if (strpos($_SERVER["HTTP_USER_AGENT"],'Firefox')) {
                Header('Content-Disposition: attachment; filename=数据字典.doc');
            } else {
                header('Content-Disposition: attachment; filename=数据字典.doc');
            }
            header("Pragma:no-cache");
            header("Expires:0");
            echo $html;
        }
    }
    
    $dba = new DBA();
    $dba->init();
    $dba->exportDataStruct('kmc_genzon_prd');