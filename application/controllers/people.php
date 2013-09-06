<?php
class People extends SS_Controller{
	
	var $form_validation_rules=array();
	
	var $search_items=array();
	
	var $list_args;
	
	var $relative_list_args;

	var $profile_list_args;

	var $status_list_args;

	var $project_list_args;
	
	var $team_list_args;
	
	var $schedule_list_args;

	function __construct() {
		parent::__construct();
		$this->search_items=array('name','labels','in_team');
		
		$this->list_args=array(
			'abbreviation'=>array(
				'heading'=>'名称',
				'cell'=>array('data'=>'{abbreviation}','class'=>"ellipsis",'title'=>'{name}')
			),
			'type'=>array('heading'=>'类型','parser'=>array('function'=>function($type){
				return lang($type);
			},'args'=>array('type'))),
			'phone'=>array('heading'=>'电话'),
			'email'=>array('heading'=>'电邮'),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->people,'getCompiledLabels'),'args'=>array('id')))
		);
		
		$this->relative_list_args=array(
			'name'=>array('heading'=>'名称','cell'=>'{name}'), 
			'phone'=>array('heading'=>'电话'), 
			'email'=>array('heading'=>'电邮'), 
			'relation'=>array('heading'=>'关系')
		);
		
		$this->profile_list_args=array(
			'name'=>array('heading'=>'名称','cell'=>'{name}'), 
			'content'=>array('heading'=>'内容','parser'=>array('function'=>function($name,$content){
				if($name=='电子邮件'){
					return '<a href="mailto:'.$content.'" target="_blank">'.$content.'</a>';
				}else{
					return $content;
				}
			},'args'=>array('name','content'))), 
			'comment'=>array('heading'=>'备注')
		);
		
		$this->status_list_args=array(
			'name'=>array('heading'=>'状态'), 
			'content'=>array('heading'=>'内容','cell'=>array('class'=>'ellipsis','title'=>'{content}')),
			'date'=>array('heading'=>'日期'),
			'comment'=>array('heading'=>'备注')
		);
		
		$this->team_list_args=array(
			'name'=>array('heading'=>'名称'),
			'type'=>array('heading'=>'类型','parser'=>array('function'=>'lang','args'=>array('type'))),
			'leader_name'=>array('heading'=>'负责人','cell'=>array('data'=>'<a href="#{leader_type}/{leader}">{leader_name}</a>'))
		);
		
		$this->schedule_list_args=array(
			'name'=>array('heading'=>array('data'=>'标题'),'wrap'=>array('mark'=>'span','class'=>'show-schedule','id'=>'{id}')),
			'start'=>array('heading'=>array('data'=>'时间'),'parser'=>array('function'=>function($start){
				return $start?date('Y-m-d H:i',intval($start)):null;
			},'args'=>array('start'))),
			'creater_name'=>array('heading'=>array('data'=>'人员'))
		);
		
		$this->load->view_path['list_aside']='people/list_sidebar';
		$this->load->view_path['edit']='people/edit';
		$this->load->view_path['edit_aside']='people/edit_sidebar';
	}
	
	/**
	 * 根据请求的字符串返回匹配的人员id，名称和类别
	 */
	function match($term=NULL){
		
		if(is_null($term)){
			$term=$this->input->post('term');
		}
		
		$term=urldecode($term);
		
		$result=$this->people->match($term);

		$array=array();

		foreach ($result as $row){
			$array[]=array(
				'id'=>$row['id'],
				'type'=>lang($row['type'])?lang($row['type']):'',
				'name'=>$row['name'],
				'label'=>lang($row['type']).'　'.$row['name'],
				'value'=>$row['id'],
			);
		}
		$this->output->data=$array;
	}
	
	function index(){
		
		$this->config->set_user_item('search/limit', 'pagination', false);
		$this->config->set_user_item('search/order_by', 'people.id desc',false);
		
		$this->_search();
		
		$this->table->setFields($this->list_args)
			->setRowAttributes(array('hash'=>'{type}/{id}'))
			->setData($this->people->getList($this->config->user_item('search')));
		
		$this->load->view('list');
		$this->load->view('list_aside',true,'sidebar');
	}
	
	function add(){
		$this->people->id=$this->people->getAddingItem();
		
		if($this->people->id===false){
			$this->people->id=$this->people->add();
		}
		
		$this->edit($this->people->id);
	}
	
	function edit($id){

		$this->people->id=$id;
		
		$this->load->model('staff_model','staff');

		try{
			$this->people->data=array_merge($this->people->fetch($id),$this->input->sessionPost('people'));
			$this->people->labels=$this->people->getLabels($this->people->id);
			$this->people->profiles=array_sub($this->people->getProfiles($this->people->id),'content','name');

			if(!$this->people->data['name'] && !$this->people->data['abbreviation']){
				
				$this->output->title='未命名'.lang(CONTROLLER);
			}else{
				$this->output->title=$this->people->data['abbreviation']?$this->people->data['abbreviation']:$this->people->data['name'];
			}

			$available_options=$this->people->getAllLabels();
			$profile_name_options=$this->people->getProfileNames();

			$this->load->addViewData('relative_list', $this->relativeList());
			$this->load->addViewData('profile_list',$this->profileList());
			$this->load->addViewData('project_list', $this->projectList());
			$this->load->addViewData('schedule_list', $this->scheduleList());

			if($this->people->data['staff']){
				$this->people->data['staff_name']=$this->staff->fetch($this->people->data['staff'],'name');
			}

			$this->load->addViewArrayData(compact('controller','available_options','profile_name_options'));
			$this->load->addViewData('people', $this->people->data);
			$this->load->addViewData('labels', $this->people->labels);
			$this->load->addViewData('profiles', $this->people->profiles);

			if($this->input->post('character') && in_array($this->input->post('character'),array('个人','单位'))){
				post('people/character', $this->input->post('character'));
			}

			$this->load->view('edit');
			$this->load->view('edit_aside',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}
	}
	
	/**
	 * 返回相关人列表
	 */
	function relativeList(){
		
		$people=new People_model();
		
		$list=$this->table->setFields($this->relative_list_args)
			->setRowAttributes(array('hash'=>'{type}/{id}'))
			->setData($people->getList(array('is_relative_of'=>$this->people->id)))
			->generate();
		
		return $list;
	}
	
	/**
	 * 返回资料项列表
	 */
	function profileList(){

		$list=$this->table->setFields($this->profile_list_args)
			->setData($this->people->getProfiles($this->people->id))
			->generate();
		
		return $list;
	}
	
	/**
	 * 返回状态列表
	 */
	function statusList(){

		$list=$this->table->setFields($this->status_list_args)
			->setData($this->people->getStatus($this->people->id))
			->generate();
		
		return $list;
	}
	/**
	 * 返回相关项目列表
	 */
	function projectList(){
		
		$project=new Project_model();
		
		$this->project_list_args=array(
			'name'=>array(
				'heading'=>'名称'
			),
			'people'=>array('heading'=>'人员','cell'=>array('class'=>'ellipsis'),'parser'=>array('function'=>array($project,'getCompiledPeople'),'args'=>array('id')))
		);
		
		$list=$this->table->setFields($this->project_list_args)
			->setRowAttributes(array('hash'=>'{type}/{id}'))
			->setData($project->getList(array('people'=>$this->people->id,'limit'=>10,'orderby'=>'project.id DESC')))
			->generate();
		
		return $list;
	}

	/**
	 * 返回人员所在的组列表
	 */
	function teamList(){
		
		$team=new Team_model();
		
		$list=$this->table->setFields($this->team_list_args)
			->setRowAttributes(array('hash'=>'{type}/{id}'))
			->setData($team->getList(array('has_relative_like'=>$this->people->id,'get_leader'=>true,'limit'=>10)))
			->generate();
		
		return $list;
	}
	
	/**
	 * 返回人员相关日程列表
	 */
	function scheduleList(){
		
		$this->load->model('schedule_model','schedule');
		
		return $this->table->setFields($this->schedule_list_args)
			->setAttribute('name','schedule')
			->setRowAttributes(array('onclick'=>"$.viewSchedule({id:{id}})",'style'=>'cursor:pointer;'))
			->generate($this->schedule->getList(array('show_creater'=>true,'limit'=>10,'people'=>$this->people->id,'orderby'=>'id desc')));
	}
	
	/**
	 * 提交处理
	 */
	function submit($submit,$id,$button_id=NULL){

		$this->people->id=$id;
		
		$this->people->data=array_merge($this->people->fetch($id),$this->input->sessionPost('people'));
		
		$this->load->library('form_validation');
		
		try{
			
			if(isset($this->form_validation_rules[$submit])){
				$this->form_validation->set_rules($this->form_validation_rules[$submit]);
				if($this->form_validation->run()===false){
					$this->output->message(validation_errors(),'warning');
					throw new Exception;
				}
			}
		
			if($submit=='cancel'){
				unsetPost();
				$this->output->status='close';
			}

			elseif($submit=='people'){
				$this->people->labels=$this->input->sessionPost('labels');
				$this->people->profiles=$this->input->sessionPost('profiles');

				if(!$this->people->data['display']){
					$this->people->data['display']=true;
					$this->output->data=CONTROLLER.'/'.$this->people->id;
					$this->output->status='redirect';
				}
				
				if($this->people->data['character']!='单位' && !$this->people->data['gender']){
					//个人，则性别必填
					$this->output->message('选择性别','warning');
					throw new Exception;
				}
				
				if($this->people->data['birthday']===''){
					$this->people->data['birthday']=NULL;
				}
				
				$this->people->update($this->people->id,$this->people->data);
				$this->people->updateProfiles($this->people->id,$this->people->profiles);

				unsetPost();
				$this->output->message($this->output->title.' 已保存');
			}

			elseif($submit=='relative'){
				
				$relative=$this->input->sessionPost('relative');
				
				if(!$relative['id']){
					$this->people->profiles=$this->input->sessionPost('relative_profiles');
					
					if(count($this->people->profiles)==0){
						$this->output->message('请至少输入一种联系方式','warning');
						throw new Exception;
					}
					
					if(!$this->people->profiles['电话'] && !$this->people->profiles['电子邮件']){
						$this->output->message('至少输入一种联系方式', 'warning');
						throw new Exception;
					}

					foreach($this->people->profiles as $name => $content){
						if($name=='电话'){
							if($this->people->isMobileNumber($content)){
								$relative['profiles']['手机']=$content;
							}else{
								$relative['profiles']['电话']=$content;
							}
							$relative['phone']=$content;
						}elseif($name=='电子邮件' && $content){
							if(!$this->form_validation->valid_email($content)){
								$this->output->message('请填写正确的Email地址', 'warning');
								throw new Exception;
							}
							$relative['email']=$content;
						}else{
							$relative['profiles'][$name]=$content;
						}
					}

					$relative+=array(
						'type'=>'client',
						'abbreviation'=>$relative['name'],
						'character'=>isset($relative['character']) && $relative['character'] == '单位' ? '单位' : '个人',
						'profiles'=>$this->people->profiles,
						'labels'=>array('类型'=>'潜在客户')
					);
					
					$relative['display']=true;
					
					$relative['id']=$this->people->add($relative);
					$this->output->message('新客户 <a href="#'.CONTROLLER.'/' . $relative['id'] . '">' . $relative['name'] . ' </a>已经添加');
				}
				
				$this->people->addRelationship($this->people->id,$relative['id'],empty($relative['relation'])?NULL:$relative['relation']);

				$this->output->setData($this->relativeList(),'relative-list','content-table','.item[name="relative"]>.contentTable','replace');
				
				unsetPost('relative');

			}

			elseif($submit=='remove_relative'){
				$this->people->removeRelationship($this->people->id,$button_id);
				$this->output->setData($this->relativeList(),'relative-list','content-table','.item[name="relative"]>.contentTable','replace');
			}

			elseif($submit=='profile'){
				$profile=$this->input->sessionPost('profile');
				
				if(!$profile['name']){
					$this->output->message('请选择资料项名称','warning');
					throw new Exception;
				}
				
				$this->people->addProfile($this->people->id,$profile['name'],$profile['content'],$profile['comment']);
				
				$this->output->setData($this->profileList(),'profile-list','content-table','.item[name="profile"]>.contentTable','replace');
				
				unsetPost('profile');
			}

			elseif($submit=='remove_profile'){
				$this->people->removeProfile($this->people->id,$button_id);
				$this->output->setData($this->profileList(),'profile-list','content-table','.item[name="profile"]>.contentTable','replace');
			}
			
			elseif($submit=='status'){
				$status=$this->input->sessionPost('status');
				
				if(!$status['name']){
					$this->output->message('请填写状态标题','warning');
					throw new Exception;
				}
				
				$this->people->addStatus($this->people->id,$status['name'],$status['date'],$status['content'],$status['team'],$status['comment']);
				
				$this->output->setData($this->statusList(),'status-list','content-table','.item[name="status"]>.contentTable','replace');
				
				unsetPost('status');
			}

			elseif($submit=='remove_status'){
				$this->people->removestatus($this->people->id,$button_id);
				$this->output->setData($this->statusList(),'status-list','content-table','.item[name="status"]>.contentTable','replace');
			}
			
			elseif($submit=='changetype'){
				$this->people->update($this->people->id,array('type'=>$this->input->post('type')));
			}
			
			if(is_null($this->output->status)){
				$this->output->status='success';
			}
			
		}catch(Exception $e){
			$this->output->status='fail';
		}
	}
}
?>
