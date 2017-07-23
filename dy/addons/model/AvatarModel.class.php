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
 * 头像设置模型
 +------------------------------------------------------------------------------
 * 
 * @example [path|url] description               
 * @todo    description                           
 * @author    jason <yangjs17@yeah.net> 
 * @version   1.0
 +------------------------------------------------------------------------------
 */
class AvatarModel {
    //上传头像
    function upload(){

    	$_data['img_type'] = 'self';
    	$info = model('Attach')->upload($_data);

    	if(!$info['status']){
    		$return = array('status'=>0,'data'=>$info['info'],'info'=>$info['info']);
    		return $return;
    	}
        $file_path = 'data/upload/'.$info['info'][0]['save_path'].$info['info'][0]['save_name'];
        include( ADDON_PATH.'/liberary/Image.class.php' );

        $imginfo = Image::getImageInfo(SITE_PATH.'/'.$file_path);

        //获取大边
        $smaller = $imginfo['width'] > $imginfo['height'] ? $imginfo['height'] : $imginfo['width'];
        if($smaller > 360){
            $cutbig = 360;
        }else{
            $cutbig = $smaller;
        }
        $thumbfile = str_replace('.'.$info[0]['extension'],'_thumb.'.$info[0]['extension'],$file_path);
        
        Image::thumb( SITE_PATH.'/'.$file_path , SITE_PATH.'/'.$thumbfile ,'', $cutbig , $cutbig );

        list($sr_w, $sr_h, $sr_type, $sr_attr) = @getimagesize($thumbfile);

    	$return['data']['picurl'] 		= $thumbfile;
    	$return['data']['picwidth'] 	= $sr_w;
    	$return['data']['picheight'] 	= $sr_h;
    	$return['data']['bigpic']		= $file_path;
    	$return['data']['attach_id']    = $info['info'][0]['attach_id'];
    	$return['status']    = '1';
    	return $return;
    }

    //保存图片 edit by yangjs 
    function dosave(){
        $x1 = (int)$_POST['x1'];//客户端选择区域左上角x轴坐标
        $y1 = (int)$_POST['y1'];//客户端选择区域左上角y轴坐标
        $w = (int)$_POST['w'];//客户端选择区 的高
        $h = (int)$_POST['h'];//客户端选择区 的高
        $x2 = $x1 + $w;
        $y2 = $y1 + $h;
        $src = SITE_PATH.'/'.$_POST['picurl'];//图片的路径
        $trueSrc = str_replace('_thumb', '', $src);
        // 获取源图的扩展名宽高
        $_i = @getimagesize($src);
        list($_sr_w, $_sr_h, $_sr_type, $_sr_attr) = @getimagesize($src);
        if ($_sr_type) {
            //获取后缀名
            $ext = image_type_to_extension($_sr_type,false);
        } else {
            $return['info'] = '保存失败';
            $return['status']    = '0';
            return $return;
        }
        //获取真正图片的数据
        list($sr_w, $sr_h, $sr_type, $sr_attr) = @getimagesize($trueSrc);

        $x1 = ($x1*$sr_w) / $_sr_w;
        $y1 = ($y1*$sr_h) / $_sr_h;
        $w  = ($w*$sr_w)  / $_sr_w;
        $h  = ($h*$sr_h)  / $_sr_h;

        $big_w = '120';
        $big_h = '120';


        $func   =   ($ext != 'jpg') ? 'imagecreatefrom' . $ext : 'imagecreatefromjpeg';
        $img_r  =   call_user_func($func, $trueSrc);

        $dst_r  =   ImageCreateTrueColor( $big_w, $big_h );
        $back   =   ImageColorAllocate( $dst_r, 255, 255, 255 );
        ImageFilledRectangle( $dst_r, 0, 0, $big_w, $big_h, $back );//黑色的大图区域
      
        //去正真的原图截取
        ImageCopyResampled( $dst_r, $img_r, 0, 0, $x1, $y1, $big_w, $big_h, $w, $h );

        ImagePNG($dst_r, $trueSrc);  // 生成大图

        ImageDestroy($dst_r);
        ImageDestroy($img_r);
        $return['status'] = 1;
        $return['data']['avatar'] = str_replace(SITE_PATH,SITE_URL,$trueSrc);
        return $return;
    }
}