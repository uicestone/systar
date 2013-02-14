<?php
class Query extends SS_controller{
	function __construct(){
		$this->default_method='lists';
		parent::__construct();
	}
	
	function filed(){
		$this->lists('filed');
	}
	
	function lists($para=NULL){

		$field=array(
			'first_contact'=>array('title'=>'日期','td_title'=>'width="95px"','td'=>'href="cases/edit/{id}"'),
			'num'=>array('title'=>'编号','td_title'=>'width="143px"'),
			'client_name'=>array('title'=>'咨询人','wrap'=>array('mark'=>'a','href'=>'#client/edit/{client}')),
			'type'=>array('title'=>'方式','td_title'=>'width="80px"'),
			'source'=>array('title'=>'来源'),
			'staff_names'=>array('title'=>'接洽人'),
			'summary'=>array('title'=>'概况','td'=>'class="ellipsis" title="{summary}"'),
			'comment'=>array('title'=>'备注','td'=>'class="ellipsis" title="{comment}"')
		);
		$table=$this->table->setFields($field)
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
		
		$query=$this->query->fetch($id);
		
		$labels=$this->query->getLabels($this->query->id);
		
		if(!$query['name']){
			$query['name']='未命名咨询';
		}
		
		$this->load->addViewData('cases', $query);
		
		$this->output->setData(strip_tags($query['name']), 'name');

		$this->load->view('query/edit');
	}
	
	function submit($submit,$id){
		
		$this->query->id=$id;

		$this->load->model('client_model','client');
		$this->load->model('staff_model','staff');
		
		$query=array_merge($this->query->fetch($id),(array)post('cases'))+(array)$this->input->post('cases');
		
		try{
			
			if($submit=='cancel'){
				unset($_SESSION[CONTROLLER]['post'][$this->query->id]);
				$this->query->clearUserTrash();
			}
		
			elseif($submit=='query'){
				
				$labels=(array)post('labels')+$this->input->post('labels');
				
				if(!isset($labels['咨询方式'])){
					$this->output->message('请选择咨询方式','warning');
					throw new Exception;
				}
				
				if(!isset($labels['领域'])){
					$this->output->message('请选择业务领域','warning');
					throw new Exception;
				}
				
				if(!$query['summary']){
					$this->output->message('请填写咨询概况','warning');
					throw new Exception;
				}
				
				$related_staff_name=(array)post('related_staff_name')+$this->input->post('related_staff_name');
				
				$related_staff=array();
				foreach($related_staff_name as $role => $staff_name){
					$related_staff[$role]=$this->staff->check($staff_name);
				}
				
				$query['is_query']=true;

				$client=(array)post('client')+$this->input->post('client');
				
				if(!$client['id']){
					if(!$client['name']){
						$this->output->message('请填写咨询人', 'warning');
						throw new Exception;
					}
					
					$client_profiles=(array)post('client_profiles')+$this->input->post('client_profiles');
					
					if(!$client_profiles['电话'] && !$client_profiles['电子邮件']){
						$this->output->message('至少输入一种联系方式','warning');
						throw new Exception;
					}
					
					if(!isset($client['staff'])){
						$client['staff']=$this->staff->check($client['staff_name']);
					}
					
					$client_source=(array)post('client_source')+$this->input->post('client_source');
					
					$client['source']=$this->client->setSource($client_source['type'], @$client_source['detail']);

					$client['id']=$this->client->add(
						$client
						+array('profiles'=>$client_profiles)
						+array('labels'=>array('类型'=>'潜在客户'))
					);
				}

				$query['num']=$this->query->getNum($this->query->id,NULL,$labels['领域'],true,$query['first_contact']);

				$client_role=array('client_name'=>$client['name']);
				$query['name']=$this->query->getName($client_role,true);

				$query['display']=true;
				
				$this->query->update($this->query->id,$query);
				
				$this->query->updateLabels($this->query->id, $labels);

				$this->query->addPeople($this->query->id,$client['id'],'客户');

				$this->query->addStaff($this->query->id,$related_staff['督办人'],'督办人');
				$this->query->addStaff($this->query->id,$related_staff['接洽律师'],'接洽律师');
				$this->query->addStaff($this->query->id,$related_staff['律师助理'],'律师助理');
				$this->query->calcContribute($this->query->id);
				
			}

			if($submit=='advanced'){
				$this->output->status='redirect';
				$this->output->data='cases/edit/'.$this->query->id;
			}
			
			$this->output->status='success';
			
		}catch(exception $e){
			$this->output->status='fail';
		}
	}
}
?>