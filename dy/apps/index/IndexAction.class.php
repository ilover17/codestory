<?php
/**
 * 教务平台 - 首页
 */
class IndexAction extends Action
{	
	/**
	 * 项目一览表
	 * @return [type] [description]
	 */

	public function login(){
		$this->display();
	}

	public function doLogin(){
		if( model('Passport')->loginLocal($_POST['login'],$_POST['pass'])){
			$this->success();
		}else{
			$this->error('登录失败');
		}
	}

	public function index(){
		$map = array();
		$_GET = array_map('t',$_GET);
		!empty($_GET['uname']) && $map['uname'] = array('like','%'.$_GET['uname'].'%');
		!empty($_GET['money']) && $map['money'] = $_GET['money'];
		!empty($_GET['cday']) && $map['cday'] = $_GET['cday'];
		!empty($_GET['username']) && $map['username'] = array('like','%'.$_GET['username'].'%');
		!empty($_GET['userfee']) && $map['userfee'] = $_GET['userfee'];
		!empty($_GET['platfee']) && $map['platfee'] = $_GET['platfee'];
		if( isset($_GET['status']) && $_GET['status'] !='all' ){
			$map['status'] = intval($_GET['status']);
		}
		if($this->user['is_admin'] != 1){
			$map['login'] = $this->user['login'];
		}
		$lists = D('fee')->where($map)->order('cday desc')->findPage(20);
		$total = D('fee')->where($map)->field('sum(money) as money,sum(platfee) as platfee,sum(userfee) as userfee,sum(realyfee) as realyfee,sum(curbackfee) as curbackfee,sum(totalbackfee) as totalbackfee')->find();;
		$this->assign('total',$total);
		$this->assign('lists',$lists);
		$this->display();
	}

	public function user(){
		$list = model('User')->where('is_del=0')->findAll();
		$this->assign('lists',$list);
		$this->display();
	}

	public function doAddUser(){
		$uname = t($_POST['uname']);
		$login = t($_POST['login']);
		$password = t($_POST['pass']);
		$is_admin = intval($_POST['is_admin']);
		$uid = model('User')->addUser($login,$password,$uname,$is_admin);
		if($uid){
			$this->success('用户添加成功');
		}else{
			$this->error('用户添加失败');
		}
	}

	public function doDelUser(){
		$uid = intval($_POST['uid']);
		if(empty($uid)){
			$this->error('错误的请求');
		}
		$map['uid'] = $uid;
		$save['is_del'] = 1;
		D('user')->where($map)->save($save);
		$this->success('删除成功');
	}

	public function badBox(){
		$info = D('fee')->find(intval($_GET['id']));
		$this->assign('info',$info);
		$this->display();
	}

	public function todayback(){
		$map = array();
		$_GET = array_map('t',$_GET);
		$map['back_day'] = date('Y-m-d',time());
		!empty($_GET['fee_id']) && $map['fee_id'] = $_GET['fee_id'];
		!empty($_GET['cday']) && $map['cday'] = $_GET['cday'];
		if( isset($_GET['status']) && $_GET['status'] !='all' ){
			$map['status'] = intval($_GET['status']);
		}
		if($this->user['is_admin'] != 1){
			$map['login'] = $this->user['login'];
		}
		$lists = D('back')->where($map)->order('back_day desc')->findPage(20);
		$uname = D('fee')->getHashList('id','uname');
		$this->assign('uname',$uname);
		$this->assign('lists',$lists);
		$this->display();
	}
	public function back(){
		$map = array();
		$_GET = array_map('t',$_GET);
		!empty($_GET['fee_id']) && $map['fee_id'] = $_GET['fee_id'];
		!empty($_GET['cday']) && $map['cday'] = $_GET['cday'];
		!empty($_GET['back_day']) && $map['back_day'] = array('like','%'.$_GET['back_day'].'%');
		!empty($_GET['realy_back_day']) && $map['realy_back_day'] = array('like','%'.$_GET['realy_back_day'].'%');
		!empty($_GET['backnums']) && $map['backnums'] = $_GET['backnums'];
		
		if( isset($_GET['status']) && $_GET['status'] !='all' ){
			$map['status'] = intval($_GET['status']);
		}
		if($this->user['is_admin'] != 1){
			$map['login'] = $this->user['login'];
		}
		$lists = D('back')->where($map)->order('back_day desc')->findPage(20);
		$uname = D('fee')->getHashList('id','uname');
		$this->assign('uname',$uname);
		$this->assign('lists',$lists);
		$this->display();
	}

