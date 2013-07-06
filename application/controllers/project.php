<?php
class Project extends SS_controller{
	
	var $form_validation_rules=array();
	
	var $search_items=array();
	
	var $list_args;
	
	var $people_list_args;
	
	var $account_list_args;
	
	var $miscfee_list_args;
	
	var $status_list_args;
	
	var $schedule_list_args;
	
	var $plan_list_args;

	var $document_list_args;
	
	var $relative_list_args;
	
	function __construct(){
		parent::__construct();
		$this->load->model('project_model','project');
		
		$this->form_validation_rules['people'][]=array('rules'=>'required','label'=>'人员','field'=>'people[id]');
		$this->form_validation_rules['account']=array(
			array('field'=>'account[type]','label'=>'收费类型','rules'=>'required'),
			array('field'=>'account[amount]','label'=>'金额','rules'=>'required|numeric'),
			array('field'=>'account[date]','label'=>'收费日起','rules'=>'required')
		);
		
		$this->search_items=array('num','name','labels','people');

		$this->list_args=array(
			'name'=>array('heading'=>'名称','cell'=>'{name}'),
			'people'=>array('heading'=>'人员','cell'=>array('class'=>'ellipsis'),'parser'=>array('function'=>array($this->project,'getCompiledPeople'),'args'=>array('id'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->project,'getCompiledLabels'),'args'=>array('id')))
		);
		
		$this->people_list_args=array(
			'name'=>array('heading'=>'名称','cell'=>'{abbreviation}'),
			'role'=>array('heading'=>'角色')
		);
		
		$this->schedule_list_args=array(
			'name'=>array('heading'=>array('data'=>'标题'),'wrap'=>array('mark'=>'span','class'=>'show-schedule','id'=>'{id}')),
			'start'=>array('heading'=>array('data'=>'时间'),'parser'=>array('function'=>function($start){
				return $start?date('Y-m-d H:i',intval($start)):null;
			},'args'=>array('start'))),
			'creater_name'=>array('heading'=>array('data'=>'人员'))
		);
		
		$this->plan_list_args=array(
			'name'=>array('heading'=>array('data'=>'标题'),'wrap'=>array('mark'=>'span','class'=>'show-schedule','id'=>'{id}')),
			'start'=>array('heading'=>array('data'=>'时间'),'parser'=>array('function'=>function($start){
				return $start?date('Y-m-d H:i',intval($start)):null;
			},'args'=>array('start'))),
			'creater_name'=>array('heading'=>array('data'=>'人员'))
		);
		
		$this->status_list_args=array();
		
		$this->account_list_args=array(
			'account'=>array('heading'=>'帐目编号'),
			'type'=>array('heading'=>'类型','cell'=>'{type}'),
			'amount'=>array('heading'=>array('data'=>'数额','width'=>'30%'),'parser'=>array('function'=>function($total,$received,$received_date){
				return $total.($received==''?'':' <span title="'.$received_date.'">（到账：'.$received.'）</span>');
			},'args'=>array('total_amount','received_amount','received_date'))),
			'receivable_date'=>array('heading'=>'预计时间'),
			'comment'=>array('heading'=>'条件/备注','cell'=>array('class'=>'ellipsis','title'=>'{comment}'))
		);
		
		$this->relative_list_args=array(
			'name'=>array('heading'=>'名称'),
			'relation'=>array('heading'=>'关系'),
		);
		
		$this->load->view_path['list_aside']='project/list_sidebar';
		$this->load->view_path['edit']='project/edit';
		$this->load->view_path['edit_aside']='project/edit_sidebar';
		
	}
	
	function index(){
		
		$this->load->model('staff_model','staff');//用于边栏职员搜索

		!$this->user->isLogged('developer') && $this->config->set_user_item('search/people', array_merge(array($this->user->id),array_keys($this->user->teams)), false,'method',false);
		$this->config->set_user_item('search/order_by', 'project.id desc', false);
		$this->config->set_user_item('search/limit', 'pagination', false);
		
		$this->_search();
		
		$this->table->setFields($this->list_args)
			->setRowAttributes(array('hash'=>'{type}/{id}'))
			->setData($this->project->getList($this->config->user_item('search')));
		
		$this->load->view('list');
		$this->load->view('list_aside',true,'sidebar');
	}
	
	function archived(){
		$this->output->title='已归档'.$this->output->title;
		$this->config->set_user_item('search/active', false, false);
		$this->index();
	}
	
