<?php
ini_set('display_errors','on');
error_reporting(E_ERROR);
header("Content-Type:text/html; charset=utf-8");
//球员price,进一球+10,赢一场+5,输一场-1
//球队price,赢一场+10,平一场+5,进一球+1,输一场-5，失一球-1
//胜负概率(主队为A，客队为B)  A/(A+B)= X%, rand(1,100) = Y，Y =<（X+10）则 A赢，Y>=(X+20)则B赢 ，否则两队平
//比分概率(
//  a=主队进球数，b=客队进球数
//	主队赢,rand(1,8)=a,rand(0,3)=b,如果a>b,则返回，否则b-1再返回,a:b
//	客队赢,rand(0,3)=a,rand(1,4)=b,如果a>b,则a=b-1,否则返回,a:b
//	打平,则rand(0,5)=a,b,并返回
//	)
//进球概率
//(x=rand(1,100)
// 如果 x<= 70,则前锋进球
// 如果 70<x<90,则中场进球
// 如果 90<=x<99,则后卫进球
// 如果 x=100,则门将进球
//)
//DB
// match_member_goals : team_name,name,match_year,match_lunch,goal_type,goal_body
// match_detail:master_team,slave_team,master_goal,slave_goal,match_year,match_lunch,master_goal_members,slave_goal_members
// match_record:team_name,win_nums,lost_nums,eq_nums,credit,goal_in,goal_lost,match_year
// team:team_name,win,eq,lost,credit,first_nums,goal_nums,lostgoal_nums,price,country
// team_member:name,goals,team_name,place,match_times,price

class football{
	//比赛详细
	public $match_detail = array();
	//比赛记录
	public $match_record = array();
	public $match_member_goals = array();
	//球队
	public $team = array();
	public $team_hash = array();
	//球员
	public $team_member = array();
	//进球类型 //0:运动战，1定位球，2点球
	public $goal_type = array('运动战','定位球','点球','角球');
	//进球部位 0：左脚，1，右脚，2头球，3身体其他部位
	public $goal_body = array('左脚','右脚','头球','身体其他部位');
	
	//赛季
	public $match_year = '';
	public $team_number = 20;
	public $match_hash_list = array();
	//link
	public $link = null;
	public $query_id = null;
	public $sqlError = [];
	public $dbConfig = [
		'host'=>'mysql57',
		'user'=>'root',
		'password'=>'123456',
		'db_name'=>'football',
		'db_port'=>3306
	];

	public $log ='';

	//初始化数据
	function init($match_year,$team_number=20){
		$this->match_detail = $this->match_record 
						    = $this->match_member_goals 
						    = $this->team
						    = $this->team_hash
						    = $this->team_member
						    = $this->match_hash_list
						    = array();
		$this->log = '';
		$this->match_year = $match_year;
		$this->team_number = $team_number;
		$this->connect();
		$this->team = $this->getTeam();
		return $this;
	}

	function start(){
		//获取半程对阵图
		$this->match_hash_list = $this->getList();
		$id = uniqid();
		$this->log = '<div><div onclick="document.getElementById(\'c'.$id.'\').style.display=\'block\'">【'.$this->match_year.' 赛况播报】</div><br/><div  id="c'.$id.'" style="display:none">';
		foreach($this->match_hash_list as $k=>$lun){
			$this->playLun($lun,$k+1,1);
		}
		foreach($this->match_hash_list as $k=>$lun){
			$this->playLun($lun,$k+20,2);
		}
		//保存记录
		$this->saveRecord();
		$this->showResult();
	}

	public function reset(){
		$sql = "TRUNCATE `match_detail`";
		$this->query($sql);
		$sql = "TRUNCATE `match_member_goals`";
		$this->query($sql);
		$sql = "TRUNCATE `match_record`";
		$this->query($sql);
		$sql = "TRUNCATE `team`";
		$this->query($sql);
		$sql = "TRUNCATE `team_member`";
		$this->query($sql);
		$sql = "insert into team select * from team_bak";
		$this->query($sql);
		$sql = "insert into team_member select * from team_member_bak";
		$this->query($sql);
	}
	//将本赛季的记录保存到数据库
	private function saveRecord(){
		
		//更新每个球队记录 team
		//更新每个球员记录 team_members
		//添加每个球员的出场次数--38--
		foreach($this->team as $v){
			$members = $v['members'];
			$where = "team_id = {$v['team_id']}";
			unset($v['members'],$v['team_id']);
			$this->update($v,$where,'team');
			//更新球员表
			foreach($members as $mv){
				$where = "member_id = {$mv['member_id']}";
				unset($mv['member_id']);
				$mv['match_times'] +=38;
				$this->update($mv,$where,'team_member');
			}
		}
		//插入match_detail表数据
		
		foreach($this->match_detail as $v){
			$this->add($v,'match_detail');
		}
		//插入match_member_goals表数据
		foreach($this->match_member_goals as $v){
			$this->add($v,'match_member_goals');
		}
		//插入match_record表数据
		foreach($this->match_record as $v){
			$this->add($v,'match_record');
		}
	}

