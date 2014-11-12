<?php
  /**
   * PHP 日历程序 类
   */
  class calendar{
    public $year,$month,$day;
    function calendar(){
      $this->__construct();
    }
    function __construct(){
      $this->year=isset($_POST['year'])?$_POST['year']:date('Y');
      $this->month=isset($_POST['month'])?$_POST['month']:date('m');
      $this->day=date('d');
    }
    function getWeek($year,$month,$day){
      $week=date("w",mktime(0,0,0,$month,$day,$year));
      return $week;
    }
    /**
     * @desc 输出日历
     *
     */
    function out(){
      $week=$this->getWeek($this->year,$this->month,$this->day);//今天星期
      $fweek=$this->getWeek($this->year,$this->month,1);      //本月首日星期  
      $html=$this->formatout($this->year,$this->month,$this->day,$week,$fweek);
      echo $html;
    }
    /**
     * @desc 格式化输出日历
     *
     */
    function formatout($year,$month,$day,$week,$fweek){
      $date=$year.'-'.$month.'-'.$day;
      $monthday=date('t',strtotime($date));
      $lastmonth=strtotime('-1 month',strtotime($date));
      $nextmonth=strtotime('+1 month',strtotime($date));
      $lastmonthday=date('t',$lastmonth);  //上个月天数
      $html="<table class='calendar_table'><tr><td colspan='7' class='calendar_th'><a onclick='prevYear($month,$year)'><</a>{$year}<a onclick='nextYear($month,$year)'>></a> <a onclick='prevMonth($month,$year)'><</a>{$month}<a onclick='nextMonth($month,$year)'>></a></td></tr><tr><td class='calendar_td_0'>日</td><td class='calendar_td_1'>一</td><td class='calendar_td_2'>二</td><td class='calendar_td_3'>三</td><td class='calendar_td_4'>四</td><td class='calendar_td_5'>五</td><td class='calendar_td_6'>六</td></tr><tr>";
      $j=$c=1;
      for($i=1;$i<=$monthday+$fweek;$i++){
        if($j>7){
          $j=2;
          $html.='</tr><tr>';
        }else{
          $j++;
        }
        if($year==date('Y') && $month==date('m') && $day==date('d') && $c==$day){
          $class="class='calendar_td_now'";
        }else{
          $class="class='calendar_td_".($j-2)."'";
        }
        if($fweek<$i){
          $html.="<td><a $class onclick=gotoday($year,$month,$c)>{$c}</a></td>";
          $c++;
        }else{//添头
          $html.="<td><a class='calendar_td_else' onclick=gotoday(".date('Y',$lastmonth).",".date('m',$lastmonth).",".($lastmonthday-$fweek+$i).")>".($lastmonthday-$fweek+$i)."</a></td>";
        }
      }
      if($j>=2 && $j<8){  //补尾
        for($x=1;$x<=(8-$j);$x++){
          $html.="<td><a class='calendar_td_else' onclick=gotoday(".date('Y',$nextmonth).",".date('m',$nextmonth).",".$x.")>".$x."</a></td>";        
        }
      }
      $html.='</tr></table>';
      return $html;
    }
  }
  $cal=new calendar();
  $cal->out();
?>