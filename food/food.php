<?php
	date_default_timezone_set('PRC');
	$action = isset($_GET['act']) ? $_GET['act'] : 'index';
	if(!isset($_COOKIE['nidedaming'])){
		$action = 'login';
	}
	$title ='�����Ͱɣ�';
	$ok = false;
	switch($action){
		case 'login':	//��¼/ע��
			$title = '��¼';
			if(!empty($_POST['nidedaming'])){
				setcookie('nidedaming',$_POST['nidedaming']);
				header('Location:food.php');exit();
			}
			break;
		case 'index':	//�����б�
			$dbFile = @file_get_contents('logs/'.date('Y-m-d').'.txt');	
			if($dbFile){
				$dbFile = unserialize($dbFile);
			}
			$food = getFood();
			$title = '�����б�';
			break;
		case 'del':		//ȡ��
			$dbFile = @file_get_contents('logs/'.date('Y-m-d').'.txt');	
			if($dbFile){
				$dbFile = unserialize($dbFile);
			}
			foreach($dbFile as $k=>$v){
				if($v['uname']== $_COOKIE['nidedaming'] && $v['fid'] ==$_GET['fid']){
					unset($dbFile[$k]);
				}
			}
			file_put_contents('logs/'.date('Y-m-d').'.txt',serialize($dbFile));
			header('Location:food.php');exit();
			break;
		case 'add':     //����
			$data = getFood();
			$title = '����';
			$list = array();
			foreach($data as $v){
				if($v['fisdel']==1) continue;
				$list[trim($v['shop'])][]= $v;
			}
			break;	
		case 'doadd':
			$food = getFood();
			$food = $food[$_GET['fid']];
			$dbFile = @file_get_contents('logs/'.date('Y-m-d').'.txt');
			$add = array('uname'=>$_COOKIE['nidedaming'],'ctime'=>time(),
							'fname'=>$food['fname'],'shop'=>$food['shop'],
							'fvalue'=>$food['fvalue'],'fid'=>$food['fid']);
			if($dbFile){
				$dbFile = unserialize($dbFile);
				$dbFile[] = $add;
			}else{
				$dbFile = array($add);
			}
			file_put_contents('logs/'.date('Y-m-d').'.txt',serialize($dbFile));
			header('Location:food.php');exit();
			break;		
		case 'food':	//�˵�����
			$title = '�˵�����';
			$list = getFood();
			break;
		case 'addfood':	//��Ӳ˵�
			$title = '��Ӳ˵�';
			if(!empty($_POST)){
				$list = getFood();
				$nums = count($list);
				foreach($_POST['fname'] as $k=>$v){
					$list[$nums] = array('fid'=>$nums,
						'fname'	=> $v,
						'shop'	=> $_POST['shop'][$k],
						'fvalue'=> $_POST['fvalue'][$k],
						'fisdel'=>0,
					);
					$nums++;
				}
				@file_put_contents('food.txt',serialize($list));
				header('Location:food.php?act=food');exit();
			}
			break;
		case 'delfood':
			$list = getFood();
			$list[$_GET['fid']]['fisdel'] = 1;
			@file_put_contents('food.txt',serialize($list));
			header('Location:food.php?act=food');exit();
			break;	
		default:
			die('�����Σ��,��ػ���ȥ');
			break;
	}
	if(!isset($dbFile)){
		$dbFile = @file_get_contents('logs/'.date('Y-m-d').'.txt');
		$dbFile = unserialize($dbFile);
	}
	if($dbFile){
		foreach($dbFile as $v){
			if($v['uname']== @$_COOKIE['nidedaming']){
				$ok = true;break;
			}
		}
	}

	function getFood(){
		$data = @file_get_contents('food.txt');
		return $data ? unserialize($data) : array();	
	}
