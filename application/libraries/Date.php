<?php
class SS_Date{
	
	var $db;
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
		
		$CI=&get_instance();
		
		$this->db=$CI->load->database('',true);
		
		$this->now=time();
		$this->microtime=microtime(true);
		$this->today=date('Y-m-d',time());
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
	
	/**
	 * 返回两个日期之间工作日的天数
	 * @param string/int $start_date date syntax or timestamp
	 * @param string/int  $end_date date syntax or timestamp
	 * @param array $holidays array of date syntaxes
	 * @param array $overtimedays  array of date syntaxes
	 * @param bool $timestamp_input $start_date and $end_date input as timestamp, default false
	 * @return int
	 */
	function workingDays($start_date, $end_date, $timestamp_input = false) {
		// do strtotime calculations just once
		if (!$timestamp_input) {
			$end_date = strtotime($end_date);
			$start_date = strtotime($start_date);
		}
		//The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
		//We add one to inlude both dates in the interval.
		$days = floor(($end_date - $start_date) / 86400) + 1;

		$no_full_weeks = floor($days / 7);
		$no_remaining_days = fmod($days, 7);

		//It will return 1 if it's Monday,.. ,7 for Sunday
		$the_first_day_of_week = date("N", $start_date);
		$the_last_day_of_week = date("N", $end_date);

		//---->The two can be equal in leap years when february has 29 days, the equal sign is added here
		//In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
		if ($the_first_day_of_week <= $the_last_day_of_week) {
			if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week)
				$no_remaining_days--;
			if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week)
				$no_remaining_days--;
		} else {
			// (edit by Tokes to fix an edge case where the start day was a Sunday
			// and the end day was NOT a Saturday)

			// the day of the week for start is later than the day of the week for end
			if ($the_first_day_of_week == 7) {
				// if the start date is a Sunday, then we definitely subtract 1 day
				$no_remaining_days--;

				if ($the_last_day_of_week == 6) {
					// if the end date is a Saturday, then we subtract another day
					$no_remaining_days--;
				}
			} else {
				// the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
				// so we skip an entire weekend and subtract 2 days
				$no_remaining_days -= 2;
			}
		}

		//The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
		//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
		$workingDays = $no_full_weeks * 5;
		if ($no_remaining_days > 0) {
			$workingDays += $no_remaining_days;
		}
		
		$holidays = $this->holidays();
		
		$overtimedays = $this->overtimedays();

		//We subtract the holidays
		foreach ($holidays as $holiday) {
			$time_stamp = strtotime($holiday);
			//If the holiday doesn't fall in weekend
			if ($start_date <= $time_stamp && $time_stamp <= $end_date && date("N", $time_stamp) != 6 && date("N", $time_stamp) != 7)
				$workingDays--;
		}

		foreach ($overtimedays as $overtimeday) {
			$time_stamp = strtotime($overtimeday);
			//If the holiday doesn't fall in weekend
			if ($start_date <= $time_stamp && $time_stamp <= $end_date)
				$workingDays++;
		}

		return $workingDays;
	}
	
	/**
	 * 返回两个工作日之间假日的数组
	 * @param NULL/int $people
	 * @return array
	 */
	function holidays($people=NULL){
		$this->db->select('date')
			->from('holidays')
			->where('is_overtime',false)
			->where('staff',$people);
		return array_sub($this->db->get()->result_array(),'date');
	}

	/**
	 * 返回两个工作日之间加班日的数组
	 * @param NULL/int $people
	 * @return array
	 */
	function overtimedays($people=NULL){
		$this->db->select('date')
			->from('holidays')
			->where('is_overtime',true)
			->where('staff',$people);
		return array_sub($this->db->get()->result_array(),'date');
	}
}
?>
