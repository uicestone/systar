<?php
class Schedule extends SS_controller{
	
	var $section_title='日程';
	
	var $list_args;
	
	function __construct(){
		$this->default_method='calendar';
		
		parent::__construct();
		
		$this->load->model('project_model','project');
		
		$this->list_args=array(
			'name'=>array('heading'=>'标题','cell'=>array('class'=>'ellipsis','title'=>'{content}')),
			'start'=>array('heading'=>'时间','cell'=>array('style'=>'color:{color}')),
			'hours'=>array('heading'=>'时长','parser'=>array('function'=>function($hours_own,$hours_checked){
				if($hours_checked!==''){
					return $hours_checked;
				}else{
					return $hours_own;
				}
			},'args'=>array('hours_own','hours_checked'))),
			'creater_name'=>array('heading'=>'人员'),
			'project'=>array('heading'=>'事项','cell'=>'{project_name}')
		);
	}
	
	function calendar(){
		$this->load->addViewData('side_task_board', $this->schedule->getList(array('people'=>$this->user->id,'in_todo_list'=>true,'show_project'=>true)));
		
		$this->load->view('schedule/calendar');
		$this->load->view('schedule/todo_list',true,'sidebar');
	}
	
	function mine(){
		$this->lists();
	}
	
	function plan(){
		$this->config->set_user_item('search/completed', false, false);
		
		$this->list_args=array(
			'name'=>array('heading'=>'标题','cell'=>array('class'=>'ellipsis','title'=>'{content}')),
			'time'=>array('heading'=>'时间','parser'=>array('function'=>function($start){
				return $start?date('Y-m-d H:i',intval($start)):null;
			},'args'=>array('start'))),
			'creater_name'=>array('heading'=>'人员'),
			'project'=>array('heading'=>'事项','cell'=>'{project_name}')
		);
		
		$this->lists();
	}
	
