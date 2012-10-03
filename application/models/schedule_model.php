<?php
class Schedule_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM schedule WHERE id='".$id."'";
		return db_fetch_first($query,true);
	}
	
	function fetch_single($id){
		$q_schedule="
			SELECT schedule.id,schedule.name,schedule.content,schedule.experience,schedule.time_start,schedule.time_end,schedule.hours_own,
				client.abbreviation AS client_name,client.id AS client,schedule.place,schedule.fee,schedule.fee_name,schedule.completed,
				case.name AS case_name,case.id AS `case`
			FROM schedule
				LEFT JOIN `case` ON case.id=schedule.case
				LEFT JOIN client ON client.id=schedule.client
			WHERE schedule.id='".intval($id)."'";
		$schedule=db_fetch_first($q_schedule);
		$schedule['content_paras']=explode("\n",$schedule['content']);
		$schedule['experience_paras']=explode("\n",$schedule['experience']);
		$schedule['time_start']=date('Y-m-d H:i',$schedule['time_start']);
		$schedule['time_end']=date('Y-m-d H:i',$schedule['time_end']);
	
		return $schedule;
	}
	
	function fetch_range($start,$end,&$staff,&$case){
	
		$q_calendar="SELECT * FROM schedule WHERE display=1 AND time_start>='".intval($start)."' AND time_start<'".intval($end)."'";
		
		if($staff){
			$q_calendar.=" AND `uid`='".intval($staff)."'";
		}else{
			$q_calendar.=" AND `uid`='".$_SESSION['id']."'";
		}
		
		if($case){
			$q_calendar.=" AND `case`='".intval($case)."'";
		}
	
		$calendar=db_toArray($q_calendar);
		
		$scheduleArray=array();
		foreach($calendar as $order => $schedule){
			$scheduleArray[$order]=array(
				'id'=>$schedule['id'],
				'title'=>$schedule['name'],
				'start'=>date('Y-m-d H:i',$schedule['time_start']),
				'end'=>date('Y-m-d H:i',$schedule['time_end']),
				'allDay'=>(bool)$schedule['all_day'],
				'color'=>($schedule['time_start']>$this->config->item('timestamp')?'#E35B00':($schedule['completed']?'#36C':'#555'))
			);
		}
	
		return $scheduleArray;
	}
	
	function review_selected(){
		$_POST=array_trim($_POST);
		if(isset($_POST['schedule_check'])){
			$condition = db_implode($_POST['schedule_check'], $glue = ' OR ','id','=',"'","'", '`','key');
			db_update('schedule',array('hours_checked'=>'#hours_own#'),$condition);
		}
	}
	
	function set_comment($schedule_id,$comment){
		$schedule_id=intval($schedule_id);
		db_update('schedule',array('comment'=>$comment),"id='".$schedule_id."'");
		return db_fetch_first("SELECT * FROM schedule WHERE id='".$schedule_id."'");
	}
	
	function check_hours($schedule_id,$hours_checked){
		$schedule_id=intval($schedule_id);
		db_update('schedule',array('hours_checked'=>$hours_checked),"id='".$schedule_id."'");
		return db_fetch_field("SELECT hours_checked FROM schedule WHERE id='".$schedule_id."'");
	}
	
	function add($data){
		//插入一条日程，返回插入的id
		$data['fee'] = (int)$data['fee'];
		$data['hours_own'] = round(($data['time_end']-$data['time_start'])/3600,2);
		$data['display']=1;
		$data+=uidTime();
		return db_insert('schedule',$data);
	}
	
	function delete($schedule_id){
		db_delete('schedule',"id='".intval($schedule_id)."' AND uid='".$_SESSION['id']."'");	
	}
	
	function update($schedule_id,$data){
		db_update('schedule',$data,"id='".intval($schedule_id)."'");
	}
	
	function calculateTime($case,$client=NULL,$staff=NULL){
		$q="SELECT SUM(IF(hours_checked IS NULL,0,hours_checked)) AS time FROM schedule WHERE `case`='".$case."'";
		
		if(!is_null($client)){
			$q.=" `client`='".$client."'";
		}
		
		if(!is_null($staff)){
			$q.=" `uid`='".$staff."'";
		}
		
		return db_fetch_field($q);
	}
}
?>