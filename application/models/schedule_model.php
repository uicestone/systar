<?php
class Schedule_model extends Object_model{
	
	static $fields;

	var $relative_mod=array(
		'people'=>array(
			'deleted'=>1,
			'enrolled'=>2,
			'in_todo_list'=>4
		)
	);

	function __construct(){
		parent::__construct();
		$this->table='schedule';
		parent::$fields['type']=$this->table;
		self::$fields=array(
			'content'=>NULL,//内容
			'start'=>NULL,//开始时间
			'end'=>NULL,//结束时间
			'deadline'=>NULL,//截止日期
			'hours_own'=>NULL,//自报时长
			'hours_checked'=>NULL,//核准时长
			'hours_bill'=>NULL,//账单时长
			'all_day'=>false,//全天
			'completed'=>false,//已完成
			'project'=>NULL,//关联案件
		);

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
	 *	project: get schedule only under this project
	 *	project_type
	 *	project_tags: 仅获取带有给定标签的事务的日程
	 *	people: 
	 *	people_type: 
	 *	people_tags: 仅获取带有给定标签的人员的相关日程
	 *	group_by
	 *		people
	 *	in_todo_list 仅在指定people或group_by people的时候有效
	 *	enrolled 仅在指定people或group_by people的时候有效
	 *	completed 仅在指定people或group_by people的时候有效
	 *	time array or boolean 时间段的范围
	 *		false
	 *		array(
	 *			from=>timestamp/date string/datetime string
	 *			to=>timestamp/date string/datetime string
	 *			input_format=>timestamp, date(default)
	 *			date_form=>mysql date form string, or false (default: '%Y-%m-%d')
	 *		)
	 *	in_project_of_people bool
	 *	show_creater
	 *	show_project
	 *	id_in_set
	 * @return array
	 */
	function getList(array $args=array()){
		
		$this->db->select('object.*, schedule.*');
		
		if(isset($args['project']) && $args['project']){
			$this->db->where('schedule.project',$args['project']);
		}
		
		if(isset($args['project_type']) && $args['project_type']){
			$this->db->where("schedule.project IN (SELECT id FROM object INNER JOIN project USING(id) WHERE object.type ={$this->db->escape($args['project_type'])} AND object.company = {$this->company->id} )");
		}
		
		if(isset($args['project_tags']) && $args['project_tags']){
			foreach($args['project_tags'] as $id => $tag_name){
				$this->db->join("object_tag `t_$id`","schedule.project = `t_$id`.object AND `t_$id`.tag_name = {$this->db->escape($tag_name)}",'inner',false);
			}
		}
		
		//判断需要内联object_relationship表的参数
		if(isset($args['people']) || isset($args['people_tags']) || (isset($args['group_by']) && $args['group_by']==='people')){
			$this->db->join('object_relationship','object_relationship.object = object.id','inner');
		}
		
		//依赖schedule_people表
		//TODO 判断多个人同时属于一个日程，并区分对待人员状态（如统计一个律师对一个客户的时间）
		if(isset($args['people']) && $args['people']){
			$args['has_relative_like']=$args['people'];
		}
		
		if(isset($args['people_type']) && $args['people_type']){
			$this->db->where("schedule.id IN (
				SELECT object FROM object_relationship WHERE relative IN (
					SELECT id FROM object WHERE type = {$this->db->escape($args['people_type'])}
				)
			)");
		}
		
		//依赖schedule_people表
		if(isset($args['people_tags']) && $args['people_tags']){
			foreach($args['people_tags'] as $id => $tag_name){
				$this->db->join("object_tag `t_$id`","object_relationship.object = `t_$id`.object AND `t_$id`.tag_name = {$this->db->escape($tag_name)}",'inner');
			}
		}
		
		if(isset($args['group_by'])){
			//依赖schedule_people表
			//TODO 判断多个人同时属于一个日程，并区分对待人员状态（如统计一个律师对一个客户的时间）
			if($args['group_by']==='people'){
				if(isset($args['people_is_staff']) && $args['people_is_staff']){
					$this->db->where('object_relationship.object IN (SELECT id FROM staff)',NULL,false);
				}
				$this->db->group_by('object_relationship.object')
					->join('object people','people.id = object_relationship.relative','inner')
					->select('people.id people, people.name people_name');
			}
		}
		
		if((isset($args['people']) || (isset($args['group_by']) && $args['group_by']==='people')) && isset($args['in_todo_list'])){
			//依赖人员参数
			$this->db->where('object_relationship.mod & 4 = '.($args['in_todo_list']?4:0),NULL,false);
		}
		
		if((isset($args['people']) || (isset($args['group_by']) && $args['group_by']==='people')) && isset($args['enrolled'])){
			//依赖人员参数
			$this->db->where('object_relationship.mod & 2 = '.($args['enrolled']?2:0),NULL,false);
		}
		
		if((isset($args['people']) || (isset($args['group_by']) && $args['group_by']==='people'))){
			//依赖人员参数
			!isset($args['deleted']) && $args['deleted']=false;
			$this->db->where('object_relationship.mod & 1 = '.($args['deleted']?1:0),NULL,false);
		}
		
		if(isset($args['completed'])){
			$this->db->where('schedule.completed',$args['completed']);
		}
		
		if(!isset($args['time'])){
			$args['time']=array_prefix($args, 'time');
		}
		
		if($args['time']){
			if($args['time']===false){
				$this->db->where(array('schedule.start'=>NULL,'schedule.end'=>NULL));
			}
			
			if(isset($args['time']['from']) && $args['time']['from']){
				if(isset($args['time']['input_format']) && $args['time']['input_format']!=='timestamp'){
					$args['time']['from']=strtotime($args['time']['from']);
				}
				$this->db->where('schedule.start >=',$args['time']['from']);
			}
			
			if(isset($args['time']['to']) && $args['time']['to']){
				if(isset($args['time']['input_format']) && $args['time']['input_format']!=='timestamp'){
					$args['time']['to']=strtotime($args['time']['to']);
				}
				
				if(isset($args['time']['input_format']) && $args['time']['input_format']==='date'){
					$args['time']['to']+=86400;
				}
				
				$this->db->where('schedule.end <',$args['time']['to']);
				
			}
			
			if(!isset($args['date_form'])){
				$args['date_form']='%Y-%m-%d';
			}
			if($args['date_form']!==false){
				$this->db->select(array(
					"FROM_UNIXTIME(schedule.start, '{$args['date_form']}') `start`",
					"FROM_UNIXTIME(schedule.end, '{$args['date_form']}') `end`",
					"FROM_UNIXTIME(schedule.deadline, '{$args['date_form']}') `deadline`"
				),false);
			}
		}
		
		if(isset($args['in_project_of_people']) && $args['in_project_of_people']){
			$this->db->join('object_relationship',"object_relationship.objec t  = schedule.project AND object_relationship.relative{$this->db->escape_int_array($args['in_project_of_people'])}",'inner')
				->join('object project_object','project_object.id = object_relationship.object','inner')
				->select('project_object.name AS project_name, project_object.id AS project');
		}
		elseif(isset($args['show_project']) && $args['show_project']){
			$this->db->join('object project_object','project_object.id = schedule.project','left')
				->select('project_object.name project_name');
		}
		
		if(isset($args['show_creater']) && $args['show_creater']){
			$this->db->join('object creater','creater.id = schedule.uid','inner')
				->select('creater.id creater, creater.name creater_name');
		}
		
		if(isset($args['id_in_set'])){
			$args['id_in']=$args['id_in_set'];
			if($args['id_in_set']){
				$this->db->order_by("FIELD(schedule.id, ".implode(', ',$args['id_in_set']).")",'',false);
				$args['order_by']=false;
			}
		}
		
		if(isset($args['sum']) && $args['sum']){
			array_remove_value($this->db->ar_select, 'schedule.*');
			array_remove_value($this->db->ar_select, '`deadline`',true);
			array_remove_value($this->db->ar_select, '`start`',true);
			array_remove_value($this->db->ar_select, '`end`',true);
			
			$this->db->select('SUM(IF(hours_checked IS NULL, hours_own, hours_checked)) sum',false);
		}
		
		$schedules = parent::getList($args);
		
		!isset($args['sum']) && array_walk($schedules,function(&$schedule,$index){
			if($schedule['completed']){
				$schedule['color']='#36C';
			}else{
				if($schedule['start']<time()){
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
	 * @todo 不同人员认领同一任务将不重复计算时间，实际需要计算
	 */
	function getSum(array $args=array()){
		$args=array_merge($args,array('sum'=>true));
		$result_array=$this->getList($args);
		return isset($result_array[0]['sum'])?$result_array[0]['sum']:NULL;
	}

	/**
	 * 插入一条日程，返回插入的id
	 */
	function add(array $data=array()){
		
		$insert_id=parent::add($data);
		
		//attemp to convert date string to timestamp
		foreach(array('start','end','deadline') as $timepoint){
			if(isset($data[$timepoint])){
				if(strtotime($data[$timepoint])){
					$data[$timepoint]=strtotime($data[$timepoint]);
				}
			}
		}
		
		foreach(array('start','end','deadline','hours_own') as $var){
			if(isset($data[$var])){
				if($data[$var]===''){
					$data[$var]=NULL;
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
		
		$data=array_merge(self::$fields,array_intersect_key($data,self::$fields));
		$data['id']=$insert_id;
		$this->db->insert($this->table,$data);

		$this->updatePeople($insert_id, array($this->user->id));
		
		return $insert_id;
	}
	
	function update($schedule_id,$data){
		$schedule_id=intval($schedule_id);
		
		//attemp to convert date string to timestamp
		foreach(array('start','end','deadline') as $timepoint){
			if(isset($data[$timepoint])){
				if(strtotime($data[$timepoint])){
					$data[$timepoint]=strtotime($data[$timepoint]);
				}
			}
		}
		
		foreach(array('start','end','deadline','hours_own') as $var){
			if(isset($data[$var])){
				if($data[$var]===''){
					$data[$var]=NULL;
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
		
		$return = parent::update($schedule_id,$data);

		//generate end timestamp by start timestamp end hours
		if(isset($data['hours_own']) && is_numeric($data['hours_own'])){
			$this->db->where('id',$schedule_id)
				->set('end',"start + 3600 * {$data['hours_own']}",false)
				->update('schedule');
		}
		
		return $return;
	}
	
	function remove($schedule_id){
		$schedule_id=intval($schedule_id);
		
		return $this->db->update('schedule',array('display'=>false),array('id'=>$schedule_id));	
	}
	
	function getPeople($schedule_id){
		return array_column(parent::getRelative($schedule_id),'relavite');
	}
	
	function addPeople($schedule_id,$people_id){
		return parent::addRelative($schedule_id, $people_id);
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
		
		//操作人总在人员列表中
		if(!in_array($this->user->id,$setto)){
			array_push($setto,$this->user->id);
		}
		
		$this->db->select('people')
			->from('object_relationship')
			->where('object_relationship.object',$schedule_id);
		
		$origin=array_column($this->db->get()->result_array(),'people');

		$insert=array_diff($setto,$origin);
		$delete=array_diff($origin,$setto);
		
		if($delete){
			$this->db->where('object',$schedule_id)
				->where_in('relative',$delete)
				->delete('object_relationship');
		}

		if($insert){
			$this->db->query("
				INSERT INTO object_relationship (object,relative)
				SELECT $schedule_id, id 
				FROM people
				WHERE id IN (".implode(',',$insert).")
			");
			
			$name=$this->fetch($schedule_id, 'name');
			
			$this->message->send('邀请你参与日程：'.$name, $insert);
		}
	}
	
	function removePeople($schedule_id,$people_id){
		$schedule_id=intval($schedule_id);
		$people_id=intval($people_id);
		$this->db->delete('object_relationship',array('object'=>$schedule_id,'relative'=>$people_id));
		return $this->db->affected_rows();
	}
	
	function getPeopleStatus($schedule_id, $people_id){
		$this->db->from('object_relationship')
			->where('object',$schedule_id)
			->where('relative',$people_id);
		
		$result_array=$this->db->get()->result_array();
		
		$people_status=array();
		
		foreach($result_array as $row){
			$people_status[$row['relative']]=$row;
		}
		
		return $people_status;
	}
	
	function updatePeopleStatus($schedule_id,$people_id,$data){
		$data=array_intersect_key($data, array('enrolled'=>'参与','deleted'=>'已删除','in_todo_list'=>'在任务列表显示'));
		$this->db->update('object_relationship',$data,array('object'=>$schedule_id,'relative'=>$people_id));
		return $this->db->affected_rows();
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
			'time' => time()
		);
		$this->db->update('schedule_taskboard' , $data , array('uid'=>$uid));
	}
	
	function createTaskBoard($sort_data ,$uid){
		
		$data=array(
			'sort_data'=>json_encode($sort_data),
			'uid'=>$uid,
			'time'=>time()
		);

		$this->db->insert('schedule_taskboard' , $data);
	}
	
}
?>