	public function chengben(){
		$where = '1';
		!empty($_GET['cday'])	&& $where .=" and cday >='".t($_GET['cday'])."'";
		!empty($_GET['eday'])	&& $where .=" and cday <='".t($_GET['cday'])."'";

		if($this->user['is_admin'] != 1){
			$where .=" and login='".$this->user['login']."'";
		}
		$lists = D('fee')->where($where)->field('sum(money) as money,sum(platfee) as platfee,sum(userfee) as userfee,sum(realyfee) as realyfee,sum(curbackfee) as curbackfee,sum(totalbackfee) as totalbackfee,cday')->group('cday')->findAll();
		
		$this->assign('lists',$lists);

		$total = D('fee')->where($where)->field('sum(money) as money,sum(platfee) as platfee,sum(userfee) as userfee,sum(realyfee) as realyfee,sum(curbackfee) as curbackfee,sum(totalbackfee) as totalbackfee')->find();;
		$this->assign('total',$total);
		$this->display();
	}

	public function duizhang(){
		$this->display();	
	}

	public function backBox(){
		$id = intval($_GET['id']);
		$info = D('back')->find($id);
		$feeinfo = D('fee')->where('id='.$info['fee_id'])->find();

		$this->assign('info',$info);
		$this->assign('feeinfo',$feeinfo);
		$this->display();
	}

	public function doDel(){
		$id = intval($_POST['id']);
		if(empty($id)){
			$this->error('错误的请求');
		}
		$map['id'] = $id;
		D('fee')->where($map)->delete();
		$_map['fee_id'] = $id;
		D('back')->where($_map)->delete();
		$this->success('删除成功');
	}

	public function doBack(){
		$id = intval($_POST['id']);
		if(empty($id)){
			$this->error('错误的请求');
		}
		$map['id'] = $id;
		$save['is_back'] = 1;
		$save['notes'] = t($_POST['notes']);
		$save['realy_back_day'] = t($_POST['realy_back_day']);
		$save['realy_back_fee'] = t($_POST['realy_back_fee']);
		D('back')->where($map)->save($save);
		$info = D('back')->find($id);
		$feeinfo =  D('fee')->where('id='.$info['fee_id'])->find();
		$_save['curbackfee'] = intval($feeinfo['curbackfee'])+$save['realy_back_fee'];
		D('fee')->where('id='.$info['fee_id'])->save($_save);
		$this->success();
	}
	//设置某次放款为坏账
	public function doBad(){
		$id = intval($_POST['id']);
		if(empty($id)){
			$this->error('错误的请求');
		}
		$save['status'] = 1;
		$save['notes'] = t($_POST['notes']);
		//标记为坏账，此账单未还的
		D('fee')->where('id = '.$id)->save( $save );
		D('back')->where('fee_id='.$id.' and is_back=0')->delete();
		$this->success('设置成功');
	}
	public function doAdd(){
		if( empty($_POST['uname']) || empty($_POST['money'])
			|| empty($_POST['backtimes']) || empty($_POST['backfee'])
		 ){
			$this->error('请输入必填项');
		}
		if(empty($_POST['cday'])){
			$this->error('请选择放款日期');
		}
		$_POST = array_map('t',$_POST);
		$_POST['curbackfee'] = $_POST['backfee'];
		$_POST['ctime'] = time();
		$_POST['status'] = 0;
		$_POST['totalbackfee'] = ($_POST['backtimes'] - 1)* $_POST['backfee'] ;
		$_POST['login'] = $this->user['login'];
		$fee_id = D('fee')->add($_POST);
		$week = array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
		if($fee_id){
			//计算期数
			$cur_day = strtotime($_POST['cday']);
			for($i=0;$i<$_POST['backtimes'];$i++){
				$add = array();
				$add['fee_id'] = $fee_id;
				$add['cday']   = $_POST['cday'];
				$add['back_nums'] = $i+1;
				if($i==0){
					$add['is_back'] = 1;
					$add['note'] = '第一期自动还款';
					$add['realy_back_fee'] = $_POST['backfee'];
					$add['realy_back_day'] = $_POST['cday'];
				}elseif($i==1){
					$add['is_back'] = 0;
					$cur_day += 3600*24*6;
				}else{
					$add['is_back'] = 0;
					$cur_day += 3600*24*7;
				}
				$add['back_day'] = date('Y-m-d',$cur_day);
				$add['week'] = $week[date('w',$cur_day)];
				$add['back_fee'] = $_POST['backfee'];
				$add['login'] = $this->user['login'];
				D('back')->add($add);
			}
			$this->success('放款信息添加成功');
		}else{
			$this->error('放款信息添加失败');
		}
	}
    

    public function verify(){
    	qmload(ADDON_PATH.'/liberary/Image.class.php');
		qmload(ADDON_PATH.'/liberary/String.class.php');
		Image::buildImageVerify();
    }

    public function logout(){
    	model('Passport')->logoutLocal();
    	redirect(SITE_URL);exit();
    }

}
