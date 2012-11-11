<?php
class Schedule_model extends SS_Model{
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
	
	function reviewSelected(){
		$_POST=array_trim($_POST);
		if($this->input->post('schedule_check')){
			$condition = db_implode($this->input->post('schedule_check'), $glue = ' OR ','id','=',"'","'", '`','key');
			db_update('schedule',array('hours_checked'=>'#hours_own#'),$condition);
		}
	}
	
	function setComment($schedule_id,$comment){
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
		$q="SELECT SUM(IF(hours_checked IS NULL,hours_own,hours_checked)) AS time FROM schedule WHERE `case`='".$case."'";
		
		if(!is_null($client)){
			$q.=" `client`='".$client."'";
		}
		
		if(!is_null($staff)){
			$q.=" `uid`='".$staff."'";
		}
		
		return db_fetch_field($q);
	}
	
	function getList($para=NULL){
		$q="
			SELECT
				schedule.id,schedule.name,schedule.content,schedule.experience, schedule.time_start,schedule.hours_own,schedule.hours_checked,schedule.comment,schedule.place,
				case.id AS `case`,case.name AS case_name,
				staff.name AS staff_name,staff.id AS staff,
				if(MAX(case_lawyer.role)='督办合伙人',1,0) AS review_permission
		
				#imperfect 2012/7/13 MAX ENUM排序依据为字符串，并非INDEX
		
			FROM schedule INNER JOIN `case` ON schedule.case=case.id
				INNER JOIN case_lawyer ON case.id=case_lawyer.case
				LEFT JOIN staff ON staff.id = schedule.uid
			WHERE case_lawyer.lawyer='".$_SESSION['id']."'
				AND schedule.display=1 AND schedule.completed=".($this->input->get('plan')?'0':'1')."
		";
		
		$condition='';
		if($para=='mine'){
			$condition.=" AND schedule.`uid`='".$_SESSION['id']."'";
		}else{
			if($this->input->get('staff')){
				$condition.=" AND schedule.`uid`='".intval($this->input->get('staff'))."'";
			}
		}

		if($this->input->get('case')){
			$condition.=" AND schedule.`case`='".intval($this->input->get('case'))."'";
		}
			
		if($this->input->get('client')){
			$condition.=" AND schedule.client='".intval($this->input->get('client'))."'";
		}
									
		$q.=$condition;
		$this->session->set_userdata('last_list_action',$_SERVER['REQUEST_URI']);
		$q=$this->search($q,array('case.name'=>'案件','staff.name'=>'人员'));
		$q=$this->dateRange($q,'time_start');
		$q.="
			GROUP BY schedule.id
			ORDER BY FROM_UNIXTIME(time_start,'%Y%m%d') ".($this->input->get('plan')?'ASC':'DESC').",schedule.uid,time_start ".($this->input->get('plan')?'ASC':'DESC')."
		";
		if(!$this->input->post('export')){
		    $q=$this->pagination($q);
		}

		return $this->db->query($q)->result_array();
	}
}
?>