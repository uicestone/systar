<?php
class Query extends Cases{
	
	function __construct(){
		parent::__construct();
		array_unshift($this->controllers, __CLASS__);
		$this->load->model('query_model','query');
		$this->project=$this->query;
		$this->cases=$this->query;
		$this->list_args=array(
			'first_contact'=>array('heading'=>'日期'),
			'name'=>array('heading'=>'名称','cell'=>'{name}'),
			'people'=>array('heading'=>'人员','cell'=>array('class'=>'ellipsis'),'parser'=>array('function'=>array($this->query,'getCompiledPeople'),'args'=>array('id'))),
			'labels'=>array('heading'=>'标签','parser'=>array('function'=>array($this->query,'getCompiledLabels'),'args'=>array('id')))
		);
		
	}
	
	function index(){
		$this->config->set_user_item('search/type', 'query', false);
		parent::index();
	}
	
	function filed(){
		$this->config->set_user_item('search/labels', array('已归档'), false);
		$this->index();
	}
	
	function add(){
		$this->query->id=$this->query->getAddingItem();
		
		if($this->query->id===false){
			$this->query->id=$this->query->add(array('type'=>'业务','first_contact'=>$this->date->today));
		}
		
		$this->edit($this->query->id);
	}
	
	function submit($submit,$id,$button_id=NULL){
		
		parent::submit($submit,$id,$button_id);
		
		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		
		try{
			
			if($submit=='query'){
				
				$client=$this->input->sessionPost('client');
				
				$this->query->labels[]='咨询';
				
				$this->load->library('form_validation');
				
				if(!$client['id']){
					if(!$client['name']){
						$this->output->message('请填写咨询人', 'warning');
						throw new Exception;
					}
					
					$client_profiles=$this->input->sessionPost('client_profiles');
					
					if(!$client['gender']){
						$this->output->message('请选择性别','warning');
						throw new Exception;
					}
					
					if(!$client_profiles['电话'] && !$client_profiles['电子邮件']){
						$this->output->message('至少输入一种联系方式','warning');
						throw new Exception;
					}
					
					foreach($client_profiles as $name => $content){
						if($name=='电话'){
							if($this->client->isMobileNumber($content)){
								$client_profiles+=array('手机'=>$content);
								unset($client_profiles['电话']);
							}
						}elseif($name=='电子邮件' && $content){
							if(!$this->form_validation->valid_email($content)){
								$this->output->message('请填写正确的Email地址', 'warning');
								throw new Exception;
							}
						}
					}

					if(!isset($client['staff'])){
						$client['staff']=$this->staff->check($client['staff_name']);
					}
					
					$client['id']=$this->client->add(
						$client
						+array(
							'profiles'=>$client_profiles,
							'labels'=>array('类型'=>'潜在客户'),
							'display'=>true
						)
					);
					
					$this->query->addPeople($this->query->id,$client['id'],'client');
				}

				if(empty($this->query->labels['咨询方式'])){
					$this->output->message('请选择咨询方式','warning');
					throw new Exception;
				}
				
				if(empty($this->query->labels['领域'])){
					$this->output->message('请选择业务领域','warning');
					throw new Exception;
				}
				
				$related_staff_name=$this->input->sessionPost('related_staff_name');
				
				if(!$related_staff_name['接洽律师']){
					$this->output->message('请填写接洽律师（跟进人员中间一项）');
					throw new Exception;
				}
				
				$related_staff=array();
				
				foreach($related_staff_name as $role => $staff_name){
					if($staff_name){
						$related_staff[$role]=$this->staff->check($staff_name);
					}
				}
				
				if(!$this->query->data['summary']){
					$this->output->message('请填写咨询概况','warning');
					throw new Exception;
				}
				
				if(!$this->query->data['display']){
					$this->query->data['display']=true;
					$this->output->status='redirect';
					$this->output->data='query/'.$this->query->id;
				}
				
				$this->query->data['name']=$client['name'].' 咨询';
				
				$this->query->update($this->query->id,$this->query->data);
				
				$roles_people=$this->query->getRolesPeople($this->query->id);
				$roles=array();
				foreach($roles_people as $role => $people_role){
					$roles[$role]=$people_role[0]['people'];
				}
				
				post('staffs',$roles);
				
				foreach($related_staff as $role=>$staff){
					if(!in_array($staff,post('staffs'))){
						$this->query->addPeople($this->query->id,$staff,'律师',$role);
						post('staffs',post('staffs')+array($staff));
					}
				}
				
				$this->output->message($this->output->title.'已保存');
			}
			
			elseif($submit=='new_case'){
				$this->query->removeLabel($this->query->id, '已归档');
				$this->query->addLabel($this->query->id, '等待立案审核');
				$this->query->update($this->query->id,array(
					'type'=>'cases',
					'num'=>NULL,
					'time_contract'=>$this->date->today,
					'end'=>date('Y-m-d',$this->date->now+100*86400)
				));
				
				$this->output->message('已立案，请立即获得案号');
				
				$this->output->status='redirect';
				$this->output->data='cases/'.$this->query->id;
			}
			
			elseif($submit=='file'){
				$this->query->addLabel($this->query->id, '已归档');
				$this->query->update($this->query->id,array('active'=>false));
				$this->output->message('咨询案件已归档');
			}
			
			if(is_null($this->output->status)){
				$this->output->status='success';
			}
			
		}catch(exception $e){
			$this->output->status='fail';
		}
	}
}
?>