?>
<html>
<head>
<title><?php echo $title;?></title>
</head>
<style>
body {font:12px Microsoft YaHei,Arial,Helvetica,sans-serif,Simsun;  color:#333}
body, div, dl, dt, dd, ul, ol, li, pre, form, fieldset, blockquote, h1, h2, h3, h4, h5, h6,p{padding:0; margin:0 }
h1, h2, h3, h4, h5, h6 {font-weight: normal}
table, td, tr, th {font-size:12px;line-height:22px}
hr{ border:1px solid #BDC7D8; }
a:link { color:#3B7096; text-decoration:none }
a:visited { color:#3B7096; text-decoration:none }
a:hover { color:#BA2636; text-decoration:underline }
a:active { color:#3B7096 }
.text { border:1px solid #BDC7D8; font-size:12px; font-family: Arial, Helvetica, sans-serif; padding:4px 5px;height:22px}
.btn{color:#3B7096; border:1px solid #BDC7D8;height:25px;padding:2px;}
</style>
<body>
<?php
	if($action == 'login'):	//��¼
?>
<form action='food.php?act=login' method='post'>
	<h3 style="text-align:center;padding-top:200px;">ͬѧ,ɶҲ����˵��,��ǩ�����ɣ�<input type="text" name='nidedaming' class="text"> <input type="submit"  class='btn' value='Go->'></h3>
</form>
</body>
</html>
<?php exit();endif; ?>
<h2 style="text-align:center;padding:10px;"><font color="blue"><b><?php echo $_COOKIE['nidedaming'];?></b></font>,��ӭ�������ͣ���ǰ״̬:<?php if($ok){?><font color="blue">�Ѷ���</font><?php }else{?> <font color="red">δ����</font><?php }?></h2>
<div>
<h3>
 <a href="food.php?act=index">�����б�</a> | 
 <a href="food.php?act=add">��Ҫ����</a> |
 <a href="food.php?act=food">�˵�����</a> |
 <a href="food.php?act=login">����ͬѧ</a> 
</h3>
<hr/>
</div>
<?php if($action=='index'):  //��ҳ?>
<h4>��ϸ�б�</h4>
<table width="100%" border=1>
	<tr style="background-color:#BDC7D8">
		<th width="20"></th><th>���</th><th>����</th><th>�û�</th><th>����</th><th>����ʱ��</th><th>����</th>
	</tr>
	<?php if($dbFile):
		$total = array();
		$totalRmb = '0';
		foreach($dbFile as $v):
			$totalRmb += $v['fvalue'];
			$total[$v['shop']][$v['fid']][] = $v['uname']; 
	?>
	<tr align="center" <?php if($_COOKIE['nidedaming']==$v['uname']){ echo 'style="color:blue;font-weight:bold"';}?>>
		<td>&nbsp;</td>
		<td><?php echo $v['shop'];?>&nbsp;</td>
		<td><?php echo $v['fname'];?>&nbsp;</td>
		<td><?php echo $v['uname'];?>&nbsp;</td>
		<td><?php echo $v['fvalue'];?> &nbsp;Ԫ</td>
		<td width='200px'><?php echo date('Y-m-d H:i:s',$v['ctime']);?></td>
		<td> <?php if($_COOKIE['nidedaming']==$v['uname']){?><span class="btn" ><a href='food.php?act=del&fid=<?php echo $v['fid'];?>'>ȡ��</a> </span> <?php }else{?>-<?php }?></td>
	</tr>
	<?php endforeach; ?> 
	<tr style="color:red;font-weight:bold">
	<td colspan='4' align='right'>�ܼ�:</td>
	<td align='center'><?php echo $totalRmb;?> Ԫ</td>
	<td colspan ='2'>&nbsp;</td>
	</tr>
	<?php endif;?>
</table>
<br/>
<h4>����</h4>
<table width="100%" border=1>
	<tr style="background-color:#BDC7D8"><th width="20"></th><th>���</th><th>����</th><th>�û�</th><th>����</th><th>����</th><th>�ܼ�</th></tr>
	<?php $tRmb = 0;
	if(isset($total)):foreach($total as $sp=>$sv):
	$spRmb = 0;
	foreach($sv as $sfid=>$svv):?>
	<tr align="center">
		<td>&nbsp;</td>
		<td><?php echo $sp;?></td>
		<td><?php  echo $food[$sfid]['fname'];?></td>
		<td><?php echo implode(',',$svv);?></td>
		<td><?php echo $food[$sfid]['fvalue'];?> Ԫ</td>
		<td> X <?php echo count($svv);?></td>
		<td><?php echo $food[$sfid]['fvalue']*count($svv);?> Ԫ</td>
	</tr>
	<?php 
	$spRmb += $food[$sfid]['fvalue']*count($svv);
	endforeach;
	?>
	<tr style="color:blue;font-weight:bold">
		<td colspan='6' align='right' >[���:<?php echo $sp;?>]:
		<td align="center"><?php echo $spRmb;?> Ԫ</td>
	</tr>	
	<?php 
		$tRmb += $spRmb;
		endforeach;
	?>
	<tr style="color:red;font-weight:bold">
		<td colspan='6' align='right'>һ���ܼ�:
		<td align="center"><?php echo $tRmb;?> Ԫ</td>
	</tr>
	<?php endif;?>
</table>
<?php endif;?>
<?php if($action=='add'): //����
	if($ok){
		echo '<h2 align="center">���Ѿ������ˣ�</h2>';
	}else{
	?>
<h2 align="center">### ���ٶ��͹���,�����ĵ���,ֻҪ���һ�¾Ϳ���... ###</h2>
<?php foreach($list as $k=>$v): ?>
<br/><br/>
 <h2>���:<font><?php echo $k;?></font></h2>
 <hr/>
 <div style="margin:5px 10px;padding:0px; 10px;">
 <?php foreach($v as $vv):?>
 <span style="padding:2px 10px;border:1px solid #BDC7D8;margin:10px;line-height:30px;height:30px;">
 <a href="food.php?act=doadd&fid=<?php echo $vv['fid'];?>" ><?php echo $vv['fname'],' (��:',$vv['fvalue'],')';?></a>
 </span>
<?php endforeach;?>
</div>
<?php endforeach; } endif;?>
<?php if($action=='food'):  //�˵����� ?>
<h4><a href="food.php?act=food">�˵��б�</a>  | <a href="food.php?act=addfood"> ����²�</a></h4>
<table width="100%" border=1 >
	<tr style="background-color:#BDC7D8">
		<th width="20"></th><th>���</th><th>����</th><th>����</th><th>����</th>
	</tr>
	<?php 
		if(!empty($list)): foreach($list as $v):
		if($v['fisdel'] == '1') continue;
	?>
	<tr align=center>
		<td><?php echo $v['fid']?>&nbsp;</td>
		<td><?php echo $v['shop']?>&nbsp;</td>
		<td><?php echo $v['fname']?>&nbsp;</td>
		<td><?php echo $v['fvalue']?>&nbsp;Ԫ</td>
		<td>[<a href="food.php?act=delfood&fid=<?php echo $v['fid'];?>">ɾ��</a>]</td>
	</tr>	
	<?php endforeach; endif;?>
</table>
<?php endif;?>
<?php
	if($action == 'addfood'):	//�Ӳ�
?>
<form action='food.php?act=addfood' method='post'>
	<div id='List'>
	<h4 id='first'>������ <input type="text" name='fname[]' class="text"> 
	���: <input type="text" name="shop[]" class="text"> 
	����: <input type="text" name='fvalue[]' class="text"> (����)<input type="button" class='btn' onclick='addFood()' value='+'></h4>
	</div>
	<input type='submit' value='����' class='btn' >
</form>
<script>
	function addFood(){
		var obj = document.createElement("div");
		obj.innerHTML = '<h4>������ <input type="text" name="fname[]" class="text"> ���: <input type="text" name="shop[]" class="text"> ����: <input type="text" name="fvalue[]" class="text"> (����)</h4>';
		document.getElementById('List').appendChild(obj); 
	}
</script>
<?php endif; ?>
</body>
</html>