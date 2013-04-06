<?php
/**
 * 案件，继承于 项目
 */
class Cases extends Project{
	
	var $section_title='案件';

	var $client_list_args;
	
	function __construct() {
		parent::__construct();
		
		$this->project=$this->cases;

		$this->list_args=array(
			'time_contract'=>array(
				'heading'=>array('data'=>'案号','width'=>'140px'),
				'cell'=>array('data'=>'{num}','title'=>'立案时间：{time_contract}')
			),
			'name'=>array('heading'=>'案名','cell'=>'{name}'),
			'responsible'=>array('heading'=>array('data'=>'主办律师','width'=>'110px'),'parser'=>array('function'=>array($this->cases,'getResponsibleStaffNames'),'args'=>array('{id}'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->cases,'getCompiledLabels'),'args'=>array('{id}')))
		);
		
		$this->client_list_args=array(
			'name'=>array('heading'=>'名称','cell'=>array('data'=>'{name}<button type="submit" name="submit[remove_client]" id="{id}" class="hover">删除</button>')),
			'phone'=>array('heading'=>'电话','cell'=>array('class'=>'ellipsis','title'=>'{phone}')),
			'email'=>array('heading'=>'电邮','cell'=>array('data'=>'<a href = "mailto:{email}">{email}</a>','class'=>'ellipsis')),
			'role'=>array('heading'=>'本案地位')
		);
		
		$this->miscfee_list_args=array(
			'receiver'=>array('heading'=>'收款方','cell'=>'{receiver}<button type="submit" name="submit[remove_miscfee]" id="{id}" class="hover">删除</button>'),
			'fee'=>array('heading'=>'数额','eval'=>true,'cell'=>"
				return '{fee}'.('{fee_received}'==''?'':' （到账：{fee_received}）');
			"),
			'comment'=>array('heading'=>'备注'),
			'pay_date'=>array('heading'=>'预计时间')
		);
		
	}
	
	function add(){
		$this->cases->id=$this->cases->add(array('time_contract'=>$this->date->today,'time_end'=>date('Y-m-d',$this->date->now+100*86400)));
		$this->edit($this->cases->id);
		redirect('#'.CONTROLLER.'/edit/'.$this->cases->id);
	}
	
	function edit($id){
		$this->cases->id=$id;
		
		try{
			$this->cases->data=array_merge($this->cases->fetch($id),$this->input->sessionPost('project'));

			$this->cases->labels=array_merge($this->cases->getLabels($this->cases->id),$this->input->sessionPost('labels'));

			if(!$this->cases->data['name']){
				$this->section_title='未命名'.$this->section_title;
			}else{
				$this->section_title=$this->cases->data['name'];
			}

			$project_role=$this->cases->getRoles($this->cases->id);

			$responsible_partner=$this->cases->getPartner($project_role);
			//获得本案督办人的id

			$lawyers=$this->cases->getLawyers($project_role);
			//获得本案办案人员的id

			$my_roles=$this->cases->getMyRoles($project_role);
			//本人的本案职位

			$this->load->addViewArrayData(compact('case_role','responsible_partner','lawyers','my_roles'));
			
			$this->load->addViewData('project', $this->cases->data);
			$this->load->addViewData('labels', $this->cases->labels);

			//计算本案有效日志总时间
			$this->load->model('schedule_model','schedule');
			$this->load->view_data['schedule_time']=$this->schedule->calculateTime($this->cases->id);

			$this->load->view_data['case_type_array']=array('诉前','一审','二审','再审','执行','劳动仲裁','商事仲裁');

			if(in_array('咨询',$this->cases->labels)){
				$this->load->view_data['staff_role_array']=array('督办人','接洽律师','律师助理');
			}else{
				$this->load->view_data['staff_role_array']=array('案源人','督办人','接洽律师','主办律师','协办律师','律师助理');
			}
			
			$this->load->addViewData('client_list', $this->clientList());

			$this->load->addViewData('staff_list', $this->staffList());
			
			$this->load->addViewData('fee_list', $this->accountList());
			
			$this->load->addViewData('miscfee_list', $this->miscfeeList());
			
			$this->load->addViewData('schedule_list', $this->scheduleList());
			
			$this->load->addViewData('plan_list', $this->planList());
			
			$this->load->addViewData('document_list', $this->documentList());

			$this->load->addViewData('relative_list', $this->relativeList());

			$this->load->view('cases/edit');
			
			$this->load->view('project/edit_sidebar',true,'sidebar');
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
			->setRowAttributes(array('hash'=>'client/edit/{id}'))
			->setAttribute('name','client')
			->generate($this->client->getList(array('project'=>$this->project->id,'is_staff'=>false)));
		
		return $list;
	}
	
	 function miscfeeList(){
		
		$this->load->model('account_model','account');
		
		$list=$this->table->setFields($this->miscfee_list_args)
				->setAttribute('name','miscfee')
				->generate($this->account->getList(array('project'=>$this->cases->id,'type'=>'办案费','limit'=>false,'orderby'=>false,'group'=>'account')));
		
		return $list;
	}
	
	function submit($submit,$id,$button_id=NULL){
		
		parent::submit($submit, $id, $button_id);
		
		try{
		
			if($submit=='project'){
				if(!$this->cases->data['num']){
					$this->output->message('尚未获取案号，请选择案件分类和阶段后获取案号','warning');
					throw new Exception();
				}
				if(isset($this->cases->labels['分类']) && in_array($this->cases->labels['分类'],array('诉讼','非诉讼')) && !in_array('咨询', $this->cases->labels) && !$this->cases->data['focus']){
					$this->output->message('请填写案件争议焦点','warning');
					throw new Exception;
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
		
				if($project_client['client']){//autocomplete搜索到已有客户
					$new_client_type=$this->client->fetch($project_client['client'],'type');
					
					if($new_client_type==='客户'){
						$recent_case=$this->cases->getList(array('people'=>$project_client['client'],'labels'=>array('案件'),'limit'=>1,'before'=>$this->cases->id));
						
						if(isset($recent_case[0])){
							
							$this->cases->addLabel($this->cases->id, '再成案');
							
							$this->cases->addRelative($this->cases->id, $recent_case[0]['id'],'上次签约案件');
							$previous_roles=$this->cases->getRolesPeople($recent_case[0]['id']);
							
							foreach(array('案源人','接洽律师') as $role){
								if(isset($previous_roles[$role])){
									foreach($previous_roles[$role] as $people){
										$this->cases->addStaff($this->cases->id, $people['people'], $role, $people['weight']/2);
									}
								}
							}
							
							$this->output->status='refresh';
						}
					}
					
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

			elseif($submit=='remove_client'){
				if($this->cases->removePeople($this->cases->id,$button_id)){
					$this->output->setData($this->clientList(),'content-table','html','.item[name="client"]>.contentTable','replace');
				}
			}

		}catch(Exception $e){
			$e->getMessage() && $this->output->message($e->getMessage(), 'warning');
			$this->output->status='fail';
		}
	}

	function host(){
		$this->section_title='主办案件';
		$this->config->user_item('search/role','主办律师');
		$this->index();
	}
	
	function consultant(){
		
		$this->config->set_user_item('search/labels', array('分类'=>'法律顾问'), false);
		
		$this->index();
	}
	
	function file(){
		
		$this->config->set_user_item('search/labels', array('已申请归档','案卷已归档'), false);
		
		$this->index();
	}
	
	function index(){
		$this->config->set_user_item('search/labels', array('案件'), false);
		
		if($this->user->isLogged('service')){
			$this->config->set_user_item('search/people', NULL);
		}
		
		parent::index();
	}
}
?>
