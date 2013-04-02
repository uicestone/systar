<?php
class Project extends SS_controller{
	
	var $section_title='事项';
	
	var $form_validation_rules=array();
	
	var $list_args;
	
	var $people_list_args;
	
	var $staff_list_args;
	
	var $fee_list_args;
	
	var $miscfee_list_args;
	
	var $status_list_args;
	
	var $schedule_list_args;
	
	var $plan_list_args;

	var $document_list_args;
	
	function __construct(){
		parent::__construct();
		
		$controller=CONTROLLER;
		
		$this->form_validation_rules['people'][]=array('rules'=>'required','label'=>'相关人员姓名','field'=>'people[name]');
		
		$this->list_args=array(
			'name'=>array('heading'=>'案名','cell'=>'{name}'),
			'people'=>array('heading'=>'人员','parser'=>array('function'=>array($this->$controller,'getCompiledPeople'),'args'=>array('{id}'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->$controller,'getCompiledLabels'),'args'=>array('{id}')))
			/*
			 * 此处被迫使用了$this->$controller来调用被继承后的model。
			 * 因为Project::__construct()时，Cases::__construct()尚未运行，
			 * $this->project=$this->cases也尚未运行，因此$this->project未定义
			 */
		);
		
		$this->people_list_args=array(
			'name'=>array('heading'=>'名称','cell'=>'{abbreviation}<button type="submit" name="submit[remove_people]" id="{id}" class="hover">删除</button>'),
			'role'=>array('heading'=>'角色')
		);
		
		$this->schedule_list_args=array(
			'name'=>array('heading'=>array('data'=>'标题','width'=>'150px'),'wrap'=>array('mark'=>'span','class'=>'show-schedule','id'=>'{id}')),
			'time_start'=>array('heading'=>array('data'=>'时间','width'=>'60px'),'eval'=>true,'cell'=>"
				if('{time_start}') return date('m-d H:i','{time_start}');
			"),
			'username'=>array('heading'=>array('data'=>'填写人','width'=>'90px'))
		);
		
		$this->plan_list_args=array(
			'name'=>array('heading'=>array('data'=>'标题','width'=>'150px'),'wrap'=>array('mark'=>'span','class'=>'show-schedule','id'=>'{id}')),
			'time_start'=>array('heading'=>array('data'=>'时间','width'=>'60px'),'eval'=>true,'cell'=>"
				if('{time_start}') return date('m-d H:i','{time_start}');
			"),
			'username'=>array('heading'=>array('data'=>'填写人','width'=>'90px'))
		);
		
		$this->status_list_args=array();
		
		$this->fee_list_args=array(
			'type'=>array('heading'=>'类型','cell'=>'{type}<button type="submit" name="submit[remove_fee]" id="{id}" class="hover">删除</button>'),
			'fee'=>array('heading'=>'数额','eval'=>true,'cell'=>"
				\$return='{fee}'.('{fee_received}'==''?'':' <span title=\"{fee_received_time}\">（到账：{fee_received}）</span>');
				if('{reviewed}'){
					\$return=wrap(\$return,array('mark'=>'span','style'=>'color:#AAA'));
				}
				return \$return;
			"),
			'condition'=>array('heading'=>'条件','cell'=>array('class'=>'ellipsis','title'=>'{condition}')),
			'pay_date'=>array('heading'=>'预计时间')
		);
	}
	
	function index(){
		
		//根据来自边栏的提交选项，筛选列表
		
		if($this->input->post('name')!==false){
			option('search/name',$this->input->post('name'));
		}
		
		if($this->input->post('name')===''){
			option('search/name',NULL);
		}
		
		if($this->input->post('labels')){
			
			if(is_null(option('search/labels'))){
				option('search/labels',array());
			}
			
			option('search/labels',$this->input->post('labels'))+option('search/labels');
		}
		
		//提交了搜索项，但搜索项中没有labels项，我们将session中搜索项的labels项清空
		if($this->input->post('submit')==='search' && $this->input->post('labels')===false){
			option('search/labels',array());
		}
		
		//点击了取消搜索按钮，则清空session中的搜索项
		if($this->input->post('submit')==='search_cancel'){
			option('search/name',NULL);
			option('search/labels',array());
		}
		
		if(is_null(option('search/people'))){
			option('search/people',$this->user->id);
		}
		
		if($this->config->item(CONTROLLER.'/index/search/type')!==false){
			option('search/type',$this->config->item('project/index/search/type'));
		}
		
		$table=$this->table->setFields($this->list_args)
			->setRowAttributes(array('hash'=>(CONTROLLER==='query'?'cases':CONTROLLER).'/edit/{id}'))
			->setData($this->project->getList(option('search')))
			->generate();
		
		$this->load->addViewData('list',$table);
		$this->load->view('list');
		$this->load->view('project/list_sidebar',true,'sidebar');
	}
	
	function add(){
		$data=array();
		if($this->config->item('project/index/search/type')!==false){
			$data['type']=$this->config->item('project/index/search/type');
		}
		$this->project->id=$this->project->add($data);
		$this->project->addPeople($this->project->id, $this->user->id, NULL, '创建人');
		$this->edit($this->project->id);
		redirect('#'.CONTROLLER.'/edit/'.$this->project->id);
	}
	
	function edit($id){
		$this->project->id=$id;
		
		try{
			$this->project->data=array_merge($this->project->fetch($id),$this->input->sessionPost('project'));

			$this->project->labels=array_merge($this->project->getLabels($this->project->id),$this->input->sessionPost('labels'));

			if(!$this->project->data['name']){
				$this->section_title='未命名'.$this->section_title;
			}else{
				$this->section_title=$this->project->data['name'];
			}
			
			$this->load->addViewData('project', $this->project->data);
			
			$this->load->addViewData('labels', $this->project->labels);

			$this->load->addViewData('people_list', $this->peopleList());
			
			$this->load->addViewData('schedule_list', $this->scheduleList());
			
			$this->load->addViewData('document_list', $this->documentList());

			$this->load->view('project/edit');
			
			$this->load->view('project/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}

	}
	
	function peopleList(){
		$this->load->model('people_model','people');
		
		return $this->table->setFields($this->people_list_args)
			->setRowAttributes(array('hash'=>'people/edit/{id}'))
			->setAttribute('name', 'people')
			->generate($this->people->getList(array('limit'=>false,'project'=>$this->project->id)));
	}

	function staffList(){
		
		$this->load->model('people_model','people');
		
		$list=$this->table->setFields($this->staff_list_args)
			->setAttribute('name','staff')
			->setRowAttributes(array('hash'=>'staff/edit/{id}'))
			->generate($this->people->getList(array('project'=>$this->project->id,'type'=>'职员')));
		
		return $list;
	}
	
	function feeList(){
		$list=$this->table->setFields($this->fee_list_args)
				->setAttribute('name','fee')
				->generate($this->project->getFeeList($this->project->id));
		
		return $list;
	}
	
	function documentList(){
		$this->load->model('document_model','document');

		$this->document_list_args=array(
			'name'=>array('heading'=>'文件名','cell'=>'<a href="/document/download/{id}">{name}</a>'),
			'time_insert'=>array('heading'=>'上传时间','parser'=>array('function'=>function($time_insert){return date('Y-m-d H:i:s',$time_insert);},'args'=>array('{time_insert}'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->document,'getCompiledLabels'),'args'=>array('{id}')))
		);
		
		return $this->table->setFields($this->document_list_args)
			->setAttribute('name','document')
			->generate($this->document->getList(array('project'=>$this->project->id)));
	}
	
	function scheduleList(){
		
		$this->load->model('schedule_model','schedule');
		
		return $this->table->setFields($this->schedule_list_args)
			->setAttribute('name','schedule')
			//@TODO 点击列表打开日程尚有问题
			->setRowAttributes(array('onclick'=>"$.viewSchedule(\{id:{id}\})"))
			->generate($this->schedule->getList(array('limit'=>10,'project'=>$this->project->id,'completed'=>true)));
	}
	
	function planList(){
		return $this->table->setFields($this->plan_list_args)
			->setAttribute('name','plan')
			->generate($this->schedule->getList(array('limit'=>10,'project'=>$this->project->id,'completed'=>false)));
	}
	
	function statusList(){
		
	}
	
	function submit($submit,$id,$button_id=NULL){
		
		$this->project->id=$id;
		
		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		
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
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]);
				$this->output->status='close';
			}
		
			elseif($submit=='project'){
				$this->project->labels=$this->input->sessionPost('labels');
				$this->project->update($this->project->id,$this->project->data);
				$this->project->updateLabels($this->project->id,$this->project->labels);
				
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]);
				$this->output->status='close';
			}
			
			elseif($submit=='case_client'){
				
				//这样对数组做加法，后者同名键不会替换前者，即后者是前者的补充，而非更新
				$project_client=$this->input->sessionPost('case_client');
				$client=$this->input->sessionPost('client');
				$client_profiles=$this->input->sessionPost('client_profiles');
				$client_labels=$this->input->sessionPost('client_labels');
				
				if(!$project_client['role']){
					$this->output->message('请选择本案地位','warning');
					throw new Exception;
				}
		
				if($project_client['client']){//autocomplete搜索到已有客户
					$this->output->message("系统中已经存在{$client['name']}，已自动识别");
				}
				else{//添加新客户
					if(!$client['name']){
						$this->output->message('请输入客户或相关人名称', 'warning');
						throw new Exception;
					}
					$new_client=array(
						'name'=>$client['name'],
						'character'=>isset($client['character']) && $client['character']=='单位'?'单位':'个人',
						'type'=>$client['type'],
						'labels'=>$client_labels
					);
					
					if(!$client_profiles['电话'] && !$client_profiles['电子邮件']){
						$this->output->message('至少输入一种联系方式', 'warning');
						throw new Exception;
					}
					
					foreach($client_profiles as $name => $content){
						if($name=='电话'){
							if($this->client->isMobileNumber($content)){
								$new_client['profiles']['手机']=$content;
							}else{
								$new_client['profiles']['电话']=$content;
							}
							$new_client['phone']=$content;
						}elseif($name=='电子邮件' && $content){
							if(!$this->form_validation->valid_email($content)){
								$this->output->message('请填写正确的Email地址', 'warning');
								throw new Exception;
							}
							$new_client['email']=$content;
						}else{
							$new_client['profiles'][$name]=$content;
						}
					}

					if($client['type']=='客户'){//客户必须输入来源
						if(!$client_profiles['来源类型']){
							$this->output->message('请选择客户来源类型','warning');
							throw new Exception;
						}
						$client['staff']=$this->staff->check($client['staff_name']);

						$new_client['staff']=$client['staff'];

					}else{//非客户必须输入工作单位
						if(!$client['work_for']){
							$this->output->message('请输入工作单位','warning');
							throw new Exception;
						}
					}
					
					if($client['work_for']){
						$new_client['work_for']=$client['work_for'];
					}
					
					$project_client['client']=$this->client->add($new_client);

					$this->output->message(
						'<a href="#'.
						($client['type']=='客户'?'client':'contact').
						'/edit/'.$project_client['client'].'">新'.
						$client['type'].' '.$client['name'].
						' 已经添加，点击编辑详细信息</a>'
					);
				}

				if($this->project->addPeople($this->project->id,$project_client['client'],'客户',$project_client['role'])){
					$this->output->setData($this->clientList(),'content-table','html','.item[name="client"]>.contentTable','replace');
				}else{
					$this->output->message('客户添加错误', 'warning');
					throw new Exception;
				}
				
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['case_client']);
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['client']);
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['client_profiles']);
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['client_labels']);
			}

			elseif($submit=='staff'){
				
				$staff=$this->input->sessionPost('staff');
				if(!$staff['id']){
					$staff['id']=$this->staff->check($staff['name']);
					
					if($staff['id']){
						post('staff/id',$staff['id']);
					}else{
						$this->output->message('请输入职员名称','warning');
						throw new Exception;
					}
				}
				
				$project_role=$this->project->getRoles($this->project->id);
		
				$responsible_partner=$this->project->getPartner($project_role);
				//获得本案督办人的id
				$my_roles=$this->project->getMyRoles($project_role);
				//本人的本案职位

				if(!$responsible_partner && $staff['role']!='督办人'){
					//第一次插入督办人后不显示警告，否则如果不存在督办人则显示警告
					$this->output->message('未设置督办人','notice');
				}

				if(!$staff['role']){
					$this->output->message('未选择本案职务','warning');
					throw new Exception();
				}

				if($this->project->addStaff($this->project->id,post('staff/id'),post('staff/role'),post('staff/hourly_fee'))){
					$this->output->setData($this->staffList(),'content-table','html','.item[name="staff"]>.contentTable','replace');
					unset($_SESSION[CONTROLLER]['post'][$this->project->id]['staff']['id']);
				}else{
					$this->output->message('人员添加错误', 'warning');
				}

				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['staff']);
			}
			
			elseif($submit=='remove_staff'){
				if($this->project->removePeople($this->project->id,$button_id)){
					$this->output->setData($this->staffList(),'content-table','html','.item[name="staff"]>.contentTable','replace');
				}
			}
			
			elseif($submit=='people'){
				
				$this->load->model('people_model','people');
				
				$people=$this->input->sessionPost('people');
				if(!$people['id']){
					$people['id']=$this->people->check($people['name']);
					
					if($people['id']){
						post('people/id',$people['id']);
					}else{
						$this->output->message('请输入人员名称','warning');
						throw new Exception;
					}
				}
				
				if($this->project->addPeople($this->project->id,$people['id'],NULL,$people['role'])){
					$this->output->setData($this->peopleList(),'content-table','html','.item[name="people"]>.contentTable','replace');
					unset($_SESSION[CONTROLLER]['post'][$this->project->id]['people']['id']);
				}else{
					$this->output->message('人员添加错误', 'warning');
				}

				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['people']);
			}
			
			elseif($submit=='remove_people'){
				if($this->project->removePeople($this->project->id,$button_id)){
					$this->output->setData($this->peopleList(),'content-table','html','.item[name="people"]>.contentTable','replace');
				}
			}
			
			elseif($submit=='project_account'){
				
				$project_account=$this->input->sessionPost('project_account');

				if(!$project_account['type']){
					$this->output->message('请选择收费类型','warning');
				}
				
				if(!is_numeric($project_account['fee'])){
					$this->output->message('请预估收费金额（数值）','warning');
				}
				
				if(!$project_account['pay_date']){
					$this->output->message('请预估收费时间','warning');
				}
				
				if(count($this->output->message['warning'])>0){
					throw new Exception;
				}
				
				if($this->project->addFee($this->project->id,$project_account['fee'],$project_account['pay_date'],$project_account['type'],$project_account['condition'])){
					//unset($_SESSION['cases']['post']['project_account']);
					$this->output->setData($this->feeList(),'content-table','html','.item[name="fee"]>.contentTable','replace');
				}else{
					$this->output->message('收费添加错误', 'warning');
				}
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['project_account']);
			}
			
			elseif($submit=='remove_fee' || $submit=='remove_miscfee'){
				$this->project->removeFee($this->project->id,$button_id);
				
				if($submit=='remove_fee'){
					$this->output->setData($this->feeList(),'content-table','html','.item[name="fee"]>.contentTable','replace');
				}else{
					$this->output->setData($this->miscfeeList(),'content-table','html','.item[name="miscfee"]>.contentTable','replace');
				}
			}
			
			elseif($submit=='case_fee_review' && $this->user->isLogged('finance')){
				//财务审核
				$this->project->ignoreFee($fee_ids);
			}
			
			elseif($submit=='case_fee_timing'){
				
				$timing_fee=$this->input->sessionPost('case_fee_timing');

				if(!$timing_fee){
					$this->project->removeTimingFee($this->project->id);
				}

				if(
					post(CONTROLLER.'/timing_fee') && 
					(!$timing_fee['date_start'] || $timing_fee['included_hours']==='')
				){
					$this->output->message('账单起始日或包含小时数未填','warning');
					throw new Exception();

				}else{
					if($this->project->setTimingFee($this->project->id,$timing_fee['date_start'],$timing_fee['bill_day'],$timing_fee['payment_day'],$timing_fee['included_hours'],$timing_fee['payment_cycle'],$timing_fee['contract_cycle'])){
						unset($_SESSION[CONTROLLER]['post'][$this->project->id]['case_fee_timing']);
					}
				}
				
				$this->output->status='refresh';
			}
			
			elseif($submit=='case_fee_misc'){
				
				$misc_fee=$this->input->sessionPost('case_fee_misc');
				
				if(!$misc_fee['receiver']){
					$this->output->message('请选择办案费收款方','warning');
				}
				
				if(!$misc_fee['fee']){
					$this->output->message('请填写办案费约定金额（数值）','warning');
				}
				
				if(!$misc_fee['pay_date']){
					$this->output->message('请填写收费时间','warning');
				}
				
				if(count($this->output->message['warning'])>0){
					throw new Exception();
				}
				
				if($this->project->addFee($this->project->id,$misc_fee['fee'],$misc_fee['pay_date'],'办案费',NULL,$misc_fee['receiver'],$misc_fee['comment'])){
					$this->output->setData($this->miscfeeList(),'content-table','html','.item[name="miscfee"]>.contentTable','replace');
				}else{
					$this->output->message('收费添加错误', 'warning');
				}
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['case_fee_misc']);
			}
			
			elseif($submit=='document'){
				$this->load->model('document_model','document');
				
				$document=$this->input->sessionPost('document');
				
				$document_labels=$this->input->sessionPost('document_labels');
				
				if(!$document['id']){
					$this->output->message('请选择要上传的文件', 'warning');
					throw new Exception;
				}
				
				$this->document->update($id, $document);
				
				$this->document->updateLabels($document['id'],$document_labels);
				
				$this->project->addDocument($this->project->id, $document['id']);
				
				$this->output->setData($this->documentList(),'content-table','html','.item[name="document"]>.contentTable','replace');
				
				unset($_SESSION[CONTROLLER]['post'][$this->project->id]['document']);
			}
			
			elseif($submit=='remove_document'){
				if($this->project->removeDocument($this->project->id,$button_id)){
					$this->output->setData($this->documentList(),'content-table','html','.item[name="document"]>.contentTable','replace');
				}
			}
			
			elseif($submit=='file_document_list'){
				
				$this->load->model('document_model','document');

				$document_catalog=$this->project->getDocumentCatalog($this->project->id,post('case_document_check'));

				$this->load->view('case/document_catalog');
			}
			
			elseif($submit=='new_case'){
				$this->project->removeLabel($this->project->id, '已归档');
				$this->project->removeLabel($this->project->id, '咨询');
				$this->project->addLabel($this->project->id, '等待立案审核');
				$this->project->addLabel($this->project->id, '案件');
				$this->project->update($this->project->id,array(
					'num'=>NULL,
					'time_contract'=>$this->date->today,
					'time_end'=>date('Y-m-d',$this->date->now+100*86400)
				));
				
				$this->output->message('已立案，请立即获得案号');
				
				$this->output->status='refresh';
			}
			
			elseif($submit=='file' && in_array('咨询',$this->project->labels)){
				$this->project->addLabel($this->project->id, '已归档');
				$this->output->status='refresh';
				$this->output->message('咨询案件已归档');
			}
			
			elseif($submit=='review'){
				$this->project->removeLabel($this->project->id, '等待立案审核');
				$this->project->addLabel($this->project->id, '在办');
				$this->output->status='refresh';
				$this->output->message('通过立案审核');
			}
			
			elseif($submit=='apply_lock'){
				//@TODO申请锁定，通过标签和消息系统来解决
			}
			
			elseif($submit=='lock_type'){
				$this->project->addLabel($this->project->id, '类型已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_client'){
				$this->project->addLabel($this->project->id, '客户已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_staff'){
				$this->project->addLabel($this->project->id, '职员已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_fee'){
				$this->project->addLabel($this->project->id, '费用已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_client'){
				$this->project->removeLabel($this->project->id, '客户已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_staff'){
				$this->project->removeLabel($this->project->id, '职员已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_fee'){
				$this->project->removeLabel($this->project->id, '费用已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='apply_file'){
				$this->project->addLabel($this->project->id, '已申请归档');
				$this->project->update($this->project->id,array(
					'time_end'=>$this->date->today
				));
				$this->output->status='refresh';
				$this->output->message('归档申请已接受');
			}
			
			elseif($submit=='review_finance'){
				$this->project->addLabel($this->project->id, '通过财务审核');
				$this->output->status='refresh';
				$this->output->message('结案财务状况已经审核');
			}
			
			elseif($submit=='review_info'){
				$this->project->addLabel($this->project->id, '通过信息审核');
				$this->output->status='refresh';
				$this->output->message('案件信息已经审核');
			}
			
			elseif($submit=='review_manager'){
				$this->project->addLabel($this->project->id, '通过主管审核');
				$this->project->update($this->project->id,array(
					'time_end'=>$this->date->today,
				));
				$this->output->status='refresh';
				$this->output->message('案件已经审核，已正式归档');
			}
			
			elseif(!in_array('咨询',$this->project->labels) && $submit=='file'){
				$this->project->removeLabel($this->project->id, '已申请归档');
				$this->project->addLabel($this->project->id, '案卷已归档');
				$this->output->status='refresh';
				$this->output->message('案卷归档归档完成');
			}

			elseif($submit=='apply_project_num'){
				
				$this->project->labels=$this->input->sessionPost('labels');
				
				if(!$this->project->labels['领域']){
					$this->output->message('获得案号前要先选择案件领域','warning');
					throw new Exception();
				}

				if(!$this->project->labels['分类']){
					$this->output->message('获得案号前要先选择案件分类','warning');
					throw new Exception();
				}

				$data=array(
					'num'=>$this->project->getNum($this->project->id, $this->project->labels['分类'], $this->project->labels['领域'], in_array('咨询', $this->project->labels), $this->project->data['first_contact'], $this->project->data['time_contract']),
				);
				
				$this->project->labels[]='类型已锁定';
				
				$this->project->update($this->project->id,$data);
				
				$this->project->updateLabels($this->project->id, $this->project->labels);
				
				$this->output->status='refresh';
			}

			if(is_null($this->output->status)){
				$this->output->status='success';
			}

		}catch(Exception $e){
			$e->getMessage() && $this->output->message($e->getMessage(), 'warning');
			$this->output->status='fail';
		}
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