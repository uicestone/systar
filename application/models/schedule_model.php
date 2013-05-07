<?php
class Schedule_model extends BaseItem_model{
	
	static $fields=array(
		'name'=>'标题',
		'content'=>'内容',
		'start'=>'开始时间',
		'end'=>'结束时间',
		'deadline'=>'截止日期',
		'in_todo_list'=>'在任务列表中',
		'hours_own'=>'自报时长',
		'hours_checked'=>'核准时长',
		'hours_bill'=>'账单时长',
		'all_day'=>'全天',
		'completed'=>'已完成',
		'project'=>'关联案件',
		'display'=>'显示'
	);
	
	function __construct(){
		parent::__construct();
		$this->table='schedule';
	}

	/**
	 * start & end time of schedule returned in Y-m-d H:i format
	 * @param int $id
	 * @param string $field
	 * @return mixed $schedule array, or a specific field of it.
	 */
	function fetch($id,$field=NULL){
		$schedule=parent::fetch($id,$field);
		
		if(is_null($field)){
			isset($schedule['start']) && $schedule['start']=date('Y-m-d H:i',$schedule['start']);
			isset($schedule['end']) && $schedule['end']=date('Y-m-d H:i',$schedule['end']);
			isset($schedule['deadline']) && $schedule['deadline']=date('Y-m-d H:i',$schedule['deadline']);
		}
		
		return $schedule;
	}
	
