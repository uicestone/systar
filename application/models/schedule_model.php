<?php
class Schedule_model extends SS_Model{
	
	var $id;
	
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$id=intval($id);

		$q_schedule="
			SELECT id,name,content,place,fee,fee_name,`case`,people,time_start,time_end,all_day
			FROM schedule
			WHERE id = $id
				AND display=1
				AND company = {$this->company->id}
		";
		
		$schedule=$this->db->query($q_schedule)->row_array();

		isset($schedule['time_start']) && $schedule['time_start']=date('Y-m-d H:i',$schedule['time_start']);
		isset($schedule['time_end']) && $schedule['time_end']=date('Y-m-d H:i',$schedule['time_end']);
	
		return $schedule;
	}
	
	function addProfile($schedule_id,$name,$content,$comment=NULL){
		$data=array(
			'schedule'=>$schedule_id,
			'name'=>$name,
			'content'=>$content,
			'comment'=>$comment
		);
		
		$data+=uidTime(false);
		
		$this->db->insert('schedule_profile',$data);
		
		return $this->db->insert_id();
	}
	
	/**
	 * 删除日程资料项
	 */
	function removeProfile($schedule_profile_ids){
		$condition = db_implode($schedule_profile_ids, $glue = ' OR ','id');
		$this->db->delete('schedule_profile',$condition);
	}
	
	/**
	 * 返回一个可用的profile name列表
	 */
	function getProfileNames(){
		$query="
			SELECT name,COUNT(*) AS hits
			FROM `schedule_profile`
			GROUP BY name
			ORDER BY hits DESC;
		";
		
		$result=$this->db->query($query)->result_array();
		
		return array_sub($result,'name');
	}
	
	/**
	 * 获得一个时间范围内的多个日程
	 * @param $start 开始时间戳
	 * @param $end 结束时间戳
	 * @param $staff
	 * @param $case
	 * @return array
	 */
	function fetch_range($start,$end,&$staff,&$case){
	
		$q_calendar="SELECT * FROM schedule WHERE display=1 AND time_start>='".intval($start)."' AND time_start<'".intval($end)."'";
		
		if($staff){
			$q_calendar.=" AND `uid`='".intval($staff)."'";
		}else{
			$q_calendar.=" AND `uid`={$this->user->id}";
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
	
	/**
	 * 根据post提交的数组，将日志标记为已审核，审核时间为自报时间
	 */
	function review($post){
		$post=array_trim($post);
		if($post){
			$condition = db_implode($post, $glue = ' OR ','id',' = ',"'","'", '`','key');
			$this->db->query("UPDATE schedule SET `hours_checked` = `hours_own` WHERE ".$condition);
			return true;
		}
	}
	
	function setComment($schedule_id,$comment){
		$schedule_id=intval($schedule_id);
		$this->db->update('schedule',array('comment'=>$comment),"id = '".$schedule_id."'");
		return db_fetch_first("SELECT * FROM schedule WHERE id='".$schedule_id."'");
	}
	
	function check_hours($schedule_id,$hours_checked){
		$schedule_id=intval($schedule_id);
		$this->db->update('schedule',array('hours_checked'=>$hours_checked),"id = '".$schedule_id."'");
		return db_fetch_field("SELECT hours_checked FROM schedule WHERE id='".$schedule_id."'");
	}
	
	function add($data){
		//插入一条日程，返回插入的id
		
		isset($data['fee']) && $data['fee'] = (int)$data['fee'];

		if(isset($data['time_start']) && isset($data['time_end'])){
			$data['hours_own'] = round(($data['time_end']-$data['time_start'])/3600,2);
		}

		$data['display']=1;
		$data+=uidTime(true,true);

		if($this->db->insert('schedule',$data)){
			return $this->db->insert_id();
		}else{
			return false;
		}
		
	}
	
	function delete($schedule_id){
		return $this->db->delete('schedule',array('id'=>intval($schedule_id),'uid'=>$this->user->id));	
	}
	
	function update($schedule_id,$data){
		$schedule_id=intval($schedule_id);
		
		return $this->db->update('schedule',$data,array('id'=>$schedule_id));
	}
	
	/**
	 * 调整calendar页面的日程时长
	 */
	function resize($schedule_id,$time_delta){
		$hours_delta=$time_delta/3600;
		return $this->db->query("UPDATE schedule SET `hours_own` = `hours_own`+'{$hours_delta}', `time_end`=`time_end`+'{$time_delta}' WHERE id='{$schedule_id}'");
	}
	
	/**
	 * 调整calendar页面的日程开始时间
	 */
	function drag($schedule_id,$seconds_delta,$all_day){
		$schedule_id=intval($schedule_id);
		$seconds_delta=intval($seconds_delta);
		$all_day=(int)(bool)$all_day;
		
		$query="
			UPDATE schedule 
			SET `time_start` = `time_start`+$seconds_delta,
				`time_end`=`time_end`+ $seconds_delta,
				all_day = $all_day
			WHERE id= $schedule_id
		";
		
		return $this->db->query($query);
	}
	
	function calculateTime($case,$client=NULL,$staff=NULL){
		$q="SELECT SUM(IF(hours_checked IS NULL,hours_own,hours_checked)) AS time FROM schedule WHERE display=1 AND completed=1 AND `case`='{$case}'";
		
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
				if(MAX(case_people.role)='督办人',1,0) AS review_permission
		
				#imperfect 2012/7/13 MAX ENUM排序依据为字符串，并非INDEX
		
			FROM schedule INNER JOIN `case` ON schedule.case=case.id
				INNER JOIN case_people ON case.id=case_people.case AND case_people.type='律师'
				LEFT JOIN people staff ON staff.id = schedule.uid
			WHERE case_people.people={$this->user->id}
				AND schedule.display=1 AND schedule.completed=".($this->input->get('plan')?'0':'1')."
		";
		
		$condition='';
		if($para=='mine'){
			$condition.=" AND schedule.`uid`={$this->user->id}";
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
		$q=$this->search($q,array('case.name'=>'案件','staff.name'=>'人员'));
		$q=$this->dateRange($q,'time_start');
		$q.="
			GROUP BY schedule.id
			ORDER BY FROM_UNIXTIME(time_start,'%Y%m%d') ".($this->input->get('plan')?'ASC':'DESC').",schedule.uid,time_start ".($this->input->get('plan')?'ASC':'DESC')."
		";

		$q=$this->pagination($q);

		return $this->db->query($q)->result_array();
	}
	
	function getOutPlanList(){
		
		$q="
			SELECT
				schedule.id AS schedule,schedule.name AS schedule_name,schedule.content AS schedule_content,schedule.experience AS schedule_experience, schedule.time_start,schedule.hours_own,schedule.hours_checked,schedule.comment AS schedule_comment,schedule.place,
				staff.name AS staff_name,staff.id AS staff
			FROM schedule LEFT JOIN people staff ON staff.id = schedule.uid
			WHERE schedule.display=1 AND schedule.place<>''
		";
		
		if($this->input->get('case') && $this->input->get('staff')){
			$q.=" AND schedule.`case`='".$this->input->get('case')."' AND uid='".$this->input->get('staff')."'";
		
		}elseif($this->input->get('case')){
			$q.=" AND schedule.`case`='".$this->input->get('case')."'";
		
		}elseif($this->input->get('staff')){
			$q.=" AND schedule.`uid`='".$this->input->get('staff')."'";
		
		}
		
		$q=$this->search($q,array('staff.name'=>'人员'));
		
		$q=$this->orderby($q,'time_start','DESC',array('place'));
		
		$q=$this->pagination($q);
		
		return $this->db->query($q)->result_array();
	}
	
	/**
	 * 获得上周和上上周每个员工的工作时间数据，用于生成HighCharts条形统计图
	 */
	function getStafflyWorkHours(){
		if(date('w')==1){//今天是星期一
			$last_week_monday=strtotime("-1 Week Monday");
		}else{
			$last_week_monday=strtotime("-2 Week Monday");
		}

		$query="
			SELECT staff.name AS staff_name,lastweek.hours AS lastweek,last2week.hours AS last2week
			FROM staff INNER JOIN (
				SELECT uid,SUM(IF(schedule.hours_checked IS NULL,schedule.hours_own,schedule.hours_checked)) AS hours
				FROM schedule
				WHERE completed=1 AND schedule.time_start >= '".$last_week_monday."' AND schedule.time_start < '".($last_week_monday+86400*7)."'
				GROUP BY uid
			)lastweek ON staff.id=lastweek.uid
			LEFT JOIN (
				SELECT uid,SUM(IF(schedule.hours_checked IS NULL,schedule.hours_own,schedule.hours_checked)) AS hours
				FROM schedule
				WHERE completed=1 AND schedule.time_start >= '".($last_week_monday-86400*7)."' AND schedule.time_start < '".$last_week_monday."'
				GROUP BY uid
			)last2week ON staff.id=last2week.uid
			ORDER BY lastweek DESC"
		;
		
		return $this->db->query($query)->result_array();
	}
	
	function getStafflyWorkHoursList(){
		$query="
			SELECT staff.name AS staff_name,SUM(IF(hours_checked IS NULL,hours_own,hours_checked)) AS sum,
				ROUND(SUM(IF(hours_checked IS NULL,hours_own,hours_checked))/".(getWorkingDays(option('date_range/from'),option('date_range/to'),getHolidays(),getOvertimedays(),false)).",2) AS avg
			FROM schedule INNER JOIN people staff ON staff.id=schedule.uid
			WHERE completed=1 AND schedule.display=1
		";

		$query=$this->dateRange($query, 'time_start' ,true);

		$query.="	GROUP BY schedule.uid
		";

		$query=$this->orderBy($query,'sum','DESC');

		return $this->db->query($query)->result_array();
	}
	
	function getTaskBoardSort($uid)
	{
		$query = $this -> db -> query("SELECT sort_data FROM schedule_taskboard WHERE uid='{$uid}'");
		
		if($query -> num_rows() == 0)	//若查询结果为空
		{
			return array();
		}
		else
		{
			$row = $query->row_array();
			return json_decode($row['sort_data']);
		}
	}
	
	function setTaskBoardSort($sort_data , $uid)
	{
		$data['sort_data'] = $sort_data;
		$this -> db -> update('schedule_taskboard' , $data , array('uid'=>$uid));
	}
	
	function createTaskBoard($sort_data ,$uid)
	{
		//$data['id'] = "NULL";这是？
		$data['sort_data'] = $sort_data;
		$data['uid'] = $uid;
		
		$this -> db -> insert('schedule_taskboard' , $data);
	}
}
?>