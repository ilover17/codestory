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
 * 附件模型
 +------------------------------------------------------------------------------
 * 
 * @example [path|url] description               
 * @todo    description                           
 * @author    jason <yangjs17@yeah.net> 
 * @version   1.0
 +------------------------------------------------------------------------------
 */
qmload(SITE_PATH . '/addons/liberary/UploadFile.class.php');

class AttachModel extends Model {
	protected $tableName = 'attach';

    protected	$fields		=	array('attach_id','save_name','attach_type','login','ctime','name','type','size','extension','hash','private','is_del','save_path');

   
	/**
	 * 获取附件数据
	 *
	 * @param int $attachId 附件编号
	 * @param string $field 获取字段 //作废
     * @return array
	 */
	public function getDetailByIds($ids, $field="*") {

		!is_array($ids) && $ids = explode(',', $ids);
		$cache_list = model('Cache')->getList('Attach_',$ids);
		$return  = array();
		foreach($ids as $k=>$v){
			$return[$k] =  !$cache_list[$v] ? $this->getDetail($v) : $cache_list[$v];
		}
		return $return;
	}

	public function deleteAttach($id){
		!is_array($id) && $id = explode(',',$id);
		$map['attach_id'] = array('in',$id);
		$save['is_del']   = 1; 
		$this->where($map)->save($save);
		$this->cleanCache($id);
	}
	
	public function cleanCache($ids){
		!is_array($ids) && $ids = explode(',',$ids);
		foreach($ids as $id){
			model('Cache')->rm('Attach_'.$id);
		}
		return true;
	}
	public function getDetail($id){
		if(empty($id)){
			return false;
		}
		
		if($sc = static_cache('attach_infoHash_'.$id)){
			return $sc;
		}
		if(($sc = model('Cache')->get('Attach_'.$id)) === false){
			$map['attach_id'] = $id;
			$sc =  $this->where($map)->find();
			empty($sc) && $sc = array();
			model('Cache')->set('Attach_'.$id,$sc,3600);
		}
		static_cache('attach_infoHash_'.$id,$sc);
		return $sc;
	}
	
	/**
	 * 获取已有所有附件的扩展名
	 *
	 * @return array
	 */
	public function getAllExtensions() {
		return $this->field('`extension`')->group('`extension`')->getFieldAsArray('extension');
	}

	public function toZipPath(){
		$custom_path = 'zip_data/'.date('Ymd/');
		$save_path = UPLOAD_PATH.'/'.$custom_path;
		if(!is_dir($save_path)){
			mkdir($save_path,0777,true);	
		}
		return $save_path;
	}

