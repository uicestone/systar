<?php
class Cases extends Project{
	
	var $client_list_args;
	
	var $staff_list_args;
	
	function __construct() {
		parent::__construct();
		$this->load->model('cases_model','cases');
		
		$this->project=$this->cases;

		$this->search_items=array('name','num','people','time_contract/from','time_contract/to','labels','without_labels');

		$this->list_args=array(
			'time_contract'=>array(
				'heading'=>array('data'=>'案号','width'=>'140px'),
				'cell'=>array('data'=>'{num}','title'=>'立案时间：{time_contract}')
			),
			'name'=>array('heading'=>'案名','cell'=>'{name}'),
			'responsible'=>array('heading'=>array('data'=>'主办律师','width'=>'110px'),'parser'=>array('function'=>array($this->cases,'getResponsibleStaffNames'),'args'=>array('id'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->cases,'getCompiledLabels'),'args'=>array('id')))
		);
		
		$this->account_list_args=array(
			'account'=>array('heading'=>'账目编号'),
			'type'=>array('heading'=>'类型'),
			'subject'=>array('heading'=>'科目'),
			'amount'=>array('heading'=>'金额','parser'=>array('function'=>function($amount,$received){
				if($amount>0){
					return '<span style="color:#0A0">'.$amount.'</span> '.(intval($received)?'√':'？');
				}else{
					return '<span style="color:#A00">'.$amount.'</span> '.(intval($received)?'√':'？');
				}
			},'args'=>array('amount','received')),'cell'=>array('style'=>'text-align:right')),
			'date'=>array('heading'=>'日期'),
			'payer_name'=>array('heading'=>'付款/收款人'),
			'comment'=>array('heading'=>'备注','cell'=>array('title'=>'{comment}'),'parser'=>array('function'=>function($comment){return str_getSummary($comment);},'args'=>array('comment')))
		);
		
		$this->client_list_args=array(
			'name'=>array('heading'=>'名称','cell'=>array('data'=>'{name}')),
			'phone'=>array('heading'=>'电话','cell'=>array('class'=>'ellipsis','title'=>'{phone}')),
			'email'=>array('heading'=>'电邮','cell'=>array('data'=>'<a href = "mailto:{email}">{email}</a>','class'=>'ellipsis')),
			'role'=>array('heading'=>'本案地位')
		);
		
		$this->staff_list_args=array(
			'staff_name'=>array('heading'=>array('data'=>'名称','width'=>'38%'),'cell'=>'{name}'),
			'role'=>array()
		);
	
		$this->load->view_path['edit']='cases/edit';
		$this->load->view_path['edit_aside']='cases/edit_sidebar';
		$this->load->view_path['list_aside']='cases/list_sidebar';
		
	}
	
	function add(){
		$this->cases->id=$this->cases->getAddingItem();
		
		if($this->cases->id===false){
			$this->cases->id=$this->cases->add(array('time_contract'=>$this->date->today,'end'=>date('Y-m-d',$this->date->now+100*86400)));
		}
		
		$this->edit($this->cases->id);
	}
	
