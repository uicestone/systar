<?php
class Schedule_model extends BaseItem_model{
	
	static $fields=array(
		'name'=>'标题',
		'content'=>'内容',
		'time_start'=>'开始时间',
		'time_end'=>'结束时间',
		'hours_own'=>'自报时长',
		'hours_checked'=>'核准时长',
		'hours_bill'=>'账单时长',
		'all_day'=>'全天',
		'completed'=>'已完成',
		'project'=>'关联案件'
	);
	
	function __construct(){
		parent::__construct();
		$this->table='schedule';
	}

	function fetch($id,$field=NULL){
		$schedule=parent::fetch($id,$field);
		
		if(is_null($field)){
			isset($schedule['time_start']) && $schedule['time_start']=date('Y-m-d H:i',$schedule['time_start']);
			isset($schedule['time_end']) && $schedule['time_end']=date('Y-m-d H:i',$schedule['time_end']);
		}
		
		return $schedule;
	}
	
	function getList($args=array()){
		
		if(isset($args['project'])){
			$this->db->where('schedule.project',$args['project']);
		}
		
		if(isset($args['id_in_set'])){
			if(!$args['id_in_set']){
				return array();
			}
			$this->db->where_in('schedule.id', $args['id_in_set'])
				->order_by("FIELD(schedule.id, ".implode(', ',$args['id_in_set']).")",'',false);
			$args['orderby']=false;
		}
		
		if(isset($args['completed'])){
			$this->db->where('schedule.completed',$args['completed']);
		}
		
		return parent::getList($args);
	}
	
	/**
	 * 插入一条日程，返回插入的id
	 */
	function add(array $data=array()){
		
		if(isset($data['people'])){
			$people=$data['people'];
		}
		
		$data=array_intersect_key($data, self::$fields);
		
		if(isset($data['time_start']) && isset($data['time_end'])){
			$data['hours_own'] = round(($data['time_end']-$data['time_start'])/3600,2);
		}

		$data['display']=1;
		$data+=uidTime(true,true);
		
		$this->db->insert('schedule',$data);
		$schedule_id=$this->db->insert_id();
		
		if(isset($people)){
			$this->addPeople($schedule_id,$people);
		}
		
		return $schedule_id;
	}
	
	function update($schedule_id,$data){
		$schedule_id=intval($schedule_id);

		$data=array_intersect_key($data, self::$fields);
		
		return $this->db->update('schedule',$data,array('id'=>$schedule_id));
	}
	
	function remove($schedule_id){
		$schedule_id=intval($schedule_id);
		
		return $this->db->update('schedule',array('display'=>false),array('id'=>$schedule_id));	
	}
	
	function addPeople($schedule_id,$people){
		$schedule_id=intval($schedule_id);
		
		if(is_array($people)){
			$set=array();
			foreach($people as $person){
				$set[]=array('people'=>$person,'schedule'=>$schedule_id);
			}
			return $this->db->insert_batch('schedule_people',$set);
		}elseif($people){
			return $this->db->insert('schedule_people',array('people'=>intval($people),'schedule'=>$schedule_id));
		}
	}
	
	function getPeople($schedule_id){
		$schedule_id=intval($schedule_id);
		return array_sub($this->db->get_where('schedule_people',array('schedule'=>$schedule_id))->result_array(),'people');
	}
	
	function removePeople($schedule_id){
		$schedule_id=intval($schedule_id);
		return $this->db->delete('schedule_people',array('schedule'=>$schedule_id));
	}
	
