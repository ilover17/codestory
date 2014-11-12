<?php
define('__QQWRY__' ,"QQWry.Dat");
class Myip
{   
/**
*  @Desc 根据纯真IP库获取IP的地址
*  @desc 文件编码为GB2312
*
*/
    var $StartIP=0;
    var $EndIP=0;
    var $Country='';    
    var $Local='';      
    var $CountryFlag=0;
    var $fp;
    var $FirstStartIp=0;
    var $LastStartIp=0;
    var $EndIpOff=0 ;
    function getStartIp($RecNo){
     $offset=$this->FirstStartIp+$RecNo * 7 ;
     @fseek($this->fp,$offset,SEEK_SET) ;
     $buf=fread($this->fp ,7) ;
     $this->EndIpOff=ord($buf[4]) + (ord($buf[5])*256) + (ord($buf[6])* 256*256);
     $this->StartIp=ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
     return $this->StartIp;
    }
    function getEndIp(){
     @fseek ( $this->fp , $this->EndIpOff , SEEK_SET ) ;
     $buf=fread ( $this->fp , 5 ) ;
     $this->EndIp=ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
     $this->CountryFlag=ord ( $buf[4] ) ;
     return $this->EndIp ;
    }
    function getCountry(){
     switch ( $this->CountryFlag ) {
        case 1:
        case 2:
         $this->Country=$this->getFlagStr ( $this->EndIpOff+4) ;
         //echo sprintf('EndIpOffset=(%x)',$this->EndIpOff );
         $this->Local=( 1 == $this->CountryFlag )? '' : $this->getFlagStr ( $this->EndIpOff+8);
         break ;
        default :
         $this->Country=$this->getFlagStr ($this->EndIpOff+4) ;
         $this->Local=$this->getFlagStr ( ftell ( $this->fp )) ;
     }
    }
    function getFlagStr ($offset){
     $flag=0 ;
     while(1){
        @fseek($this->fp ,$offset,SEEK_SET) ;
        $flag=ord(fgetc($this->fp ) ) ;
        if ( $flag == 1 || $flag == 2 ) {
         $buf=fread ($this->fp , 3 ) ;
         if ($flag==2){
            $this->CountryFlag=2;
            $this->EndIpOff=$offset - 4 ;
         }
         $offset=ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])* 256*256);
        }
        else{
         break ;
        }
     }
     if($offset<12)
        return '';
     @fseek($this->fp , $offset , SEEK_SET ) ;

     return $this->getStr();
    }
    function getStr ( )
    {
     $str='' ;
     while ( 1 ) {
        $c=fgetc ( $this->fp ) ;
        if(ord($c[0])== 0 )
         break ;
        $str.= $c ;
     }
     return $str ;
    }
    function qqwry ($dotip='') {
        if( !is_string($dotip) || $dotip==''){return;}
        if(preg_match("/^127/",$dotip)){$this->Country="本地网络";return ;}
        elseif(preg_match("/^192/",$dotip)) {$this->Country="局域网";return ;}

     $nRet;
     $ip=$this->IpToInt ( $dotip );
     $this->fp= fopen(__QQWRY__, "rb");
     if ($this->fp == NULL) {
         $szLocal= "OpenFileError";
        return 1;
     }
     @fseek ( $this->fp , 0 , SEEK_SET ) ;
     $buf=fread ( $this->fp , 8 ) ;
     $this->FirstStartIp=ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
     $this->LastStartIp=ord($buf[4]) + (ord($buf[5])*256) + (ord($buf[6])*256*256) + (ord($buf[7])*256*256*256);
     $RecordCount= floor( ( $this->LastStartIp - $this->FirstStartIp ) / 7);
     if ($RecordCount <= 1){
        $this->Country="FileDataError";
        fclose($this->fp) ;
        return 2 ;
     }
     $RangB= 0;
     $RangE= $RecordCount;
     while ($RangB < $RangE-1)
     {
     $RecNo= floor(($RangB + $RangE) / 2);
     $this->getStartIp ( $RecNo ) ;

        if ( $ip == $this->StartIp )
        {
         $RangB=$RecNo ;
         break ;
        }
     if ($ip>$this->StartIp)
        $RangB= $RecNo;
     else
        $RangE= $RecNo;
     }
     $this->getStartIp ( $RangB ) ;
     $this->getEndIp ( ) ;

     if ( ( $this->StartIp <= $ip ) && ( $this->EndIp >= $ip ) ){
        $nRet=0 ;
        $this->getCountry ( ) ;
      $this->Local=str_replace("（我们一定要解放台湾！！！）", "", $this->Local);
     }
     else{
        $nRet=3 ;
        $this->Country='未知' ;
        $this->Local='' ;
     }
     fclose ( $this->fp );
  $this->Country=preg_replace("/(CZ88.NET)|(纯真网络)/","局域网/未知",$this->  Country);
  $this->Local=preg_replace("/(CZ88.NET)|(纯真网络)/","局域网/未知",$this-> Local);
    return $nRet ;
    }
    function IpToInt($Ip) {
     $array=explode('.',$Ip);
     $Int=($array[0] * 256*256*256) + ($array[1]*256*256) + ($array[2]*256) + $array[3];
     return $Int;
    }
}


function getAreaByIp($ip){
	$myip=new Myip();
	$r=array();
	if(!is_array($ip)){
	   $ifErr=$myip->qqwry($ip);
	   $r['ip']		=$ip;
	   $r['Country']=$myip->Country;
	   $r['Local']	=$myip->Local;
	   $r['fp']		=$myip->fp;
//	  return iconv('GB2312','UTF-8',$this->ci->myip->Country);
	}else{
	 foreach ($ip as $pv){
		$ifErr=$myip->qqwry($pv);
		$d['ip']		=$pv;
		$d['Country']	=$myip->Country;
		$d['Local']		=$myip->Local;
		$d['fp']		=$myip->fp;
		$r[]=$d;
	  }
	}
	  return $r;
}

 
/**
* 取客户端IP地址
*/
function onlineip()
{
	if(isset($_SERVER['HTTP_X_REAL_IP']) && $_SERVER['HTTP_X_REAL_IP'] && strcasecmp($_SERVER['HTTP_X_REAL_IP'], 'unknown')){
		$onlineip = $_SERVER['HTTP_X_REAL_IP'];
	}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], 'unknown')){
		$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}elseif(isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] && strcasecmp($_SERVER['HTTP_CLIENT_IP'], 'unknown')){
		$onlineip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')){
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	$onlineip = preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
	return $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
}	

$ip[]=onlineip();
$ip[]='123.158.170.51';//联通
$ip[]='122.87.255.191';//江苏
$ip[]='219.234.81.136';//北京
$ip[]='222.73.191.115';//上海
$ip[]='123.153.195.129';//安徽
$ip[]='122.85.125.3';//铁通
$ip[]='60.181.75.122';//未知
$ip[]='123.96.180.143';//移动

echo '<pre>';
print_r(getAreaByIp($ip));
echo '</pre>';