	public function showResult(){
		echo '<style>th{text-align:left}; td{text-align:left}</style>';
		echo $this->log;
		echo '</div>';
		echo '<br/>【赛季积分榜】<br/>';
		$sql = "select * from match_record where match_year = '{$this->match_year}' order by credit desc,goal_in - goal_lost desc";
		$data = $this->query($sql);
		$html = "<table width='100%'><tr>
				<th>排名</th>
				<th>球队</th>
				<th>胜</th>
				<th>平</th>
				<th>负</th>
				<th>积分</th>
				<th>进球</th>
				<th>失球</th></tr>";
		foreach($data as $k=>$v){
			$html.="<tr><td>".($k+1)."</td>
			<td>{$v['team_name']}</td>
			<td>{$v['win_nums']}</td>
			<td>{$v['eq_nums']}</td>
			<td>{$v['lost_nums']}</td>
			<td>{$v['credit']}</td>
			<td>{$v['goal_in']}</td>
			<td>{$v['goal_lost']}</td></tr>";
			if($k == 0){
				//增加冠军数
				$sql = "update team set first_nums =first_nums+1 where team_name ='{$v['team_name']}'";
				$this->query($sql);
			}
		}
		$html .="</table>";
		echo $html;
		echo '<br/>【赛季射手榜】<br/>';
		$sql  ="select count(1) as nums,name,team_name from match_member_goals where match_year = '{$this->match_year}' group by name,team_name order by nums desc limit 20";
		$data = $this->query($sql);
		$html = "<table width='100%'><tr>
				<th>排名</th>
				<th>球员</th>
				<th>球队</th>
				<th>进球数</th></tr>";
		foreach($data as $k=>$v){
			$html.="<tr><td>".($k+1)."</td>
			<td>{$v['name']}</td>
			<td>{$v['team_name']}</td>
			<td>{$v['nums']}</td></tr>";
		}
		$html .="</table>";
		echo $html;	
		echo '<br/>【历史积分榜】<br/>';
		$sql = "select *,goal_nums - lostgoal_nums as vnums from team order by credit desc,vnums desc";
		$data = $this->query($sql);
		$html = "<table width='100%'><tr>
				<th>排名</th>
				<th>球队</th>
				<th>胜</th>
				<th>平</th>
				<th>负</th>
				<th>积分</th>
				<th>冠军数</th>
				<th>进球</th>
				<th>失球</th>
				<th>价值</th>
				<th>国家</th></tr>";
		foreach($data as $k=>$v){
			$html.="<tr><td>".($k+1)."</td>
			<td>{$v['team_name']}</td>
			<td>{$v['win']}</td>
			<td>{$v['eq']}</td>
			<td>{$v['lost']}</td>
			<td>{$v['credit']}</td>
			<td>{$v['first_nums']}</td>
			<td>{$v['goal_nums']}</td>
			<td>{$v['lostgoal_nums']}</td>
			<td>{$v['price']}</td>
			<td>{$v['country']}</td>
			</tr>";
		}
		$html .="</table>";
		echo $html;
		echo '<br/>【历史射手榜】<br/>';
		$sql = "select * from team_member where goals > 0 order by goals desc limit 20";
		$data = $this->query($sql);
		$html = "<table width='100%'><tr>
				<th>排名</th>
				<th>球员</th>
				<th>进球数</th>
				<th>球队</th>
				<th>位置</th>
				<th>出场次数</th>
				<th>身价</th>
				</tr>";		
		foreach($data as $k=>$v){
			$html.="<tr><td>".($k+1)."</td>
			<td>{$v['name']}</td>
			<td>{$v['goals']}</td>
			<td>{$v['team_name']}</td>
			<td>{$v['place']}</td>
			<td>{$v['match_times']}</td>
			<td>{$v['price']}</td>
			</tr>";
		}
		$html .="</table>";
		echo $html;
	}
	//一轮比赛
	private function playLun($lun,$nums,$type = 1){
		$this->log .= '第'.$nums.'轮：<br/>';
		foreach($lun as $game){
			//主客场调换
			if($type == 1){
				$this->playGame($game[0],$game[1],$nums);	
			}else{
				$this->playGame($game[1],$game[0],$nums);	
			}
		}
	}