	/**
	 * 
	 * @param array $args
	 * project: get schedule only under this project
	 * people: get schedule related with this people (by schedule.uid and schedule_people)
	 * show_creater
	 * id_in_set
	 * in_todo_list
	 * completed
	 * time array or boolean
	 *	false
	 *	array(
	 *		from=>timestamp/date string/datetime string
	 *		to=>timestamp/date string/datetime string
	 *		format=>mysql date form string, or false (default: '%Y-%m-%d')
	 *	)
	 * in_project_of_people bool
	 * show_project
	 * @return type
	 */
	function getList(array $args=array()){
		
		$this->db->select('schedule.*');
		
		if(isset($args['project'])){
			$this->db->where('schedule.project',$args['project']);
		}
		
		if(isset($args['people'])){
			$this->db->where("
				(
					schedule.uid = {$args['people']} 
					OR 
					schedule.id IN (
						SELECT schedule FROM schedule_people WHERE people = {$args['people']}
					)
				)
			",NULL,FALSE);
		}
		
		if(isset($args['show_creater']) && $args['show_creater']){
			$this->db->join('people creater','creater.id = schedule.uid','inner')
				->select('creater.id creater, creater.name creater_name');
		}
		
		if(isset($args['id_in_set'])){
			if(!$args['id_in_set']){
				return array();
			}
			$this->db->where_in('schedule.id', $args['id_in_set'])
				->order_by("FIELD(schedule.id, ".implode(', ',$args['id_in_set']).")",'',false);
			$args['orderby']=false;
		}
		
		if(isset($args['in_todo_list'])){
			$this->db->where('schedule.in_todo_list',$args['in_todo_list']);
		}
		
		if(isset($args['completed'])){
			$this->db->where('schedule.completed',$args['completed']);
		}
		
		if(isset($args['time'])){
			if($args['time']===false){
				$this->db->where(array('start'=>NULL,'end'=>NULL));
			}
			
			if(isset($args['time']['from'])){
				if(isset($args['time']['input_format']) && $args['time']['input_format']!=='timestamp'){
					$args['time']['from']=strtotime($args['time']['from']);
				}
				$this->db->where('start >=',$args['time']['from']);
			}
			
			if(isset($args['time']['to'])){
				if(isset($args['time']['input_format']) && $args['time']['input_format']!=='timestamp'){
					$args['time']['to']=strtotime($args['time']['to']);
				}
				
				if(isset($args['time']['input_format']) && $args['time']['input_format']==='date'){
					$this->db->where('end <=',$args['time']['to']);
				}else{
					$this->db->where('end <',$args['time']['to']);
				}
				
			}
			
			if(!isset($args['date_form'])){
				$args['date_form']='%Y-%m-%d';
			}
			if($args['date_form']!==false){
				$this->db->select("
					FROM_UNIXTIME(schedule.start, '{$args['date_form']}') AS start,
					FROM_UNIXTIME(schedule.end, '{$args['date_form']}') AS end,
					FROM_UNIXTIME(schedule.deadline, '{$args['date_form']}') AS deadline,
				",false);
			}
		}
		
		if(isset($args['in_project_of_people']) && $args['in_project_of_people']){
			$this->db->join('project_people',"project_people.project  = schedule.project AND project_people.people = {$args['in_project_of_people']}",'inner')
				->join('project','project.id = project_people.project','inner')
				->select('project.name AS project_name, project.id AS project');
		}
		elseif(isset($args['show_project']) && $args['show_project']){
			$this->db->join('project','project.id = schedule.project','left')
				->select('project.name project_name');
		}
		
		$schedules = parent::getList($args);
		
		array_walk($schedules,function(&$schedule,$index,$CI){
			if($schedule['completed']){
				$schedule['color']='#36C';
			}else{
				if($schedule['start']<$CI->date->now){
					$schedule['color']='#555';
				}else{
					$schedule['color']='#E35B00';
				}
			}
			
			$schedule['all_day']=(bool)$schedule['all_day'];
			$schedule['completed']=(bool)$schedule['completed'];

		},$this);
		
		return $schedules;
	
	}
	
	/**
	 * 插入一条日程，返回插入的id
	 */
	function add(array $data=array()){
		
		$data=array_intersect_key($data, self::$fields);
		
		//attemp to convert date string to timestamp
		foreach(array('start','end','deadline') as $timepoint){
			if(isset($data[$timepoint])){
				if($data[$timepoint]===''){
					$data[$timepoint]=NULL;
				}
				elseif(strtotime($data[$timepoint])){
					$data[$timepoint]=strtotime($data[$timepoint]);
				}
			}
		}
		
		//generate hours by start timestamp and end timestamp
		if(isset($data['start']) && isset($data['end'])){
			$data['hours_own'] = round(($data['end']-$data['start'])/3600,2);
		}
		//generate end timestamp by start timestamp end hours
		elseif(isset($data['start']) && isset($data['hours_own'])){
			$data['end'] = $data['start']+$data['hours_own']*3600;
		}
		else{
			$data['hours_own']=NULL;
		}
		
		$data+=uidTime(true,true);
		
		$this->db->insert('schedule',$data);
		$schedule_id=$this->db->insert_id();
		
		return $schedule_id;
	}
	
	function update($schedule_id,$data){
		$schedule_id=intval($schedule_id);
		
		//attemp to convert date string to timestamp
		foreach(array('start','end','deadline') as $timepoint){
			if(isset($data[$timepoint])){
				if($data[$timepoint]===''){
					$data[$timepoint]=NULL;
				}
				elseif(strtotime($data[$timepoint])){
					$data[$timepoint]=strtotime($data[$timepoint]);
				}
			}
		}
		
		//generate hours by start timestamp and end timestamp
		if(isset($data['start']) && isset($data['end'])){
			$data['hours_own'] = round(($data['end']-$data['start'])/3600,2);
		}
		
		if(array_key_exists('hours_own',$data) && is_null($data['hours_own'])){
			$data['hours_own']=$data['end']=NULL;
		}
		
		$data=array_intersect_key($data, self::$fields);
		
		$return = $this->db->update('schedule',$data,array('id'=>$schedule_id));

		//generate end timestamp by start timestamp end hours
		if(isset($data['hours_own']) && is_numeric($data['hours_own'])){
			$this->db->query("UPDATE schedule SET end = start + 3600 * {$data['hours_own']} WHERE id = {$schedule_id}");
		}
		
		return $return;
	}
	
	function remove($schedule_id){
		$schedule_id=intval($schedule_id);
		
		return $this->db->update('schedule',array('display'=>false),array('id'=>$schedule_id));	
	}
	
	function addPeople($schedule_id,$people_id){
		$schedule_id=intval($schedule_id);
		$people_id=intval($people_id);
		
		return $this->db->insert('schedule_people',array('schedule'=>$schedule_id,'people'=>$people_id));
	}
	
	function getPeople($schedule_id){
		$schedule_id=intval($schedule_id);
		return array_sub($this->db->get_where('schedule_people',array('schedule'=>$schedule_id))->result_array(),'people');
	}
	
	/**
	 * update relatied people of a schedule
	 * will add new ones and remove old ones
	 * suitbable for all add/remove/update operation when an array of people is given
	 * @param int $schedule_id
	 * @param array $setto
	 */
	function updatePeople($schedule_id,$setto){
		
		$schedule_id=intval($schedule_id);
		
		if(!is_array($setto)){
			$setto=array();
		}
		
		$this->db->select('people')
			->from('schedule_people')
			->where('schedule_people.schedule',$schedule_id);
		
		$origin=array_sub($this->db->get()->result_array(),'people');

		$insert=array_diff($setto,$origin);
		$delete=array_diff($origin,$setto);
		
		if($delete){
			$this->db->query("
				DELETE FROM schedule_people
				WHERE schedule = $schedule_id
					AND people IN (".implode($delete).")
			");
		}

		if($insert){
			$this->db->query("
				INSERT INTO schedule_people (schedule,people)
				SELECT $schedule_id, id 
				FROM people
				WHERE id IN (".implode(',',$insert).")
			");
		}
	}
	
	function removePeople($schedule_id,$people_id){
		$schedule_id=intval($schedule_id);
		$people_id=intval($people_id);
		return $this->db->delete('schedule_people',array('schedule'=>$schedule_id,'people'=>$people_id));
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
		return $this->db->query("UPDATE schedule SET `hours_own` = `hours_own`+'{$hours_delta}', `end`=`end`+'{$time_delta}' WHERE id='{$schedule_id}'");
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
			SET `start` = `start`+$seconds_delta,
				`end`=`end`+ $seconds_delta,
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
				WHERE completed=1 AND schedule.start >= '".$last_week_monday."' AND schedule.start < '".($last_week_monday+86400*7)."'
				GROUP BY uid
			)lastweek ON staff.id=lastweek.uid
			LEFT JOIN (
				SELECT uid,SUM(IF(schedule.hours_checked IS NULL,schedule.hours_own,schedule.hours_checked)) AS hours
				FROM schedule
				WHERE completed=1 AND schedule.start >= '".($last_week_monday-86400*7)."' AND schedule.start < '".$last_week_monday."'
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

		$query=$this->dateRange($query, 'start' ,true);

		$query.="	GROUP BY schedule.uid
		";

		$query=$this->orderBy($query,'sum','DESC');

		return $this->db->query($query)->result_array();
	}
	
	/**
	 * get a taskboardsort of a  user, whetherever the sort exists
	 * @param int $uid
	 * @return array
	 */
	function getTaskBoardSort($uid){
		$uid=intval($uid);
		
		$query=$this->db->select('sort_data')->from('schedule_taskboard')->where('uid',$uid)->get();
		
		if($query -> num_rows() == 0){
			$sort_data=array(array());
			$this->createTaskBoard($sort_data, $uid);
			return $sort_data;
		}
		else
		{
			$row = $query->row_array();
			return json_decode($row['sort_data']);
		}
	}
	
	function setTaskBoardSort($sort_data , $uid){
		$uid=intval($uid);
		
		//获得sort_data最大键名，补全空键
		end($sort_data);
		$last_key=key($sort_data);
		$last_key && $sort_data += array_fill(0, $last_key, array());
		
		ksort($sort_data);
		
		$data=array(
			'sort_data' => json_encode($sort_data),
			'time' => $this->date->now
		);
		$this->db->update('schedule_taskboard' , $data , array('uid'=>$uid));
	}
	
	function createTaskBoard($sort_data ,$uid){
		
		$data=array(
			'sort_data'=>json_encode($sort_data),
			'uid'=>$uid,
			'time'=>$this->date->now
		);

		$this->db->insert('schedule_taskboard' , $data);
	}
	
}
?>