	function edit($id){
		$this->cases->id=$id;
		
		try{
			$this->cases->data=array_merge($this->cases->fetch($this->cases->id),$this->input->sessionPost('project'));
			$this->cases->profiles=array_merge(array_sub($this->cases->getProfiles($this->cases->id),'content','name'),$this->input->sessionPost('profiles'));
			$this->cases->labels=array_merge($this->cases->getLabels($this->cases->id),$this->input->sessionPost('labels'));
			
			if(!$this->cases->data['name']){
				$this->output->title='未命名'.lang(CONTROLLER);
			}else{
				$this->output->title=$this->cases->data['name'];
			}
			
			$people_roles=$this->cases->getPeopleRoles($this->cases->id);
			
			$this->load->addViewData('people_roles', $people_roles);
			
			$this->load->addViewData('project', $this->cases->data);
			$this->load->addViewData('labels', $this->cases->labels);
			$this->load->addViewData('profiles', $this->cases->profiles);

			//计算本案有效日志总时间
			$this->load->model('schedule_model','schedule');
			
			$this->load->view_data['schedule_time']=$this->schedule->getSum(array('project'=>$this->cases->id,'completed'=>true));

			if($this->cases->data['type']==='query'){
				$this->load->view_data['staff_role_array']=array('接洽律师','律师助理');
			}else{
				$this->load->view_data['staff_role_array']=array('案源人','接洽律师','主办律师','协办律师','律师助理');
			}
			
			$this->load->addViewData('client_list', $this->clientList());

			$this->load->addViewData('staff_list', $this->staffList());
			
			$this->load->addViewData('fee_list', $this->accountList());
			
			$this->load->addViewData('schedule_list', $this->scheduleList());

			$this->load->addViewData('plan_list', $this->planList());
			
			$this->load->addViewData('document_list', $this->documentList());

			$this->load->addViewData('relative_list', $this->relativeList());

			$this->load->view('cases/edit');
			
			$this->load->view('cases/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}
	}
	
	function clientList(){
		
		$this->load->model('client_model','client');
		
		$list=$this->table->setFields($this->client_list_args)
			->setRowAttributes(array('hash'=>'{type}/{id}'))
			->setAttribute('name','client')
			->generate($this->people->getList(array('in_project'=>$this->cases->id,'is_staff'=>false)));
		
		return $list;
	}
	
	function workList(){
		
		$this->load->model('schedule_model','schedule');
		
		return $this->table->setFields($this->schedule_list_args)
			->setAttribute('name','schedule')
			->setRowAttributes(array('onclick'=>"$.viewSchedule({id:{id}})",'style'=>'cursor:pointer;'))
			->generate($this->schedule->getList(array('show_creater'=>true,'limit'=>10,'project'=>$this->project->id,'completed'=>true,'orderby'=>'id desc')));
	}
	
	function planList(){
		
		$this->load->model('schedule_model','schedule');
		
		return $this->table->setFields($this->plan_list_args)
			->setAttribute('name','schedule')
			->setRowAttributes(array('onclick'=>"$.viewSchedule({id:{id}})",'style'=>'cursor:pointer;'))
			->generate($this->schedule->getList(array('show_creater'=>true,'limit'=>10,'project'=>$this->project->id,'completed'=>false)));
	}
	
	function staffList(){
		
		$this->staff_list_args['role']=array('heading'=>'本案职位','parser'=>array('function'=>array($this->cases,'getCompiledPeopleRoles'),'args'=>array($this->cases->id,'id')));
		$this->load->model('staff_model','staff');
		
		$list=$this->table->setFields($this->staff_list_args)
			->setAttribute('name','staff')
			->setRowAttributes(array('hash'=>'staff/{id}'))
			->generate($this->staff->getList(array('in_project'=>$this->cases->id)));
		
		return $list;
	}
	
	function submit($submit,$id,$button_id=NULL){
		
		parent::submit($submit, $id, $button_id);
		
		try{
		
			if($submit=='project'){
				
				if($this->cases->data['type']==='cases' && !$this->cases->data['num']){
					$this->output->message('尚未获取案号，请选择案件领域和分类后获取案号','warning');
					throw new Exception();
				}
				
				if(isset($this->cases->labels['分类']) && $this->cases->data['type']==='cases' && !$this->cases->data['focus']){
					if($this->cases->labels['分类']==='争议'){
						$this->output->message('请填写案件争议焦点','warning');
						throw new Exception;
					}
					elseif($this->cases->labels['分类']==='非争议'){
						$this->output->message('请填写案件标的','warning');
						throw new Exception;
					}
				}
				
			}
			
			elseif($submit=='client'){
				
				$this->load->model('client_model','client');
				
				//这样对数组做加法，后者同名键不会替换前者，即后者是前者的补充，而非更新
				$project_client=$this->input->sessionPost('case_client');
				$client=$this->input->sessionPost('client');
				$client_profiles=$this->input->sessionPost('client_profiles');
				$client_labels=$this->input->sessionPost('client_labels');
				
				if(!$project_client['role']){
					$this->output->message('请选择本案地位','warning');
					throw new Exception;
				}
		
				if($project_client['client']){
					
					if($project_client['role']==='主委托人'){

						$recent_case_of_client=$this->cases->getRow(array('people'=>$project_client['client'],'role'=>'主委托人','before'=>$this->cases->id,'order_by'=>'id desc'));
						$recent_case_of_client_relative=$this->cases->getRow(array('people_is_relative_of'=>$project_client['client'],'role'=>'主委托人','before'=>$this->cases->id,'order_by'=>'id desc'));
						$recent_case_of_client_arelative=$this->cases->getRow(array('people_has_relative_like'=>$project_client['client'],'role'=>'主委托人','before'=>$this->cases->id,'order_by'=>'id desc'));
						
						if($recent_case_of_client){
							$recent_case=$recent_case_of_client;
						}
						
						if($recent_case_of_client_relative && (!isset($recent_case) || $recent_case_of_client_relative['id']>$recent_case['id'])){
							$recent_case=$recent_case_of_client_relative;
						}
						
						if($recent_case_of_client_arelative && (!isset($recent_case) || $recent_case_of_client_arelative['id']>$recent_case['id'])){
							$recent_case=$recent_case_of_client_arelative;
						}
						
						if(isset($recent_case)){

							$this->cases->addLabel($this->cases->id, '再成案');

							$this->cases->addRelative($this->cases->id, $recent_case['id'], '上次签约案件');
							$previous_roles=$this->cases->getRolesPeople($recent_case['id']);
							$recent_case_profiles=array_sub($this->cases->getProfiles($recent_case['id']),'content','name');
							
							foreach(array('案源人') as $role){
								if(isset($previous_roles[$role])){
									foreach($previous_roles[$role] as $people){
										$this->cases->addStaff($this->cases->id, $people['people'], $role, $people['weight']/2);
									}
								}
							}

							$this->cases->addProfile($this->cases->id, '案源系数', 0.2-(0.2-$recent_case_profiles['案源系数'])/2);
							$this->cases->addProfile($this->cases->id,'案源类型',$recent_case_profiles['案源类型']);
							$this->output->setData($this->relativeList(),'relative-list','content-table','.item[name="relative"]>.contentTable','replace');
							$this->output->setData($this->staffList(),'staff-list','content-table','.item[name="staff"]>.contentTable','replace');
						}else{
							$this->cases->profiles['案源类型']=array_sub($this->client->getProfiles($project_client['client']),'content','name')['来源类型']==='亲友介绍'?'个人案源':'所内案源';
							if($this->cases->profiles['案源类型']==='所内案源'){
								$this->cases->addProfile($this->cases->id, '案源系数', 0.08);
							}else{
								$this->cases->addProfile($this->cases->id, '案源系数', 0.2);
							}
							$this->cases->addProfile($this->cases->id,'案源类型',$this->cases->profiles['案源类型']);
						}
					}
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

					if($client['type']=='client'){//客户必须输入来源
						if(empty($client_profiles['来源类型'])){
							$this->output->message('请选择客户来源类型','warning');
							throw new Exception;
						}
						
						$this->load->model('staff_model','staff');
						
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
					
					$new_client['display']=true;
					
					$project_client['client']=$this->client->add($new_client);

					$this->output->message(
						'<a href="#'.
						$client['type'].
						'/'.$project_client['client'].'">新'.
						lang($client['type']).' '.$client['name'].
						' 已经添加，点击编辑详细信息</a>'
					);
				}

				if($this->cases->addPeople($this->cases->id,$project_client['client'],'client',$project_client['role'])){
					$this->output->setData($this->clientList(),'client-list','content-table','.item[name="client"]>.contentTable','replace');
				}else{
					$this->output->message('客户添加错误', 'warning');
					throw new Exception;
				}
				
				unsetPost('case_client');
				
				$this->session->unset_userdata();
				
				unsetPost('case_client');
				unsetPost('client');
				unsetPost('client_profiles');
				unsetPost('client_labels');
			}

			elseif($submit=='remove_client'){
				if($this->cases->removePeople($this->cases->id,$button_id)){
					$this->output->setData($this->clientList(),'client-list','content-table','.item[name="client"]>.contentTable','replace');
				}
			}
			
			elseif($submit=='staff'){
				
				$this->load->model('staff_model','staff');
				
				$staff=$this->input->sessionPost('staff');
				
				if(!$staff['id']){
					$staff['id']=$this->staff->check($staff['name']);
					
					if(!$staff['id']){
						$this->output->message('请输入职员名称','warning');
						throw new Exception;
					}
				}
				
				if(!$staff['role']){
					$this->output->message('未选择本案职务','warning');
					throw new Exception;
				}
				
				if(in_array($staff['role'],array('案源人','接洽律师','主办律师')) && !$staff['weight']){
					$staff['weight']=100;
				}
				
				if($staff['weight']===''){
					$staff['weight']=NULL;
				}else{
					$staff['weight']/=100;
				}
				
				if($this->cases->addStaff($this->cases->id,$staff['id'],$staff['role'],$staff['weight'])){
					$this->message->send('将你加入<a href="#'.$this->cases->data['type'].'/'.$this->cases->data['id'].'">案件：'.$this->cases->data['name'].'</a>',$staff['id']);
					$this->output->setData($this->staffList(),'staff-list','content-table','.item[name="staff"]>.contentTable','replace');
					unsetPost('staff/id');
				}else{
					$this->output->message('人员添加错误', 'warning');
				}

				unsetPost('staff');
			}
			
			elseif($submit=='remove_staff'){
				if($this->cases->removePeople($this->cases->id,$button_id)){
					$this->output->setData($this->staffList(),'staff-list','content-table','.item[name="staff"]>.contentTable','replace');
				}
			}
			
			elseif($submit=='miscfee'){
				
				$this->load->model('account_model','account');
				
				$misc_fee=$this->input->sessionPost('miscfee');
				
				if(!$misc_fee['receiver']){
					$this->output->message('请选择办案费收款方','warning');
					throw new Exception;
				}
				
				if(!is_numeric($misc_fee['amount'])){
					$this->output->message('请预估收费金额（数值）','warning');
					throw new Exception;
				}
				
				if(!$misc_fee['date']){
					$this->output->message('请预估收费时间','warning');
					throw new Exception;
				}
				
				$misc_fee['type']=$misc_fee['subject']='办案费';
				
				$misc_fee['comment']=$misc_fee['receiver'].'收 '.$misc_fee['comment'];
				
				$this->account->add($misc_fee+array('project'=>$this->project->id,'display'=>true));
				$this->output->setData($this->miscfeeList(),'miscfee-list','content-table','.item[name="miscfee"]>.contentTable','replace');
				
				unsetPost('miscfee');

			}
			
			elseif($submit=='review'){
				$this->cases->removeLabel($this->cases->id, '等待立案审核');
				$this->cases->addLabel($this->cases->id, '在办');
				$this->output->status='refresh';
				$this->output->message('通过立案审核');
			}
			
			elseif($submit=='apply_lock'){
				//@TODO申请锁定，通过标签和消息系统来解决
			}
			
			elseif($submit=='lock_client'){
				$this->cases->addLabel($this->cases->id, '客户已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_staff'){
				$this->cases->addLabel($this->cases->id, '职员已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='lock_fee'){
				$this->cases->addLabel($this->cases->id, '费用已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_client'){
				$this->cases->removeLabel($this->cases->id, '客户已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_staff'){
				$this->cases->removeLabel($this->cases->id, '职员已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='unlock_fee'){
				$this->cases->removeLabel($this->cases->id, '费用已锁定');
				$this->output->status='refresh';
			}
			
			elseif($submit=='apply_file'){
				$this->cases->addLabel($this->cases->id, '已申请归档');
				$this->cases->update($this->cases->id,array(
					'end'=>$this->date->today
				));
				$this->output->message('归档申请已接受');
			}
			
			elseif($submit=='review_finance'){
				$this->cases->addLabel($this->cases->id, '通过财务审核');
				$this->output->status='refresh';
				$this->output->message('结案财务状况已经审核');
			}
			
			elseif($submit=='review_info'){
				$this->cases->addLabel($this->cases->id, '通过信息审核');
				$this->output->status='refresh';
				$this->output->message('案件信息已经审核');
			}
			
			elseif($submit=='review_manager'){
				$this->cases->addLabel($this->cases->id, '通过主管审核');
				$this->cases->update($this->cases->id,array(
					'end'=>$this->date->today,
				));
				$this->output->status='refresh';
				$this->output->message('案件已经审核，已正式归档');
			}
			
			elseif($submit=='file' && $this->cases->data['type']==='cases'){
				$this->cases->removeLabel($this->cases->id, '已申请归档');
				$this->cases->addLabel($this->cases->id, '案卷已归档');
				$this->cases->update($this->cases->id,array('active',false));
				$this->output->status='refresh';
				$this->output->message('案卷归档归档完成');
			}

			elseif($submit=='apply_num'){
				
				if(empty($this->cases->labels['领域'])){
					$this->output->message('获得案号前要先选择案件领域','warning');
					throw new Exception();
				}

				if(empty($this->cases->labels['分类'])){
					$this->output->message('获得案号前要先选择案件分类','warning');
					throw new Exception();
				}

				if(!$this->cases->data['name']){
					$this->output->message('获得案号前要先填写案件名称','warning');
					throw new Exception();
				}

				$this->cases->data['num']=$this->cases->getNum($this->cases->labels);
				
				$this->cases->data['display']=true;
				
				$this->cases->update($this->cases->id,$this->cases->data);
				
				$this->cases->updateLabels($this->cases->id, $this->cases->labels);
				
				$this->output->status='redirect';
				$this->output->data='cases/'.$this->cases->id;
				
			}
			
		}catch(Exception $e){
			$e->getMessage() && $this->output->message($e->getMessage(), 'warning');
			$this->output->status='fail';
		}
	}

	function removePeopleRole($cases_id,$people_id){
		
		$this->cases->id=$cases_id;
		
		$role=$this->input->post('role');
		
		$this->cases->removePeopleRole($cases_id,$people_id,$role);
		$this->output->setData($this->staffList(),'staff-list','content-table','.item[name="staff"]>.contentTable','replace');
	}
	
	function host(){
		$this->output->title='主办案件';
		$this->config->set_user_item('search/role','主办律师',false);
		$this->index();
	}
	
	function index(){
		if($this->user->isLogged('service')){
			$this->config->set_user_item('search/people', NULL, false);
		}
		
		$this->config->set_user_item('search/active', true, false, 'method', false);
		
		parent::index();
	}
	
}
?>