	//一场比赛
	private function playGame($master,$slave,$lunch){
		$this->match_lunch = $lunch;
		$result = $this->getResult($master,$slave);
		$this->log .= $this->team_hash[$master].' '.$result[0].' VS '.$result[1].' '.$this->team_hash[$slave];
		$this->log .='  (进球队员：'.$result[2].';'.$result[3].")";
		$this->log .='<br/>';
	}
	//获取比赛结果	
	//胜负概率(主队为A，客队为B)  A/(A+B)= X%, rand(1,100) = Y，Y =<（X+10）则 A赢，Y>=(X+20)则B赢 ，否则两队平
	//比分概率(
	//  a=主队进球数，b=客队进球数
	//	主队赢,rand(1,8)=a,rand(0,3)=b,如果a>b,则返回，否则b-1再返回,a:b
	//	客队赢,rand(0,3)=a,rand(1,4)=b,如果a>b,则a=b-1,否则返回,a:b
	//	打平,则rand(0,5)=a,b,并返回
	//	)	
	public function getResult($master,$slave,$caneq = true){
		$masterPrice = $this->team[$this->team_hash[$master]]['price'];
		$slavePrice  = $this->team[$this->team_hash[$slave]]['price'];
		$Y = rand(1,100);
		if($Y > 50){
			//平均来
			$X = 50;
		}else{
			//按实力来
			$X = $masterPrice/($masterPrice+$slavePrice)*100;	
		}
		$Y = rand(1,100);
		$ra = $rb= $a =$b =0;
		if($Y<=($X+10)){
			$ra = '1';
			$rb = '-1';
			$a = rand(1,5);
			$b = rand(0,2);
			if($b>=$a) $b = $a-1;
		}elseif($Y>=($X+20)){
			$ra = '-1';
			$rb = '1';
			$a = rand(0,1);
			$b = rand(1,3);
			if($a>=$b) $a = $b-1;
		}else{
			if($caneq){
				$ra = '0';
				$rb = '0';
				$a = $b =rand(0,3);	
			}else{
				//点球
				$a = rand(1,5);
				$b = rand(1,5);
				//T平的话算作a获胜
				if($a == $b){ $a = $b+1;}
				$ra = $a > $b ? '1':'-1';
				$rb = $a > $b ? '-1':'1';
			}
		}
		//处理球队输赢
		$this->recordTeamPrice($this->team_hash[$master],$ra,$a,$b);
		$this->recordTeamPrice($this->team_hash[$slave],$rb,$b,$a);
		//处理入球及赢球记录
		$master_goals_members = $this->getGoalsMembers($this->team_hash[$master],$a);
		$slave_goals_members = $this->getGoalsMembers($this->team_hash[$slave],$b);
		$this->match_detail[] = array(
				'master_team'=>$this->team_hash[$master],
				'slave_team' =>$this->team_hash[$slave],
				'master_goal'=>$a,
				'slave_goal' =>$b,
				'match_year' =>$this->match_year,
				'match_lunch'=>$this->match_lunch,
				'master_goal_members'=>$master_goals_members,
				'slave_goal_members' =>$slave_goals_members,
			);
		return array($a,$b,$master_goals_members,$slave_goals_members);
	}
	//球队price,赢一场+10,平一场+5,进一球+2,输一场-5，失一球-1
	//球员,赢一场+5,平一场+1,输一场-1
	private function recordTeamPrice($team,$result,$goals,$lost){
		if(!isset($this->match_record[$team])){
			$this->match_record[$team] = array(
				'team_name'=>$team,
				'win_nums'=>0,
				'lost_nums'=>0,
				'eq_nums'=>0,
				'credit'=>0,
				'goal_in'=>0,
				'goal_lost'=>0,
				'match_year'=>$this->match_year,
				);
		}

		if($result === '1'){
			$member_add = 5;
			$this->team[$team]['price'] += 10;
			$this->team[$team]['win'] +=1;
			$this->team[$team]['credit']+=3;
			$this->match_record[$team]['credit'] +=3;
			$this->match_record[$team]['win_nums'] +=1;
		}elseif($result === '-1'){
			$member_add = -1;
			$this->team[$team]['price'] -= 5;
			$this->team[$team]['lost'] +=1;
			$this->match_record[$team]['lost_nums'] +=1;
		}elseif($result === '0'){
			$member_add = 1;
			$this->team[$team]['price'] += 5;
			$this->team[$team]['eq'] +=1;
			$this->team[$team]['credit']+=1;
			$this->match_record[$team]['credit'] +=1;
			$this->match_record[$team]['eq_nums'] +=1;
		}
		$this->match_record[$team]['goal_in'] += $goals;
		$this->match_record[$team]['goal_lost'] += $lost;
		$this->team[$team]['price'] += $goals*2;
		$this->team[$team]['price'] -= $lost;
		//记录进球数,失球数
		$this->team[$team]['goal_nums'] += $goals;
		$this->team[$team]['lostgoal_nums'] +=$lost;
		//记录球员price
		foreach($this->team[$team]['members'] as $k=>$v){
			$this->team[$team]['members'][$k]['price'] = $this->team[$team]['members'][$k]['price'] + $member_add;
		}
	}
	//球员price,进一球+10
	//进球概率
	//(x=rand(1,100)
	// 如果 x<= 70,则前锋进球
	// 如果 70<x<90,则中场进球
	// 如果 90<=x<99,则后卫进球
	// 如果 x>=99,则门将进球
	//)
	private function getGoalsMembers($team,$goals){
		//获取球队的人员组成
		$members_place = array();
		foreach($this->team[$team]['members'] as $k => $v){
			$v['temp_key'] = $k;
			$members_place[$v['place']][] = $v;
		}
		$member_list = array();
		for($i=0;$i<$goals;$i++){
			$x= rand(1,100);
			
			if($x <= 60){
				$member_key = $x%count($members_place['ST']);
				$member = $members_place['ST'][$member_key];
			}elseif($x>60 && $x<90){
				$member_key = $x%count($members_place['M']);
				$member = $members_place['M'][$member_key];
			}elseif($x>=90 && $x<=99){
				$member_key = $x%count($members_place['L']);
				$member = $members_place['L'][$member_key];
			}else{
				$member_key = $x%count($members_place['GK']);
				$member = $members_place['GK'][$member_key];
			}
			$member_list[] = $member['name'];
			//记录进球price及进球数
			$this->team[$team]['members'][$member['temp_key']]['price'] +=10;
			$this->team[$team]['members'][$member['temp_key']]['goals'] +=1;
			$goal_type = rand(0,3);
			$goal_body = rand(0,3);
			$this->match_member_goals[] = array(
				'team_name' =>$team,
				'name'      =>$member['name'],
				'match_year'=>$this->match_year,
				'match_lunch'=>$this->match_lunch,
				'goal_type'  =>$goal_type,
				'goal_body'  =>$goal_body,
				);
		}
		return implode(',',$member_list);
	}

