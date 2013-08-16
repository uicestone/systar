<?php
class SS_Date{
	
	var $now;
	var $microtime;
	var $today;
	var $quarter;
	var $year;
	var $year_begin;
	var $year_end;
	var $last_year_end;
	var $month_begin;
	var $month_end;
	var $week_begin;
	
	function __construct() {
		$this->now=time();
		$this->microtime=microtime(true);
		$this->today=date('Y-m-d');
		$this->quarter=date('y',$this->now.ceil(date('m',$this->now/3)));
		$this->year=date('Y',$this->now);
		$this->year_begin=date('Y',$this->now).'-01-01';
		$this->year_end=date('Y',$this->now).'-12-31';
		$this->last_year_end=(date('Y',$this->now)-1).'-12-31';
		$this->month_begin=date('Y-m',$this->now).'-01';
		$this->month_end=date('Y-m-d',strtotime($this->month_begin.' +1 month -1 day'));
		
		if(date('w')==1){//今天是星期一
			$this->week_begin=$this->today;
		}else{
			$this->week_begin=date('Y-m-d',strtotime("-1 Week Monday"));
		}
	}
}
?>
