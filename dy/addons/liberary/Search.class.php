<?php
/** 
+---------------------------------------------------------------------------------
| QimingDao
+---------------------------------------------------------------------------------
| Copyright (c) 2012 http://www.qimingcx.com All rights reserved.
+---------------------------------------------------------------------------------
| Author: jiangbingren@qimingcx.com
+---------------------------------------------------------------------------------
| 搜索的底层服务；
| 利用sphinx的mysql协议实现；
| 依赖文件：Db.class.php；
+---------------------------------------------------------------------------------
 */
class Search{
    private $host;
    private $port;
    private $sdb = null;
    private $keywords;
    private $error;
   
    public function __construct(){
        if( C('SEARCH_ENGINE') == 'sphinx'){
            $this->connect(); 
        }
    }
    

    public function getError(){
        return $this->error;
    }
    
    public function connect(){
        $connection = $connection   = array( 'dbms' => 'mysql', 'hostname' => ( C('SEARCHD_HOST')?C('SEARCHD_HOST'):$this->host ), 'hostport' => ( C('SEARCHD_PORT')?C('SEARCHD_PORT'):$this->port) );
        try{
            $this->sdb =  new Db($connection);
            $this->sdb->connect();
         }catch(Exception $e){
            //主动抓取异常 删除已经插入的数据
            Log::write($e->getMessage(),Log::EXECPTION);
            $this->error = 'Sphinx数据库连接失败';
            return false;
        }
        return $this;
    }

    //直接搜索sphinx，结果未处理
    public function query($query){
        if(!$this->sdb->connected){
            $this->error  = 'sphinx is not connected';
            return false;
        }
        $list = $this->sdb->query($query);
        return $list;
    }
    
    //执行搜索，结果有处理
    public function search($query,$limit=20){

        if(!$this->sdb->connected){
            $this->error  = 'sphinx is not connected';
            return false;
        }
        
        if(empty($query))   return false;
        
        //执行SphinxQL查询
        $query .=" order by int03 desc limit ".$this->getLimit($limit);

        $datas  =   $this->sdb->query($query);

        if(!$datas) return false;
        
        //获取关键词信息
        $metas  =   $this->sdb->query("SHOW META");
        if(!$metas) return false;
        
        foreach($metas as $v){
            if($v['Variable_name']=='total_found'){
                $data['count']  =   $v['Value'];
            }
            if($v['Variable_name']=='time'){
                $data['time']   =   $v['Value'];
            }
            if(is_numeric($k = str_replace(array('keyword','[',']'),'',$v['Variable_name']))){
                $data['matchwords'][$k]['keyword']  =   $v['Value'];
                $data['keywords'][] =   $v['Value'];
            }
            if(is_numeric($k = str_replace(array('docs','[',']'),'',$v['Variable_name']))){
                $data['matchwords'][$k]['docs'] =   $v['Value'];
            }
            if(is_numeric($k = str_replace(array('hits','[',']'),'',$v['Variable_name']))){
                $data['matchwords'][$k]['hits'] =   $v['Value'];
            }
        }

        //保存关键字 用来高亮
        $this->keywords = $data['keywords'];
        
        $p = new Page($data['count'],$limit);
        $data['totalPages'] = $p->totalPages;
        $data['html']       = $p->show();
        $data['data']       = $datas;
        return $data;
    }


    /**
     * 获取当前Search查询的关键词解析结果
     */
    public function getKeywords(){
        return $this->keywords;
    }

    public function getPage(){
        return !empty($_GET[C('VAR_PAGE')]) && ($_GET[C('VAR_PAGE')] >0) ? intval($_GET[C('VAR_PAGE')]):1;
    }

    public function getLimit($limit=20){
        $nowPage    =   $this->getPage();
        $now        =   intval(abs($nowPage-1)*$limit);
        return  $now.','.$limit;
    }