	private function getTeam(){
		$sql = "select * from team order by rand() limit {$this->team_number}";
		$team = $this->query($sql);
		$team_name = array();
		foreach($team as $k=>$v){
			$this->team_hash[$k+1] = $v['team_name'];
			$team_name[] = $v['team_name'];
			$team[$v['team_name']] = $v;
			unset($team[$k]);
		}
		$sql = "select * from team_member where team_name in('".implode("','",$team_name)."') order by member_id asc";
		$members = $this->query($sql);
		
		foreach($members as $v){
			$team[$v['team_name']]['members'][] = $v;
		}
		return $team;
	}

	

	//获取循环对阵列表
	private function getList(){
		$nums = count($this->team);
		//球队临时序列
		$numsSort = array();
		for($i=1;$i<=$nums;$i++){
			$numsSort[] = $i;
		}  

		for($i = $nums/2,$j=1;$i>1;$i--,$j++){
			$tmpArr[] = $numsSort[$i-1];
			$tmpArr[] = $numsSort[$nums-$j-1];
		}

		$result = array();
		for ($i=1;$i<$nums;$i++){
			$r = array();
			$tmp = $numsSort;
			$zd = 1;
			$kd = $nums-($i-1);
			$ii = $i< 10 ? "0".$i : $i;
			//echo 'NO.',$ii,':';
			while ($zd<$kd) {
				// echo $zd,'vs',$kd,'|';
				$r[] = array($zd,$kd);
				unset($tmp[$zd-1]);
				unset($tmp[$kd-1]);
				$zd ++;
				$kd --; 
			}
			if($i!=1){
				//echo $tmpArr[$i-2],'vs',$nums,'|';
				$r[] = array($tmpArr[$i-2],$nums);
				unset($tmp[$tmpArr[$i-2]-1]);
				unset($tmp[$nums-1]);
			}
			//以上规则都不匹配的剩余球队,可以直接使用 头尾元素对战
			if(!empty($tmp)){
				sort($tmp);
				$count = count($tmp);
				for($ci=0;$ci<$count;$ci++){
				  	if($tmp[$ci]<$tmp[$ci+$count/2])
					$r[] = array($tmp[$ci],$tmp[$ci+$count/2]);
				  	//echo $tmp[$ci],'vs',$tmp[$ci+$count/2],'|';  
				}
			}
			$result[] = $r;
			//echo '<br/>';
		}
		return $result;
	}  

	# mysql 处理的几个函数 #
	private function connect(){	
		if($this->link == null){
			$this->link = mysqli_connect( $this->dbConfig['host'],$this->dbConfig['user'], $this->dbConfig['password'],$this->dbConfig['db_name'], $this->dbConfig['db_port']);
			$dbVersion = mysqli_get_server_info($this->link);
			if ($dbVersion >= "4.1") {
				//使用UTF8存取数据库 需要mysql 4.1.0以上支持
				mysqli_query($this->link,"SET NAMES 'utf8'");
			}
			//设置 sql_model
			if($dbVersion >'5.0.1'){
				mysqli_query($this->link,"SET sql_mode=''");
				mysqli_query($this->link,"SET time_zone = '+8:00'");
			}
		}

		return $this;
	}