	function add(){
		$this->project->id=$this->project->getAddingItem();
		
		if($this->project->id===false){
			$data=array();
			if($this->config->user_item('project/index/search/type')!==false){
				$data['type']=$this->config->item('project/index/search/type');
			}
			if($this->input->get('labels')!==false){
				$labels=explode(' ',urldecode($this->input->get('labels')));
				$this->config->set_user_item('search/labels', $labels, false);
			}
			$this->project->id=$this->project->add($data);
			$this->project->addPeople($this->project->id, $this->user->id, NULL, '创建人');
			$this->project->addLabels($this->project->id, $this->config->user_item('search/labels'));
		}
		
		$this->edit($this->project->id);
	}
	
	function edit($id){
		$this->project->id=$id;
		
		try{
			$this->project->data=array_merge($this->project->fetch($id),$this->input->sessionPost('project'));

			$this->project->labels=array_merge($this->project->getLabels($this->project->id),$this->input->sessionPost('labels'));

			if(!$this->project->data['name']){
				$this->output->title='未命名'.lang(CONTROLLER);
			}else{
				$this->output->title=$this->project->data['name'];
			}
			
			$this->load->addViewData('project', $this->project->data);
			
			$this->load->addViewData('labels', $this->project->labels);

			$this->load->addViewData('people_list', $this->peopleList());
			
			$this->load->addViewData('schedule_list', $this->scheduleList());
			
			$this->load->addViewData('document_list', $this->documentList());
			
			$this->load->addViewData('relative_list', $this->relativeList());

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
	
	function peopleList(){
		$this->people_list_args['role']=array('heading'=>'角色','parser'=>array('function'=>array($this->project,'getCompiledPeopleRoles'),'args'=>array($this->project->id,'id')));
		
		return $this->table->setFields($this->people_list_args)
			->setRowAttributes(array('hash'=>'{type}/{id}'))
			->setAttribute('name', 'people')
			->generate($this->people->getList(array('in_project'=>$this->project->id)));
	}

	function accountList(){
		
		$this->load->model('account_model','account');
		
		$list=$this->table->setFields($this->account_list_args)
				->setAttribute('name','account')
				->generate($this->account->getList(array('project'=>$this->project->id,'group_by'=>'account')));
		
		return $list;
	}
	
	function documentList(){
		$this->load->model('document_model','document');

		$this->document_list_args=array(
			'name'=>array('heading'=>'文件名','parser'=>array('function'=>function($id,$name,$filename){
				if($name==''){
					$name=$filename;
				}
				return '<a href="/document/download/'.$id.'">'.$name.'</a>';
			},'args'=>array('id','name','filename'))),
			'time_insert'=>array('heading'=>'上传时间','parser'=>array('function'=>function($time_insert){return date('Y-m-d H:i:s',$time_insert);},'args'=>array('time_insert'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->document,'getCompiledLabels'),'args'=>array('id')))
		);
		
		return $this->table->setFields($this->document_list_args)
			->setAttribute('name','document')
			->setRowAttributes(array('hash'=>'document/{id}'))
			->generate($this->document->getList(array('project'=>$this->project->id)));
	}
	
	function scheduleList(){
		
		$this->load->model('schedule_model','schedule');
		
		return $this->table->setFields($this->schedule_list_args)
			->setAttribute('name','schedule')
			->setRowAttributes(array('onclick'=>"$.viewSchedule({id:{id}})",'style'=>'cursor:pointer;'))
			->generate($this->schedule->getList(array('show_creater'=>true,'limit'=>10,'project'=>$this->project->id,'orderby'=>'id desc')));
	}
	
	function statusList(){
		
	}
	
	/**
	 * 相关项目列表
	 */
	function relativeList(){
		return $this->table->setFields($this->relative_list_args)
			->setAttribute('name','relatives')
			->setRowAttributes(array('hash'=>CONTROLLER.'/{id}'))
			->generate($this->project->getList(array('limit'=>10,'is_relative_of'=>$this->project->id)));
	}
	
	function submit($submit,$id,$button_id=NULL){
		
		$this->project->id=$id;
		
		$this->project->data=array_merge($this->project->fetch($id),$this->input->sessionPost('project'));
		$this->project->labels=array_merge($this->project->getLabels($this->project->id),$this->input->sessionPost('labels'));
		
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
		
			elseif($submit=='project'){
				
				if(!$this->project->data['display']){
					$this->project->data['display']=true;
					$this->output->data=CONTROLLER.'/'.$this->project->id;
					$this->output->status='redirect';
				}
				
				$this->project->update($this->project->id,$this->project->data);
				$this->project->updateLabels($this->project->id,$this->input->sessionPost('labels'));
				
				unsetPost();
				$this->output->message($this->output->title.' 已保存');
			}
			
			elseif($submit=='people'){
				
				$people=$this->input->sessionPost('people');
				
				if($this->project->addPeople($this->project->id,$people['id'],NULL,$people['role'])){
					$this->message->send('将你加入事务：'.$this->project->data['name'],$people['id']);
					$this->output->setData($this->peopleList(),'people-list','content-table','.item[name="people"]>.contentTable','replace');
				}else{
					$this->output->message('人员添加错误', 'warning');
				}

				unsetPost('people');
			}
			
			elseif($submit=='remove_people'){
				if($this->project->removePeople($this->project->id,$button_id)){
					$this->output->setData($this->peopleList(),'people-list','content-table','.item[name="people"]>.contentTable','replace');
				}
			}
			
			elseif($submit=='account'){
				
				$this->load->model('account_model','account');
				
				$account=$this->input->sessionPost('account');

				if(!is_numeric($account['amount'])){
					$this->output->message('请预估收费金额（数值）','warning');
				}
				
				if(!$account['date']){
					$this->output->message('请预估收费时间','warning');
				}
				
				if(count($this->output->message['warning'])>0){
					throw new Exception;
				}
				
				if($this->cases->data['type']==='query'){
					$subject='咨询费';
				}elseif(in_array('法律顾问',$this->project->labels)){
					$subject='顾问费';
				}else{
					$subject='律师费';
				}
				
				$this->account->add($account+array('project'=>$this->project->id,'subject'=>$subject,'display'=>true));
				$this->output->setData($this->accountList(),'account-list','content-table','.item[name="account"]>.contentTable','replace');
				
				unsetPost('account');
			}
			
			elseif($submit=='remove_fee' || $submit=='remove_miscfee'){
				$this->project->removeFee($this->project->id,$button_id);
				
				if($submit=='remove_fee'){
					$this->output->setData($this->accountList(),'account-list','content-table','.item[name="account"]>.contentTable','replace');
				}else{
					$this->output->setData($this->miscfeeList(),'miscfee-list','content-table','.item[name="miscfee"]>.contentTable','replace');
				}
			}
			
			elseif($submit=='document'){
				$this->load->model('document_model','document');
				
				$document=$this->input->sessionPost('document');
				
				$document_labels=$this->input->sessionPost('document_labels');
				
				if(!$document['id']){
					$this->output->message('请选择要上传的文件', 'warning');
					throw new Exception;
				}
				
				$this->document->update($document['id'], $document);
				
				$this->document->addLabels($document['id'],$document_labels);
				
				$this->document->addmod(1,$this->people->getArray(array('in_project'=>$this->project->id),'id'),$document['id']);
				
				$this->project->addDocument($this->project->id, $document['id']);
				
				$this->output->setData($this->documentList(),'document-list','content-table','.item[name="document"]>.contentTable','replace');
				
				unsetPost('document');
			}
			
			elseif($submit=='remove_document'){
				if($this->project->removeDocument($this->project->id,$button_id)){
					$this->output->setData($this->documentList(),'document-list','content-table','.item[name="document"]>.contentTable','replace');
				}
			}
			
			if(is_null($this->output->status)){
				$this->output->status='success';
			}

		}catch(Exception $e){
			$e->getMessage() && $this->output->message($e->getMessage(), 'warning');
			$this->output->status='fail';
		}
	}
	
	function removePeopleRole($project_id,$people_id){
		
		$this->project->id=$project_id;
		
		$role=$this->input->post('role');
		
		$this->project->removePeopleRole($project_id,$people_id,$role);
		$this->output->setData($this->peopleList(),'people-list','content-table','.item[name="people"]>.contentTable','replace');
	}
	
	function match(){

		$term=$this->input->post('term');

		$result=$this->project->getList(array('people'=>$this->user->id));//只匹配到当前用户参与的案件

		$array=array();

		foreach ($result as $row){
			if(strpos($row['case_name'], $term)!==false){
				$array[]=array(
					'label'=>strip_tags($row['case_name']).' - '.$row['num'],
					'value'=>$row['id']
				);
			}
		}
		
		$this->output->data=$array;
	}
}
?>