        //获取搜索摘要
    public function getSearchSummary($content, $words, $max_desc_len = 150 ,$highlight=true,$filterWords=true) {
        
        //处理内容
        $content    =   strip_tags(htmlspecialchars_decode(strip_tags($content)));

        //处理关键字
        if (is_array ( $words )) {
            $arrwords = $words;
        } else {
            $arrwords = explode ( ',', $words );
        }        

        //如果没有关键词,直接输出前几段
        if (count ( $arrwords ) == 0) {
            $output = $content;
            
        //有关键词，输出带关键词的段落
        } else {
            //将内容拆分成数组
            $content = str_ireplace ( '\n', '[]', $content );
            $content = str_ireplace ( '.', '.[]', $content );
            $content = str_ireplace ( '。', '。[]', $content );
            $content = str_ireplace ( '!', '![]', $content );
            $content = str_ireplace ( '！', '！[]', $content );
            $content = str_ireplace ( '?', '?[]', $content );
            $content = str_ireplace ( '？', '？[]', $content );
            $docs = explode ( '[]', $content );
            $docs = array_filter ( $docs, 'strlen' );
            //记录每个关键词的在每段出现的次数和每段的权重，按权重排序
            $poss = 0;
            $pos = true;
            foreach ( $docs as $key => $content ) {
                foreach ( $arrwords as $word ) {
                    while ( $pos ) {
                        $pos = mb_strpos ( $content, $word, $poss, 'utf8' );
                        if ($pos === false) {
                            break;
                        } else {
                            $pos_words ['pos'] = $pos; //记录所有的关键词出现的位置
                            $pos_words ['word'] = $word; //记录每个位置的关键词
                            $allpos [$key] ['words'] [] = $pos_words; //记录段落关键词位置
                            $allpos [$key] ['weight'] += 20;
                            
                            $poss = $pos + 1;
                            $pos = true;
                        }
                    }
                    $poss = 0;
                    $pos = true;
                }
                
                //if( intval($allpos [$key] ['weight']) > 0 )
                    $weight [$key] = intval ( $allpos [$key] ['weight'] );
            }

            foreach ( $weight as $k => $v ) {
                $summary [] = $docs [$k];
            }
            $output = array_slice ( $summary, 0, 3 );
        }
        $output = $this->getSearchShort ( implode ( ' ... ', $output ), $max_desc_len );
       
        //敏感词过滤
        if(function_exists('filterWord') && $filterWords && !$highlight)
            $output =   filterWord($output);

        //高亮
        if($highlight){
            $output =   $this->getSearchHighLight($output,$words,$filterWords);
        }

        return $output;
    }

        //截取字符
    public function getSearchShort($str, $length = 40,$type = false) {
        $str    =   strip_tags(htmlspecialchars_decode(strip_tags($str)));
        $strlenth = 0;
        $out = '';
        preg_match_all ( "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match );
        foreach ( $match [0] as $v ) {
            preg_match ( "/[\xe0-\xef][\x80-\xbf]{2}/", $v, $matchs );
            if (! empty ( $matchs [0] )) {
                $strlenth += 1;
            } elseif (is_numeric ( $v )) {
                $strlenth += 0.545;
            } else {
                $strlenth += 0.475;
            }
            
            if ($strlenth > $length) {
                break;
            }
            
            $output .= $v;
        }
        
        if ($strlenth > $length) {
            $type   =   $type?($output.='...'):('');
        }
        
        return $output;
    }

        //关键字高亮
    public function getSearchHighLight($content,$words,$filterWords=true){



        //如果没有关键词,直接输出前几段
        if (count ( $words ) == 0 || strlen($content)==0 ) {
            return $content;
        }

        if(!is_array($words)){
            $words  =   explode(',',$words);
        }

        //排重、去空值
        $words  =   array_filter(array_unique($words));
        
        //高亮替换
        $pattern    =   '('.implode('|',$words).')';
        $replacement    =   '<span style="color:red">\\1</span>';
        $content    =   eregi_replace($pattern, $replacement, $content);
        
//      foreach ( $words as $word ) {
//          //$hightlightword   =   '<span style="color:#ff0000">'.$word.'</span>';
//          //$content  =   str_ireplace($word,$hightlightword,$content);
//          $pattern    =   '('.$word.')';
//          $replacement    =   '<span style="color:red">\\1</span>';
//          $content    =   eregi_replace($pattern, $replacement, $content);
//      }
        
        $content    =   str_ireplace('&amp;','&',$content);

        //敏感词过滤
        if(function_exists('filterWord') && $filterWords )
            $content    =   filterWord($content);

        return $content;
    }


    /**
     * 获取可以搜索的选项列表
     * @return [type] [description]
     */
    public function getSearchSelect(){
        $data  = model('Cache')->get('search_app');
        if($data === false){
            $data = D('search_select')->getFieldAsArray('app_name');
            model('Cache')->set('search_app',$data);
        }
        $openApp = model('App')->getOpenApp();
        foreach($data as $v){
            if(!in_array($v,$openApp)){
                unset($data[$k]);
            }
        }
        return $data;
    }

    public function initSearchData(){
        $search_app = $this->getSearchSelect();
        $init          = array();
        foreach($search_app as $app_name){
            if(isset($init[$app_name])){
                continue;
            }
            $init[$app_name] = true;
            $model = D(ucfirst($app_name).'Search',$app_name);
            if(method_exists($model,'initData')){
                $model->initData();
            }
        }
    }

    public function updateSearchData(){
        $search_app     = $this->getSearchSelect();
        $init           = array();
        foreach($search_app as $app_name){
            if(isset($init[$app_name])){
                continue;
            }
            $init[$app_name] = true;
            $model = D(ucfirst($app_name).'Search',$app_name);
            if(method_exists($model,'updateData')){
                $model->updateData();
            }
        }
    }

    private function _initSearchHost(){
        $this->host = C('SEARCHD_HOST');
        $this->port = C('SEARCHD_PORT');
        if( empty($this->host)|| empty($this->port) ){
            saas_throw_exception("No search host config！");
        }
    }
}