	private function add($data,$table){
		$keys = array_keys($data);
		$sql = "insert into $table (`".implode('`,`',$keys)."`) values ('".implode("','",$data)."')";
		return $this->query($sql);
	}
	private function update($v,$where,$table){
		$set = array();
		foreach($v as $skey => $svalue){
			$set[] = "`".$skey."` = '{$svalue}'";
		}
		$sql = "update {$table} set ".implode(',',$set).' where '.$where;
		return $this->query($sql);	
	}

	private function findAll(){
		$result = array();
     	while($row = mysqli_fetch_assoc($this->query_id)){
			
            $result[]   =  $row;
        }
		@mysqli_data_seek($this->query_id,0);
		
        return $result;
	}
	private function query($sql){
		
		$this->query_id = mysqli_query($this->link,$sql);
		$this->sqlError[] = [
			'sql'=>$sql,
			'err'=>mysqli_error($this->link)
		];
		return $this->findAll();
	}

	public function getError(){
		dump($this->sqlError);
	}
	public function install(){
		$this->connect();
		$sql = "DROP table if exists `match_member_goals`";
		$this->query($sql);
		$sql = "DROP table if exists `match_detail`";
		$this->query($sql);
		$sql = "DROP table if exists `match_record`";
		$this->query($sql);
		$sql = "DROP table if exists `team`";
		$this->query($sql);
		$sql = "DROP table if exists `team_member`";
		$this->query($sql);
		$sql = "DROP table if exists `team_bak`";
		$this->query($sql);
		$sql = "DROP table if exists `team_member_bak`";
		$this->query($sql);

		$sql = "CREATE TABLE `match_member_goals` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`team_name` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`name` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`match_year` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`match_lunch` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`goal_type` varchar(10) COLLATE utf8mb4_bin DEFAULT NULL,
			`goal_body` varchar(10) COLLATE utf8mb4_bin DEFAULT NULL,
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='match_member_goals';";
		$this->query($sql);
		  
		$sql = "CREATE TABLE `match_detail` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`master_team` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`slave_team` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`master_goal` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`slave_goal` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`match_year` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`match_lunch` varchar(10) COLLATE utf8mb4_bin DEFAULT NULL,
			`master_goal_members` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`slave_goal_members` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='match_detail';";
		$this->query($sql);
		  
		$sql = "CREATE TABLE `match_record` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`team_name` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`win_nums` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`lost_nums` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`eq_nums` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`credit` int(10) COLLATE utf8mb4_bin DEFAULT NULL,
			`goal_in` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`goal_lost` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`match_year` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			PRIMARY KEY (`id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='match_record';";
		$this->query($sql);
		  
		$sql = "CREATE TABLE `team` (
			`team_id` int(11) NOT NULL AUTO_INCREMENT,
			`team_name` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`win` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`eq` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`lost` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`credit` int(10) COLLATE utf8mb4_bin DEFAULT 0,
			`first_nums` int(10) COLLATE utf8mb4_bin DEFAULT 0,
			`goal_nums` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`lostgoal_nums` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`price` int(4) COLLATE utf8mb4_bin DEFAULT 0,
			`country` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			PRIMARY KEY (`team_id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='team';";
		$this->query($sql);
		  
		$sql = "CREATE TABLE `team_member` (
			`member_id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`goals` int(10) COLLATE utf8mb4_bin DEFAULT 0,
			`team_name` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`place` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
			`match_times` varchar(10) COLLATE utf8mb4_bin DEFAULT '',
			`price` int(10) COLLATE utf8mb4_bin DEFAULT 0,
			PRIMARY KEY (`member_id`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='team_member';";
		$this->query($sql);

		$sql = "create table team_bak like team;";
		$this->query($sql);
		$sql = "create table team_member_bak like team_member;";
		$this->query($sql);
		//初始化球队
		$team = [];
		$team[] = array(
			'name'=>'广州恒大-中国-100',
			'members'=>'曾诚,金英权,郑智,张琳芃,孙祥,雷内,廖力生,荣昊,迪亚曼蒂,郜林,埃尔克森'
		);

		$team_list = ['北京国安-中国-90','长春亚泰-中国-70','山东鲁能-中国-89','上海上港-中国-98','广州富力-中国-80','江苏舜天-中国-85',
		'鹿岛鹿角-日本-99','浦和红钻-日本-100','大阪钢巴-日本-89','东京FC-日本-92','川崎前锋-日本-98','名古屋京巴-日本-95',
		'全北现代-韩国-100','浦项制铁-韩国-99','水原三星-韩国-99','首尔FC-韩国-100','釜山现代-韩国-92',
		'春武里-泰国-80','武里南-泰国-85','蒙通联-泰国-89','阿德莱德-澳大利亚-90','西悉尼流浪者-澳大利亚-82',
		'艾因-阿联酋-95','沙特新月-沙特-90','伊蒂哈德-沙特-100','沙特国民-沙特-95','利雅得胜利-沙特-99',
		'塔什干火车头-乌兹别克-90','德黑兰独立-伊朗-99','墨尔本胜利-澳大利亚-80','萨德-卡塔尔-99',
		'伯尔尼年轻人-瑞士-89','布拉格斯拉维亚-捷克-95','阿贾克斯-荷兰-110','萨格勒布迪纳摩-克罗地亚-100',
		'哥本哈根FC-丹麦-99','特拉维夫马卡比-以色列-109','AIK索尔纳-瑞典-99','萨尔兹堡-奥地利-102',
		'切尔西-英超-130','曼联-英超-129','热刺-英超-120','曼城-英超-131','利物浦-英超-129','阿森纳-英超-129',
		'里昂-法甲-125','大巴黎-法甲-129','拜仁-德国-131','法兰克福-德国-110','多特蒙德-德国-129','勒沃库森-德国-120',
		'皇家马德里-西班牙-135','巴萨罗那-西班牙-136','比利亚雷亚尔-西班牙-120','巴伦西亚-西班牙-110','马德里竞技-西班牙-128',
		'AC米兰-意大利-131','国际米兰-意大利-128','尤文图斯-意大利-131','罗马-意大利-120','亚特兰大-意大利-110',
		'莫斯科斯巴达-俄罗斯-110','莫斯科火车头-俄罗斯-119','基辅迪纳摩-乌克兰-120','埃因霍温-荷兰-110','波尔图-葡萄牙-120','里斯本竞技-葡萄牙-119',
		'贝尔格莱德红星-塞尔维亚-119','贝尔格兰德游记-塞尔维亚-125','红牛-奥地利-120','顿涅茨克矿工-乌克兰-118'
		];
		foreach($team_list as $v){
			list($N,$C,$P) = explode('-',$v);
			$item = [];
			$item['name'] = $v;
			for( $i=1;$i<24;$i++){
				$item['members'][] = $N.$i;
			}
			$item['members'] = implode(',',$item['members']);
			$team[] = $item;
		}
		
		$this->formatData($team);
		$sql = "insert into team_bak select * from team;";
		$this->query($sql);
		
		$sql = "insert into team_member_bak select * from team_member;";
		$this->query($sql);
		$this->getError();
	}
	private function formatData($team){
		foreach($team as $v){
			list($tname,$tc,$price) =explode('-',$v['name']);
			$members = explode(',',$v['members']);
			$sql = "insert into team (team_name,country,price) values ('$tname','$tc','$price');";
			$this->query($sql);
			foreach($members as $k=>$v){
				if($k == 0){
					$place = 'GK';		
				}else{
					if($k>0 && $k<5){
						$place = 'L';
					}elseif($k>=5 && $k<= 8){
						$place = 'M';
					}else{
						$place = 'ST';
					}
				}
				$sql = "insert into team_member (team_name,name,place) values('$tname','$v','$place');";
				$this->query($sql);
			}
		}
	}    
}

$foot = new football();
// $foot->install();	//第一次的时候才需要安装
// die();

$foot->init('2000-2001赛季欧亚联赛',20)->reset();
// $foot->init('1990-1991赛季欧亚联赛',20)->start();

// $foot->init('1991-1992赛季欧亚联赛',20)->start();
// $foot->init('1992-1993赛季欧亚联赛',20)->start();
// $foot->init('1993-1994赛季欧亚联赛',20)->start();
// $foot->init('1994-1995赛季欧亚联赛',20)->start();
// $foot->init('1995-1996赛季欧亚联赛',20)->start();
// $foot->init('1996-1997赛季欧亚联赛',20)->start();
// $foot->init('1997-1998赛季欧亚联赛',20)->start();
// $foot->init('1998-1999赛季欧亚联赛',20)->start();
// $foot->init('1999-2000赛季欧亚联赛',20)->start();
$foot->init('2000-2001赛季欧亚联赛',20)->start();
$foot->init('2001-2002赛季欧亚联赛',20)->start();
$foot->init('2002-2003赛季欧亚联赛',20)->start();
$foot->init('2003-2004赛季欧亚联赛',20)->start();
$foot->init('2004-2005赛季欧亚联赛',20)->start();
$foot->init('2005-2006赛季欧亚联赛',20)->start();
$foot->init('2006-2007赛季欧亚联赛',20)->start();
$foot->init('2007-2008赛季欧亚联赛',20)->start();
$foot->init('2008-2009赛季欧亚联赛',20)->start();
$foot->init('2009-2010赛季欧亚联赛',20)->start();
$foot->init('2010-2011赛季欧亚联赛',20)->start();
$foot->init('2011-2012赛季欧亚联赛',20)->start();
$foot->init('2012-2013赛季欧亚联赛',20)->start();
$foot->init('2013-2014赛季欧亚联赛',20)->start();
$foot->init('2014-2015赛季欧亚联赛',20)->start();
$foot->init('2015-2016赛季欧亚联赛',20)->start();

function dump(...$data){
	echo '<pre>';
	foreach($data as $v){
		print_r($v);
	}
	
	echo '</pre>';
}

#下面是杯赛形式的比赛#
//打乱下顺序
shuffle($foot->team_hash);
//第一轮
$first[1] = $foot->getResult(1,2,false);
$first[2] = $foot->getResult(3,4,false);
$first[3] = $foot->getResult(5,6,false);
$first[4] = $foot->getResult(7,8,false);
$first[5] = $foot->getResult(9,10,false);
$first[6] = $foot->getResult(11,12,false);
$first[7] = $foot->getResult(13,14,false);
$first[8] = $foot->getResult(15,16,false);
$second_team = array(
		1=>$first[1][0]>$first[1][1] ? 1 : 2, 
		2=>$first[2][0]>$first[2][1] ? 3 : 4, 
		3=>$first[3][0]>$first[3][1] ? 5 : 6, 
		4=>$first[4][0]>$first[4][1] ? 7 : 8, 
		5=>$first[5][0]>$first[5][1] ? 9 : 10, 
		6=>$first[6][0]>$first[6][1] ? 11 : 12, 
		7=>$first[7][0]>$first[7][1] ? 13 : 14, 
		8=>$first[8][0]>$first[8][1] ? 15 : 16, 
		);
//第二轮
$second[1] = $foot->getResult($second_team[1],$second_team[2],false); 
$second[2] = $foot->getResult($second_team[3],$second_team[4],false);
$second[3] = $foot->getResult($second_team[5],$second_team[6],false);
$second[4] = $foot->getResult($second_team[7],$second_team[8],false);
$third_team = array(
		1=> $second[1][0]>$second[1][1] ? $second_team[1] : $second_team[2],
		2=> $second[2][0]>$second[2][1] ? $second_team[3] : $second_team[4],
		3=> $second[3][0]>$second[3][1] ? $second_team[5] : $second_team[6],
		4=> $second[4][0]>$second[4][1] ? $second_team[7] : $second_team[8],
	);
//第三轮
$third[1] = $foot->getResult($third_team[1],$third_team[2],false);
$third[2] = $foot->getResult($third_team[3],$third_team[4],false);
//决赛
$last_team = array(
		1=> $third[1][0] > $third[1][1] ? $third_team[1] : $third_team[2],
		2=> $third[2][0] > $third[2][1] ? $third_team[3] : $third_team[4],
	);
$last = $foot->getResult($last_team[1],$last_team[2],false);
function showGoal($desc){
	if(!empty($desc)){
		echo '(<font style="font-size:12px;color:#000">'.$desc.'</font>)';	
	}
}
?>
<table style='border:1px solid #ccc;width:100%'>
<tr>
	<td <?php if($first[1][0]>$first[1][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[1];?> &nbsp; <?php echo $first[1][0];?> <br/><?php showGoal($first[1][2]);?></td>
	<td colspan = 7></td>
	<td <?php if($first[5][0]>$first[5][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[9];?> &nbsp; <?php echo $first[5][0];?> <br/><?php showGoal($first[5][2]);?></td>
</tr>
<tr>
	<td></td>
	<td <?php if($second[1][0]>$second[1][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$second_team[1]];?> &nbsp; <?php echo $second[1][0];?> <br/><?php showGoal( $second[1][2]);?></td>
	<td colspan = 5></td>
	<td <?php if($second[3][0]>$second[3][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$second_team[5]];?> &nbsp; <?php echo $second[3][0];?> <br/><?php showGoal( $second[3][2]);?></td>
	<td></td>
</tr>
<tr>
	<td <?php if($first[1][0]<$first[1][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[2];?> &nbsp; <?php echo $first[1][1];?> <br/><?php showGoal($first[1][3]);?></td>
	<td colspan = 7></td>
	<td <?php if($first[5][0]<$first[5][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[10];?> &nbsp; <?php echo $first[5][1];?> <br/><?php showGoal($first[5][3]);?></td>
</tr>
<tr>
	<td colspan = 2></td>
	<td <?php if($third[1][0]>$third[1][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$third_team[1]];?> &nbsp; <?php echo $third[1][0];?><br/> <?php showGoal($third[1][2]);?></td>
	<td colspan = 3> </td>
	<td <?php if($third[2][0]>$third[2][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$third_team[3]];?> &nbsp; <?php echo $third[2][0];?><br/> <?php showGoal($third[2][2]);?></td>
	<td colspan = 2></td>
</tr>
<tr>
	<td <?php if($first[2][0]>$first[2][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[3];?> &nbsp; <?php echo $first[2][0];?><br/> <?php showGoal($first[2][2]);?></td>
	<td colspan = 7></td>
	<td <?php if($first[6][0]>$first[6][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[11];?> &nbsp; <?php echo $first[6][0];?> <br/> <?php showGoal($first[6][2]);?></td>
</tr>
<tr>
	<td></td>
	<td <?php if($second[1][0]<$second[1][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$second_team[2]];?> &nbsp; <?php echo $second[1][1];?><br/> <?php showGoal($second[1][3]);?></td>
	<td colspan = 5></td>
	<td <?php if($second[3][0]<$second[3][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$second_team[6]];?> &nbsp; <?php echo $second[3][1];?><br/> <?php showGoal($second[3][3]);?></td>
	<td></td>
</tr>
<tr>
	<td <?php if($first[2][0]<$first[2][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[4];?> &nbsp; <?php echo $first[2][1];?><br/> <?php showGoal($first[2][3]);?></td>
	<td colspan = 7></td>
	<td <?php if($first[6][0]<$first[6][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[12];?> &nbsp; <?php echo $first[6][1];?><br/> <?php showGoal($first[6][3]);?></td>
</tr>	
<tr>
	<td colspan=4> </td>
	<td> <span <?php if($last[0]>$last[1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[$last_team[1]];?> &nbsp; <?php echo $last[0];?></span>
		VS <span <?php if($last[0]<$last[1]){ echo 'style="color:green"';} ?>><?php echo $last[1];?> &nbsp;<?php echo $foot->team_hash[$last_team[2]];?> <span>
		<br/>
		<?php showGoal($last[2].':'.$last[3]);?>
	</td>
	<td colspan=4 > </td>
</tr>
<tr>
	<td <?php if($first[3][0]>$first[3][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[5];?> &nbsp; <?php echo $first[3][0];?><br/><?php showGoal($first[3][2]);?></td>
	<td colspan = 7></td>
	<td <?php if($first[7][0]>$first[7][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[13];?> &nbsp; <?php echo $first[7][0];?><br/><?php showGoal($first[7][2]);?></td>
</tr>
<tr>
	<td></td>
	<td <?php if($second[2][0]>$second[2][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$second_team[3]];?> &nbsp; <?php echo $second[2][0];?><br/><?php showGoal($second[2][2]);?></td>
	<td colspan = 5></td>
	<td <?php if($second[4][0]>$second[4][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$second_team[7]];?> &nbsp; <?php echo $second[4][0];?><br/><?php showGoal($second[4][2]);?></td>
	<td></td>
</tr>
<tr>
	<td <?php if($first[3][0]<$first[3][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[6];?> &nbsp; <?php echo $first[3][1];?> <br/><?php showGoal($first[3][3]);?></td>
	<td colspan = 7></td>
	<td <?php if($first[7][0]<$first[7][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[14];?> &nbsp; <?php echo $first[7][1];?> <br/><?php showGoal($first[7][3]);?></td>
</tr>
<tr>
	<td colspan = 2></td>
	<td <?php if($third[1][0]<$third[1][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$third_team[2]];?> &nbsp; <?php echo $third[1][1];?><br/><?php showGoal($third[1][3]);?></td>
	<td colspan = 3> </td>
	<td <?php if($third[2][0]<$third[2][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$third_team[4]];?> &nbsp; <?php echo $third[2][1];?><br/><?php showGoal($third[2][3]);?></td>
	<td colspan = 2></td>
</tr>
<tr>
	<td <?php if($first[4][0]>$first[4][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[7];?> &nbsp; <?php echo $first[4][0];?><br/><?php showGoal($first[4][2]);?></td>
	<td colspan = 7></td>
	<td <?php if($first[8][0]>$first[8][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[15];?> &nbsp; <?php echo $first[8][0];?><br/><?php showGoal($first[8][2]);?> </td>
</tr>
<tr>
	<td></td>
	<td <?php if($second[2][0]<$second[2][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$second_team[4]];?> &nbsp; <?php echo $second[2][1];?><br/> <?php showGoal($second[2][3]);?> </td>
	<td colspan = 5></td>
	<td <?php if($second[4][0]<$second[4][1]){ echo 'style="color:green"';} ?> ><?php echo $foot->team_hash[$second_team[8]];?> &nbsp; <?php echo $second[4][1];?><br/> <?php showGoal($second[4][3]);?> </td>
	<td></td>
</tr>
<tr>
	<td <?php if($first[4][0]<$first[4][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[8];?> &nbsp; <?php echo $first[4][1];?><br/> <?php showGoal($first[4][3]);?></td>
	<td colspan = 7></td>
	<td <?php if($first[8][0]<$first[8][1]){ echo 'style="color:green"';} ?>><?php echo $foot->team_hash[16];?> &nbsp; <?php echo $first[8][1];?><br/> <?php showGoal($first[8][3]);?> </td>
</tr>

</table>