	/**
	 * 获得一个时间范围内的多个日程
	 * @param $start 开始时间戳
	 * @param $end 结束时间戳
	 * @param $staff
	 * @param $project
	 * @return array
	 */
	function fetch_range($start,$end,&$staff,&$project){
		$start=intval($start);
		$end=intval($end);
	
		if($staff){
			$people=intval($staff);
		}else{
			$people=$this->user->id;
		}
		
		$q_calendar="
			SELECT * 
			FROM schedule
			WHERE company = {$this->company->id} AND display = 1 
				AND time_start>=$start AND time_start<$end
				AND (uid = $people OR id IN (SELECT schedule FROM schedule_people WHERE people = $people))
		";
		
		if($project){
			$project=intval($project);
			$q_calendar.=" AND `project` = $project";
		}
	
		$calendar=$this->db->query($q_calendar)->result_array();
		
		$scheduleArray=array();
		foreach($calendar as $order => $schedule){
			
			if($schedule['completed']){
				$schedule['color']='#36C';
			}else{
				if($schedule['time_start']<$this->date->now){
					$schedule['color']='#555';
				}else{
					$schedule['color']='#E35B00';
				}
			}

			$scheduleArray[$order]=array(
				'id'=>$schedule['id'],
				'title'=>$schedule['name'],
				'start'=>date('Y-m-d H:i',$schedule['time_start']),
				'end'=>date('Y-m-d H:i',$schedule['time_end']),
				'allDay'=>(bool)$schedule['all_day'],
				'completed'=>(bool)$schedule['completed'],
				'color'=>$schedule['color']
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
		return $this->db->query("SELECT * FROM schedule WHERE id='".$schedule_id."'")->row_array();
	}
	
	function check_hours($schedule_id,$hours_checked){
		$schedule_id=intval($schedule_id);
		$this->db->update('schedule',array('hours_checked'=>$hours_checked),"id = '".$schedule_id."'");
		return true;
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
	
	function calculateTime($project,$client=NULL,$staff=NULL){
		$q="SELECT SUM(IF(hours_checked IS NULL,hours_own,hours_checked)) AS time FROM schedule WHERE display=1 AND completed=1 AND `project`='{$project}'";
		
		if(!is_null($client)){
			$q.=" `client`='".$client."'";
		}
		
		if(!is_null($staff)){
			$q.=" `uid`='".$staff."'";
		}
		
		return $this->db->query($q)->row()->time;
	}
	
	/**
	 * 计算特定职员在特定案件上所消耗的时间
	 * @param $project_id 接受一个项目的id，或一组项目id构成的数组
	 * @param $people_id 接受一个人员的id，或一组人员id构成的数组
	 * @param $team_id 接受一个人员组的id，或一组人员组id构成的数组
	 * @return type
	 */
	function timeSpent($project_id=NULL,$people_id=NULL,$team_id=NULL){
		//@TODO 现在仍用schedule.uid来判断相关人员，应该使用schedule_people
		$q="
			SELECT SUM(IF(hours_checked IS NULL,hours_own,hours_checked)) AS time 
			FROM schedule 
			WHERE company={$this->company->id} AND display=1 AND completed=1
		";
			
		if(isset($project_id)){
			if(is_array($project_id)){
				$project_ids=implode(',',$project_id);
				$q.=" AND schedule.project IN ($project_ids)";
			}else{
				$project_id=intval($project_id);
				$q.=" AND schedule.project = $project_id";
			}
		}
		
		if(isset($people_id)){
			if(is_array($people_id)){
				$people_ids=implode(',',$people_id);
				$q.=" AND schedule.uid IN ($people_ids)";
			}else{
				$people_id=intval($people_id);
				$q.=" AND schedule.uid = $people_id";
			}
		}
		
		if(isset($team_id)){
			if(is_array($team_id)){
				$team_ids=implode(',',$team_id);
				$q.=" AND schedule.uid IN (
					SELECT people FROM team_people WHERE team IN ($team_ids)
				)";
			}else{
				$team_id=intval($team_id);
				$q.=" AND schedule.uid = IN (
					SELECT people FROM team_people WHERE team = $team_id
				)";
			}
		}
		
		return $this->db->query($q)->row()->time;
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
		$uid=intval($uid);
		
		$query = $this -> db -> query("SELECT sort_data FROM schedule_taskboard WHERE uid=$uid");
		
		if($query -> num_rows() == 0)	//若查询结果为空则先插入
		{
			$sort_data=array_fill(0,6,array());
			$this->createTaskBoard($sort_data, $uid);
			return $sort_data;
		}
		else
		{
			$row = $query->row_array();
			return json_decode($row['sort_data']);
		}
	}
	
	function setTaskBoardSort($sort_data , $uid)
	{
		$uid=intval($uid);
		$data=array(
			'sort_data' => json_encode($sort_data),
			'time' => $this->date->now
		);
		$this -> db -> update('schedule_taskboard' , $data , array('uid'=>$uid));
	}
	
	function createTaskBoard($sort_data ,$uid){
		
		$data=array(
			'sort_data'=>json_encode($sort_data),
			'uid'=>$uid,
			'time'=>$this->date->now
		);

		$this -> db -> insert('schedule_taskboard' , $data);
	}
	
}
?>