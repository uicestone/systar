<?php
class Schedule extends SS_controller{
	
	var $list_args;
	
	var $search_items=array();
	
	function __construct(){
		$this->default_method='calendar';
		parent::__construct();
		$this->load->model('schedule_model','schedule');
		$this->load->model('project_model','project');
		
		$this->list_args=array(
			'name'=>array('heading'=>'标题','cell'=>array('class'=>'ellipsis','title'=>'{content}')),
			'start'=>array('heading'=>'时间','cell'=>array('style'=>'color:{color}')),
			'hours'=>array('heading'=>'时长','parser'=>array('function'=>function($hours_own,$hours_checked){
				if(!is_null($hours_checked)){
					return $hours_checked;
				}else{
					return $hours_own;
				}
			},'args'=>array('hours_own','hours_checked'))),
			'creater_name'=>array('heading'=>'人员'),
			'project'=>array('heading'=>'事项','cell'=>'{project_name}')
		);
		
		$this->search_items=array('name','time/from','time/to');
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
		
		$this->config->set_user_item('search/order_by', 'schedule.id desc', false);
		$this->config->set_user_item('search/limit', 'pagination', false);
		$this->config->set_user_item('search/in_project_of_people', $this->user->id, false);
		$this->config->set_user_item('search/show_creater', true, false);
		$this->config->set_user_item('search/time/input_format', 'date', false);
		$this->config->set_user_item('search/date_form', '%Y-%m-%d %H:%i', false);
		
		if($this->input->get('project')){
			$this->output->title=$this->output->title.' - '.$this->project->fetch($this->input->get('project'),'name');
			$this->config->set_user_item('search/project', $this->input->get('project'),false);
		}
		
		$this->_search();
		
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
			$this->table
				->setFields($this->list_args)
				->setData($this->schedule->getList($this->config->user_item('search')));
			
			$this->load->view('schedule/list');
			$this->load->view('schedule/list_sidebar',true,'sidebar');
		}		
	}

	function outPlan(){
		
		$this->config->set_user_item('search/meta', array('外出地点'), false);
		
		$field=array(
			'staff_name'=>array('heading'=>array('data'=>'人员','width'=>'60px'),'cell'=>'<a href="#schedule/lists?staff={staff}"> {staff_name}</a>'),
			'start'=>array('heading'=>array('data'=>'时间','width'=>'60px'),'parser'=>array('function'=>function($start){
				return date('m-d H:i',$start);
			},'args'=>array('start'))),
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
	
	function stats(){
		$this->config->set_user_item('search/time/from', $this->date->month_begin, false);
		
		$this->search_items=array('time/from','time/to');
		
		$this->_search();
		
		$data=array(
			'总时间'=>$this->schedule->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'enrolled'=>true,
				'completed'=>true,
				'time'=>array('from'=>$this->config->user_item('search/time/from'),'to'=>$this->config->user_item('search/time/to'),'input_format'=>'date'),
				'order_by'=>'sum desc'
			)),
			
			'案件'=>$this->schedule->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'enrolled'=>true,
				'completed'=>true,
				'time'=>array('from'=>$this->config->user_item('search/time/from'),'to'=>$this->config->user_item('search/time/to'),'input_format'=>'date'),
				'project_type'=>'cases'
			)),
			
			'客户'=>$this->schedule->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'enrolled'=>true,
				'completed'=>true,
				'time'=>array('from'=>$this->config->user_item('search/time/from'),'to'=>$this->config->user_item('search/time/to'),'input_format'=>'date'),
				'people_type'=>'client'
			)),

			'事务'=>$this->schedule->getList(array(
				'sum'=>true,
				'group_by'=>'people',
				'enrolled'=>true,
				'completed'=>true,
				'time'=>array('from'=>$this->config->user_item('search/time/from'),'to'=>$this->config->user_item('search/time/to'),'input_format'=>'date'),
				'project_type'=>'project'
			))
		);
		
		$joined=array();
		
		foreach($data as $key => $array){
			foreach($array as $row){
				if(!isset($joined[$row['people']])){
					$joined[$row['people']]=array(
						'people_name'=>$row['people_name'],
					);
				}
				$joined[$row['people']][$key]=$row['sum'];
			}
		};
		
		$joined=array_merge($joined,array());
		
		$this->table->setFields(array(
			'people_name'=>array('heading'=>'人员'),
			'总时间'=>array('heading'=>'总时间'),
			'案件'=>array('heading'=>'案件时间'),
			'客户'=>array('heading'=>'客户时间'),
			'事务'=>array('heading'=>'事务时间'),
		))->setData($joined);
		
		$this->load->view('list');
		$this->load->view('schedule/stats_sidebar',true,'sidebar');
	}
	
	function writeCalendar($action,$schedule_id=NULL){
		
		if($action=='add'){//插入新的任务
			$data = $this->input->post();
			$data['display']=true;
			$new_schedule_id = $this->schedule->add($data);
			
			$people=$this->input->post('people');
			is_string($people) && $people=explode(',', $people);
			$this->schedule->updatePeople($new_schedule_id,$people);
			
			$this->schedule->updateTags($new_schedule_id, $this->input->post('tags'), true);
			
			$this->schedule->updatePeopleStatus($new_schedule_id, $this->user->id, array(
				'in_todo_list'=>$this->input->post('in_todo_list'),
				'enrolled'=>$this->input->post('enrolled')
			));
			
			$this->output->status='success';
			$this->output->data=array('id'=>$new_schedule_id,'name'=>$data['name']);
			
		}elseif($action=='delete'){//删除任务
			if($this->schedule->remove($schedule_id)){
				$this->output->status='success';
			}
		
		}elseif($action=='update'){//更新任务内容
			$this->schedule->update($schedule_id,$this->input->post());

			$people=$this->input->post('people');
			is_string($people) && $people=explode(',', $people);
			$this->schedule->updatePeople($schedule_id,$people);
			
			$this->schedule->updateTags($schedule_id, $this->input->post('tags'), true);
			
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
	
	function setCompleted($schedule_id, $completed='1'){
		$completed=$completed==='1'?true:false;
		$this->schedule->update($schedule_id, array('completed'=>$completed));
	}
	
	function showInTodoList($schedule_id,$in_todo_list='1'){
		$in_todo_list=$in_todo_list==='1'?true:false;
		$this->schedule->updatePeopleStatus($schedule_id, $this->user->id, array('in_todo_list'=>$in_todo_list));
	}
	
	function enroll($schedule_id,$enrolled='1'){
		$enrolled=$enrolled==='1'?true:false;
		$this->schedule->updatePeopleStatus($schedule_id, $this->user->id, array('enrolled'=>$enrolled));
	}
	
	function delete($schedule_id){
		$this->schedule->updatePeopleStatus($schedule_id, $this->user->id, array('deleted'=>true,'enrolled'=>false));
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
	
	function removeMeta($schedule_id,$profile_id){
		$this->schedule->removeMeta($schedule_id, $profile_id);
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
		
		$people=array();
		
		if(isset($schedule_id)){
			$this->schedule->id=$schedule_id;

			$schedule=$this->schedule->fetch($schedule_id);
			
			$schedule['creater_name']=$this->people->fetch($schedule['uid'],'name');
			
			$meta=$this->schedule->getMeta($schedule_id,array('show_author'=>true));
			
			$people_status=$this->schedule->getPeopleStatus($schedule_id,$this->user->id);
			
			$people=$this->schedule->getPeople($schedule_id);
			//从people中删除当前用户，应为当前用户会自动被关联到本日志
			unset($people[array_search($this->user->id,$people)]);
			
			$tags=$this->schedule->getTags($schedule_id);

			if(isset($schedule['project'])){
				$project=$this->project->fetch($schedule['project']);
				$this->load->addViewData('project', $project);
			}
			
			isset($schedule['name']) && $this->output->setData($schedule['name'],'name');
			isset($schedule['completed']) && $this->output->setData($schedule['completed'],'completed');
			isset($people_status[$this->user->id]['in_todo_list']) && $this->output->setData($people_status[$this->user->id]['in_todo_list'],'in_todo_list');
			isset($people_status[$this->user->id]['enrolled']) && $this->output->setData($people_status[$this->user->id]['enrolled'],'enrolled');
		}
		
		$this->load->addViewArrayData(compact('schedule','meta','people','tags','people_status'));
		
		$this->output->setData($this->load->view("schedule/$mode",true));
	}
	
	function todoList(){
		$this->load->addViewData('side_task_board', $this->schedule->getList(array('people'=>$this->user->id,'in_todo_list'=>true,'show_project'=>true)));
		$this->output->setData($this->load->view('schedule/todo_list',true),'todo-list','sidebar','aside>section[hash="schedule"], aside>section[hash="schedule/taskboard"]');
	}
	
}
?>