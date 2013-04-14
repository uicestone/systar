<?php
class Schedule extends SS_controller{
	
	var $section_title='日程';
	
	var $list_args;
	
	function __construct(){
		$this->default_method='calendar';
		
		parent::__construct();
		
		$this->load->model('project_model','project');
		
		$this->list_args=array(
			'name'=>array('heading'=>'标题'),
			'time'=>array('heading'=>'时间','parser'=>array('function'=>function($time_start){
				return date('Y-m-d H:i',intval($time_start));
			},'args'=>array('{time_start}'))),
			'hours'=>array('heading'=>'时长','parser'=>array('function'=>function($hours_own,$hours_checked){
				if($hours_checked!==''){
					return $hours_checked;
				}else{
					return $hours_own;
				}
			},'args'=>array('{hours_own}','{hours_checked}'))),
			'project'=>array('heading'=>'事项','cell'=>'{project_name}')
		);
	}
	
	function calendar(){
		$task_board=$this->schedule->getTaskBoardSort($this->user->id);
		$this->load->addViewData('side_task_board', $this->schedule->getList(array('id_in_set'=>$task_board[0])));
		$this->load->view('schedule/calendar');
		$this->load->view('schedule/calendar_sidebar',true,'sidebar');
	}
	
	function mine(){
		$this->lists();
	}
	
	function plan(){
		$this->config->set_user_item('search/completed', false, false);
		$this->lists();
	}
	
	function lists(){
		
		$this->config->set_user_item('search/orderby', 'schedule.id desc', false);
		$this->config->set_user_item('search/limit', 'pagination', false);
		$this->config->set_user_item('search/in_project_of_people', $this->user->id, false);
		
		if($this->input->get('project')){
			$this->section_title='日程 - '.$this->project->fetch($this->input->get('project'),'name');
			$this->config->set_user_item('search/project', $this->input->get('project'));
		}
		
		if($this->input->post('name')){
			$this->config->set_user_item('search/name', $this->input->post('name'));
		}
		
		if($this->input->post('labels')){
			$this->config->set_user_item('search/labels', $this->input->post('labels'));
		}
		
		if($this->input->post('name')===''){
			$this->config->unset_user_item('search/name');
		}
		
		if($this->input->post('submit')==='search' && $this->input->post('labels')===false){
			$this->config->unset_user_item('search/labels');
		}
		
		if($this->input->post('submit')==='search_cancel'){
			$this->config->unset_user_item('search/name');
			$this->config->unset_user_item('search/labels');
		}
		
		if($this->input->get('export')=='excel'){
			
			$field=array(
				'name'=>array('heading'=>'标题'),
				'content'=>array('heading'=>'内容'),
				'time_start'=>array('heading'=>'时间','eval'=>true,'cell'=>"return date('Y-m-d H:i',{time_start});"),
				'hours_own'=>array('heading'=>'自报小时'),
				'staff_name'=>array('heading'=>'律师')
			);
			
			$this->table->setFields($field)
				->setData($this->schedule->getList($this->config->user_item('search')))
				->generateExcel();
		}else{
			$table=
				$this->table
					->setFields($this->list_args)
					->setData($this->schedule->getList($this->config->user_item('search')))
					->generate();
			$this->load->addViewData('list',$table);
			$this->load->view('schedule/list');
			$this->load->view('schedule/list_sidebar',true,'sidebar');
		}		
	}

