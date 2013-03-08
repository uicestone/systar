<?php
class Query extends Cases{
	function __construct(){
		$this->default_method='lists';
		parent::__construct();
	}
	
	function filed(){
		$this->lists('filed');
	}
	
	function lists($para=NULL){

		$field=array(
			'first_contact'=>array('heading'=>array('data'=>'日期','width'=>'95px')),
			'num'=>array('heading'=>array('data'=>'编号','width'=>'143px')),
			'client_name'=>array('heading'=>'咨询人','cell'=>'<a href="#client/edit/{client}">{client_name}</a>'),
			'type'=>array('heading'=>array('data'=>'方式','width'=>'80px')),
			'source'=>array('heading'=>'来源'),
			'staff_names'=>array('heading'=>'接洽人'),
			'summary'=>array('heading'=>'概况','cell'=>array('class'=>'ellipsis','title'=>'{summary}')),
			'comment'=>array('heading'=>'备注','cell'=>array('class'=>'ellipsis','title'=>'{summary}'))
		);
		$table=$this->table->setFields($field)
			->setRowAttributes(array('hash'=>'cases/edit/{id}'))
			->setData($this->query->getList($para))
			->generate();

		$this->load->addViewData('list',$table);
		$this->load->view('list');
	}

	function add(){
		$this->query->id=$this->query->add(array('is_query'=>true));
		$this->output->status='redirect';
		$this->output->data='query/edit/'.$this->query->id;
	}

	function edit($id){
		
		$this->query->id=$id;
		
		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		
		try{
			$query=$this->query->fetch($id);

			$labels=$this->query->getLabels($this->query->id);

			if(!$query['name']){
				$this->output->setData('未命名咨询','name');
			}else{
				$this->output->setData(strip_tags($query['name']), 'name');
			}

			$this->load->addViewData('cases', $query);

			$this->load->view('query/edit');
			$this->load->view('query/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}

	}
	
	function submit($submit,$id){
		
		$this->query->id=$id;

		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		
		$query=array_merge($this->query->fetch($id),$this->input->sessionPost('cases'));
		
		try{
			
			if($submit=='cancel'){
				unset($_SESSION[CONTROLLER]['post'][$this->query->id]);
				$this->output->status='close';
			}
		
			elseif($submit=='query'){
				
				$client=$this->input->sessionPost('client');
				$labels=$this->input->sessionPost('labels');
				
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
							'labels'=>array('类型'=>'潜在客户')
						)
					);
					
					$this->query->addPeople($this->query->id,$client['id'],'客户');
					
					post('client/id',$client['id']);
				}

				if(!$labels['咨询方式']){
					$this->output->message('请选择咨询方式','warning');
					throw new Exception;
				}
				
				if(!$labels['领域']){
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
				
				if(!$query['summary']){
					$this->output->message('请填写咨询概况','warning');
					throw new Exception;
				}
				
				$query['is_query']=true;

				post('cases/num',$this->query->getNum($this->query->id,NULL,$labels['领域'],true,$query['first_contact']));

				post('cases/display',true);
				
				post('cases/name',$client['name'].' 咨询');
				
				$this->query->update($this->query->id,post('cases'));
				
				$this->query->updateLabels($this->query->id, post('labels'));

				post('staffs',array());
				foreach($related_staff as $role=>$staff){
					if(!in_array($staff,post('staffs'))){
						$this->query->addStaff($this->query->id,$staff,$role);
						post('staffs',post('staffs')+array($staff));
					}
				}
				
				$this->output->status='close';
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