	function lists(){
		
		$this->config->set_user_item('search/orderby', 'schedule.id desc', false);
		$this->config->set_user_item('search/limit', 'pagination', false);
		$this->config->set_user_item('search/in_project_of_people', $this->user->id, false);
		$this->config->set_user_item('search/show_creater', true, false);
		$this->config->set_user_item('search/time/input_format', 'date', false);
		$this->config->set_user_item('search/date_form', '%Y-%m-%d %H:%i', false);
		
		if($this->input->get('project')){
			$this->section_title='日程 - '.$this->project->fetch($this->input->get('project'),'name');
			$this->config->set_user_item('search/project', $this->input->get('project'),false);
		}
		
		$search_items=array('name','time/from','time/to');
		
		foreach($search_items as $item){
			if($this->input->post($item)!==false){
				if($this->input->post($item)!==''){
					$this->config->set_user_item('search/'.$item, $this->input->post($item));
				}else{
					$this->config->unset_user_item('search/'.$item);
				}
			}
		}
		
		if($this->input->post('submit')==='search_cancel'){
			foreach($search_items as $item){
				$this->config->unset_user_item('search/'.$item);
			}
		}
		
		if($this->input->get('export')=='excel'){
			
			$this->config->set_user_item('search/date_form', '%Y-%m-%d', false);
			
			$field=array(
				'start'=>array('heading'=>'日期'),
				'content'=>array('heading'=>'工作内容'),
				'hours_own'=>array('heading'=>'工作时长'),
				'creater_name'=>array('heading'=>'经办人'),
				'project_name'=>array('heading'=>'事务')
			);
			
			$this->table
				->setFields($field)
				->setData($this->schedule->getList($this->config->user_item('search')))
				->generateExcel();
		}else{
			$table=$this->table
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
		
			'start'=>array('heading'=>array('data'=>'时间','width'=>'60px'),'eval'=>true,'cell'=>"
				return date('m-d H:i',{start});
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
			$this->output->data=$this->schedule->getList(array('date_form'=>false,'time'=>array('from'=>$start,'to'=>$end),'people'=>$this->user->id));
		}
	}
	
	function workHours(){
		//@TODO 工作时间统计

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
			$data['display']=true;
			$new_schedule_id = $this->schedule->add($data);
			
			$this->schedule->updatePeople($new_schedule_id,$this->input->post('people'));
			
			if(is_array($this->input->post('profiles'))){
				foreach($this->input->post('profiles') as $name => $content){
					$this->schedule->addProfile($new_schedule_id,$name,$content);
				}
			}
			
			$this->output->status='success';
			$this->output->data=array('id'=>$new_schedule_id,'name'=>$data['name']);
			
		}elseif($action=='delete'){//删除任务
			if($this->schedule->remove($schedule_id)){
				$this->output->status='success';
			}
		
		}elseif($action=='update'){//更新任务内容
			$this->schedule->update($schedule_id,$this->input->post());
			$this->schedule->updatePeople($schedule_id,$this->input->post('people'));
			
			if(is_array($this->input->post('profiles'))){
				foreach($this->input->post('profiles') as $name => $content){
					$this->schedule->addProfile($schedule_id,$name,$content);
				}
			}
			
			$schedule=$this->schedule->fetch($schedule_id);
			
			$this->output->data=array('id'=>$schedule_id,'name'=>$schedule['name'],'completed'=>(bool)$schedule['completed'],'start'=>$schedule['start'],'end'=>$schedule['end']);
			
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
	
	function taskBoard(){
		$sort_data = $this -> schedule -> getTaskBoardSort($this->user->id);
		
		$task_board = array();
		
		foreach($sort_data as $column){
			$task_board[]=$this->schedule->getList(array('id_in_set'=>$column,'show_project'=>true));
		}
		
		$this->load->addViewData('task_board' , $task_board);
		$this->load->addViewData('side_task_board', $this->schedule->getList(array('people'=>$this->user->id,'in_todo_list'=>true,'show_project'=>true)));
		$this->load->view('schedule/taskboard');
		$this->load->view('schedule/todo_list',true,'sidebar');
	}
	
	function setTaskBoardSort(){
		$sort_data=$this->input->post('sortData');
		$this->schedule->setTaskBoardSort($sort_data, $this->user->id);
		$this->output->status='success';
	}
	
	function addToTaskBoard($task_id , $series=0 , $uid=NULL){
		
		$task_id=intval($task_id);$series=intval($series);
		
		if(is_null($uid)){
			$uid=$this->user->id;
		}
		
		$sort_data = $this -> schedule -> getTaskBoardSort($uid);
		
		if(!isset($sort_data[$series])){
			$sort_data[]=array($task_id);
		}
		else{
			array_push($sort_data[$series] , $task_id);
		}

		$this->schedule->setTaskBoardSort($sort_data, $uid);
		
		$this->output->status='success';
	}
	
	function removeFromTaskboard($task_id , $uid=NULL){
		if(is_null($uid)){
			$uid=$this->user->id;
		}
		
		$sort_data = $this -> schedule -> getTaskBoardSort($uid);
		
		foreach($sort_data as $column_id=>$column){
			$search=array_search($task_id,$column);
			if($search!==false){
				unset($sort_data[$column_id][$search]);
			}
		}
		
		$sort_data=array_trim_rear($sort_data);
		
		$this->schedule->setTaskBoardSort($sort_data, $uid);
		
		$this->output->status='success';
	}
	
	function removeTaskboardCompleted(){
		$sort_data = $this -> schedule -> getTaskBoardSort($this->user->id);
		
		$schedule=array();
		
		foreach($sort_data as $column_id => $column){
			$schedule=array_merge($schedule,$column);
		}
		
		$taskboard_completed=$this->schedule->getArray(array('completed'=>true,'id_in'=>$schedule),'id');
		
		foreach($sort_data as $column_id =>$column){
			$sort_data[$column_id]=array_values(array_diff($column,$taskboard_completed));
		}
		$this->schedule->setTaskBoardSort($sort_data, $this->user->id);
	}
	
	function removeProfile($schedule_id,$profile_id){
		$this->schedule->removeProfile($schedule_id, $profile_id);
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
			
			$this->load->model('people_model','people');
			$schedule['creater_name']=$this->people->fetch($schedule['uid'],'name');
			
			$profiles=$this->schedule->getProfiles($schedule_id,array('show_author'=>true));
			
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
			isset($schedule['in_todo_list']) && $this->output->setData($schedule['in_todo_list'],'in_todo_list');
		}
		
		$this->output->setData($this->load->view("schedule/$mode",true));
	}
	
	function todoList(){
		$this->load->addViewData('side_task_board', $this->schedule->getList(array('people'=>$this->user->id,'in_todo_list'=>true,'show_project'=>true)));
		$this->output->setData($this->load->view('schedule/todo_list',true),'todo-list','sidebar','aside>section[hash="schedule"], aside>section[hash="schedule/taskboard"]');
	}
	
}
?>