	function outPlan(){
		
		$this->config->set_user_item('search/profiles', array('外出地点'), false);
		
		$field=array(
			'staff_name'=>array('heading'=>array('data'=>'人员','width'=>'60px'),'cell'=>'<a href="#schedule/lists?staff={staff}"> {staff_name}</a>'),
		
			'time_start'=>array('heading'=>array('data'=>'时间','width'=>'60px'),'eval'=>true,'cell'=>"
				return date('m-d H:i',{time_start});
			"),
		
			'place'=>array('heading'=>array('data'=>'外出地点','width'=>'25%'))
		);
		
		$table=$this->table->setFields($field)
					->setData($this->schedule->getOutPlanList())
					->generate();
		
		$this->load->addViewData('list',$table);
		
		$this->load->view('list');
	}
	
	function readCalendar($start,$end=NULL){
		if(is_null($end)){
			//获取指定的一个日程
			$this->output->data=$this->schedule->fetch($start);
		
		}else{
			//获得当前视图的全部日历，根据$this->input->get('start'),$this->input->get('end')(timestamp)
			$this->output->data=$this->schedule->fetch_range($start,$end,$this->input->get('staff'),$this->input->get('project'));
		}
	}
	
	function workHours(){
		//@todo

		if(date('w')==1){//今天是星期一
			$start_of_this_week=strtotime($this->date->today);
		}else{
			$start_of_this_week=strtotime("-1 Week Monday");
		}
		
		if(!option('in_date_range')){
			option('date_range/from',date('Y-m-d',$start_of_this_week));
			option('date_range/to',$this->date->today);
			option('date_range/from_timestamp',$start_of_this_week);
			option('date_range/to_timestamp',$this->date->now);
			option('in_date_range',true);
		}
		
		$staffly_workhours=$this->schedule->getStafflyWorkHoursList();

		$chart_staffly_workhours_catogary=json_encode(array_sub($staffly_workhours,'staff_name'));
		$chart_staffly_workhours_series=array(
			array('name'=>'工作时间','data'=>array_sub($staffly_workhours,'sum'))
		);
		$chart_staffly_workhours_series=json_encode($chart_staffly_workhours_series,JSON_NUMERIC_CHECK);

		$field=array(
			'staff_name'=>array('heading'=>'姓名'),
			'sum'=>array('heading'=>'总工作时间'),
			'avg'=>array('heading'=>'工作日平均')
		);
		
		$work_hour_stat=$this->table->setFields($field)
				->generate($staffly_workhours);

		$this->load->addViewArrayData(compact('chart_staffly_workhours_catogary','chart_staffly_workhours_series','work_hour_stat'));
	
		$this->load->view('schedule/workhours');
	}
	
	function writeCalendar($action,$schedule_id=NULL){
		
		if($action=='add'){//插入新的任务
			$data = $this->input->post();
			
			$new_schedule_id = $this->schedule->add($data);
			$this->schedule->addPeople($new_schedule_id,$this->input->post('people'));
			if(!isset($data['time_start']) && !isset($data['time_end']) && !isset($data['all_day'])){
				$this->addToTaskBoard($new_schedule_id);
			}
			$this->schedule->updateProfiles($new_schedule_id, $this->input->post('profiles'));
			
			if($new_schedule_id){
				$this->output->status='success';
				$this->output->data=array('id'=>$new_schedule_id,'name'=>$data['name']);
			}
			
		}elseif($action=='delete'){//删除任务
			if($this->schedule->remove($schedule_id)){
				$this->output->status='success';
			}
		
		}elseif($action=='update'){//更新任务内容
			$this->schedule->update($schedule_id,$this->input->post());
			$this->schedule->updateProfiles($schedule_id, $this->input->post('profiles'));
			//@TODO 这里为了起到更新的作用，先删除所有相关人，再添加新的，这种做法是不科学的
			$this->schedule->removePeople($schedule_id);
			$this->schedule->addPeople($schedule_id,$this->input->post('people'));
			
			$schedule=$this->schedule->fetch($schedule_id);
			
			$this->output->data=array('id'=>$schedule_id,'name'=>$schedule['name'],'completed'=>(bool)$schedule['completed'],'start'=>$schedule['time_start'],'end'=>$schedule['time_end']);
			
			$this->output->status='success';
		
		}elseif($action=='resize'){//更新任务时间
			$time_delta=intval($this->input->post('dayDelta'))*86400+intval($this->input->post('minuteDelta'))*60;
			
			if($this->schedule->resize($schedule_id,$time_delta,(int)$this->input->post('allDay'))){
				$this->output->status='success';
			}

		}elseif($action=='drag'){
			$time_delta=intval($this->input->post('dayDelta'))*86400+intval($this->input->post('minuteDelta'))*60;

			if($this->schedule->drag($schedule_id,$time_delta,(int)$this->input->post('allDay'))){
				$this->output->status='success';
			}
		}
			
	}
	
	function taskBoard()
	{
		$sort_data = $this -> schedule -> getTaskBoardSort($this->user->id);
		
		//任务强数据的第一列作为边栏列
		$side_task_board=$this->schedule->getList(array('id_in_set'=>$sort_data[0]));
		unset($sort_data[0]);
		
		$task_board = array();
		
		foreach($sort_data as $column){
			$task_board[]=$this->schedule->getList(array('id_in_set'=>$column));
		}
		
		$this->load->addViewData('task_board' , $task_board);
		$this->load->addViewData('side_task_board', $side_task_board);
		$this->load->view('schedule/taskboard');
		$this->load->view('schedule/taskboard_sidebar',true,'sidebar');
	}
	
	function setTaskBoardSort(){
		$sort_data = $this->input->post('sortData')+array_fill(0, 6, array());
		ksort($sort_data);
		//输入的最后一列是真正的0列，其他列号依次向后推
		$sidebar_column=array_pop($sort_data);
		array_unshift($sort_data,$sidebar_column);
		$this -> schedule -> setTaskBoardSort($sort_data , $this->user->id);
		$this->output->status='success';
	}
	
	function addToTaskBoard($task_id , $uid=NULL , $series=NULL)
	{	
		if(is_null($uid)){
			$uid=$this->user->id;
		}
		//取一列任务墙
		$sort_data = $this -> schedule -> getTaskBoardSort($uid);
		
		//将任务加入墙的第一列末尾
		if(is_null($series))
		{
			$series = 0;
		}
		if($series > 5)
		{
			$series = 5;
		}
		$each_series = $sort_data[$series];
		array_push($each_series , $task_id);
		$sort_data[$series] = $each_series;

		$this -> schedule -> setTaskBoardSort($sort_data, $uid);
		
		$this->output->status='success';
	}
	
	function removefromtaskboard($task_id , $uid=NULL)
	{
		if(is_null($uid))
		{
			$uid=$this->user->id;
		}
		//遍历sort_data
		$sort_data = $this -> schedule -> getTaskBoardSort($uid);
		//echo "sort_data = "; echo print_r($sort_data)."<br/>";
		for ($i=0 ; $i<6 ; $i++)
		{
			$series = $sort_data[$i];
			$key = array_search($task_id , $series);
			//echo "key = "; echo print_r($key)."<br/>";
			if($key!==false && $key!=="")
			{
				$sort_data[$i] = array();
				$series[$key] = NULL;
				//echo "series = "; echo print_r($series)."<br/>";
				$series = array_filter($series);
				//echo "series = "; echo print_r($series)."<br/>";
				foreach ($series as $value)
				{
					array_push($sort_data[$i] , $value);
				}
				//echo "sort_data = "; echo print_r($sort_data)."<br/>";
				$this -> schedule -> setTaskBoardSort($sort_data, $uid);
				break;
			}
		}
		
		$this->output->status='success';
	}
	
	function add(){
		$this->output->setData('新日程', 'name');
		$this->load->addViewData('mode', 'add');
		$this->load->view('schedule/edit');
	}
	
	function view($schedule_id){
		$this->edit($schedule_id,'view');
	}
	
	/**
	 * ajax响应页面，载入dialog内单条日程视图
	 */
	function edit($schedule_id=NULL,$mode='edit'){
		
		$this->load->model('project_model','project');
		
		if(isset($schedule_id)){
			$this->schedule->id=$schedule_id;

			$schedule=$this->schedule->fetch($schedule_id);
			
			$profiles=$this->schedule->getProfiles($schedule_id);
			
			$people=$this->schedule->getPeople($schedule_id);

			if(isset($schedule['project'])){
				$project=$this->project->fetch($schedule['project']);
				$this->load->addViewData('project', $project);
			}

			$this->load->addViewData('schedule', $schedule);
			$this->load->addViewData('profiles', $profiles);
			$this->load->addViewData('people', $people);

			isset($schedule['name']) && $this->output->setData($schedule['name'],'name');

			isset($schedule['completed']) && $this->output->setData($schedule['completed'],'completed');
		}
		
		$this->output->setData($this->load->view("schedule/$mode",true));
	}
}
?>