	/**
	 * 上传附件
	 *
	 * @param array  $data 附件相关信息
	 * @param array  $input_options 配置选项[不推荐修改, 默认使用后台的配置]
	 * @param boolen $thumb 是否启用缩略图
     * @return array 上传的附件的信息
	 */
	public function upload($data = null, $input_options = null,$thumb = false) {
		// $system_default = model('Xdata')->get('admin_Config:attach');
		// if ( empty($system_default['attach_path_rule']) || empty($system_default['attach_max_size']) || empty($system_default['attach_allow_extension']) ) {
			$system_default['attach_path_rule']		   = 'Y/md/H/';
			$system_default['attach_max_size']		   = '200'; // 默认200M
			$system_default['attach_allow_extension']  = 'jpg,gif,png,jpeg,bmp,zip,rar,doc,xls,ppt,docx,xlsx,pptx,pdf,txt,tar,gz,gzip,xmind';
		// 	model('Xdata')->put('admin_Config:attach', $system_default);
		// }
		//载入默认规则
		$default_options =	array();
		$default_options['custom_path']	=	date($system_default['attach_path_rule']);				//应用定义的上传目录规则：'Y/md/H/'
		$default_options['attach_max_size']	=	floatval($system_default['attach_max_size'])*1024*1024;	//单位: 兆
		$default_options['attach_allow_extension']	=	$system_default['attach_allow_extension']; 				//'jpg,gif,png,jpeg,bmp,zip,rar,doc,xls,ppt,docx,xlsx,pptx,pdf'
		$default_options['allow_types']	=	'';
		$default_options['save_path']	=	UPLOAD_PATH.'/'.$default_options['custom_path'];
		$default_options['save_name']	=	'';
		$default_options['save_rule']	=	'uniqid'; //什么意思？
		$default_options['save_to_db']	=	true;
		//定制化设这 覆盖默认设置
		$options	=	is_array($input_options) ? array_merge($default_options,$input_options) : $default_options;
		//转化为小写
		$options['save_name'] = strtolower($options['save_name']);;
		//初始化上传参数
        $upload					=	new UploadFile($options['attach_max_size'],$options['attach_allow_extension'],$options['allow_types']);
		//设置上传路径
		$upload->savePath		=	$options['save_path'];
        //启用子目录
		$upload->autoSub		=	false;
		//保存的名字
        $upload->saveName		=   $options['save_name'];
		//默认文件名规则
		$upload->saveRule		=	$options['save_rule'];
        //是否缩略图
        $upload->thumb          =   $thumb;

		//创建目录
		mkdir($upload->save_path,0777,true);
		//执行上传操作

        if (!$upload->upload()) {
			//上传失败，返回错误
			$return['status']	=	false;
			$return['info']		=	$upload->getErrorMsg();
			return	$return;

		} else {
			$upload_info	=	$upload->getUploadFileInfo();
			//保存信息到附件表
			$data = array(
				'login'     => $data['login'] ? $data['login'] : $GLOBALS['qm']['login'],
				'ctime'   => time(),
				'private' => $data['private'] > 0 ? 1 : 0,
				'is_del'  => 0,
			);
			foreach($upload_info as $u){
				$data['name']		=	$u['name'];
				$data['type']		=	$u['type'];
				$data['size']		=	$u['size'];
				$data['extension']	=	strtolower($u['extension']);
				$data['hash']		=	$u['hash'];
				$data['save_path']	=	$options['custom_path'];
				$data['save_name']	=	$u['savename'];
				$data['attach_id']  =	$this->add($data);
				$data['key'] =	$u['key'];
				//建立缓存
				model('Cache')->set('Attach_'.$data['attach_id'],$data);
				$data['size'] = byte_format($data['size']);
				$infos[]	 =	$data;
				unset($data);
			}
			//输出信息
			$return['status']	=	1;
			$return['info']		=	$infos;
			//上传成功，返回信息
			return	$return;
    	}
	}

    public function getImageAllowType(){
        return array(
                        'self',
                        'cut_140_202',	//二寸
                        'cut_258_142',
                        'cut_112_162',
                        'cut_150_150',
                        'thumb_1024_786',
	                    'thumb_100_100',  
	                    'thumb_50_50',
	                    'thumb_425_5000',
	                    'thumb_570_5000',
	                    'thumb_710_5000',
	                    'thumb_5000_5000',
	                    'thumb_200_200',
	                    'cut_200_200',
	                    'cut_160_160',
	                    'cut_100_100',
	                    'cut_120_120',
	                    'cut_50_50',
	                    'cut_20_20',
	                    'cut_140_140',
	                    'cut_64_64',
	                    'cut_400_248'
                    );
    }

    /**
     * 获取缩略图
     * @param unknown_type $filename 原图路劲、url
     * @param unknown_type $width 宽度
     * @param unknown_type $height 高
     * @param unknown_type $cut 是否切割 默认不切割
     * @return string
     */
    public function getThumbImage($filename,$width=100,$height='auto',$cut_type='thumb'){
        $filename =  str_ireplace(SITE_URL,'.',$filename);  //将URL转化为本地地址
        $info = pathinfo($filename);

        $oldFile = $info['dirname'].DS.$info['filename'].'.'.$info['extension'];
        $thumbFile = $info['dirname'].DS.$info['filename'].'_'.$cut_type.'_'.$width.'_'.$height.'.'.$info['extension'];
        $oldFile = str_replace('\\','/', $oldFile);
        $thumbFile = str_replace('\\','/',$thumbFile);
        if(!file_exists($oldFile)){
             $info['src']    = $oldFile;
             $info['width']  = $width;
             $info['height'] = $height;
             return $info;
        }else{
            if(!file_exists($thumbFile)){
                //生成缩略图
                ini_set('memory_limit','512M');
                if($cut_type == 'cut'){
                    Image::cut($filename,$thumbFile,$width,$height);
                }else{
                    Image::thumb( $filename, $thumbFile , '' , $width , $height );
                }

                if(!file_exists($thumbFile)){
                    $thumbFile = $oldFile;
                }
           }
        }
        $info = Image::getImageInfo($thumbFile);
        $info['src'] = SITE_URL.'/'.trim($thumbFile,'./');
        return $info;
    }

}