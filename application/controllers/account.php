<?php
class Account extends SS_controller{
	
	var $section_title='资金';
	
	var $list_args=array(
		'date'=>array('heading'=>'日期'),
		'name'=>array('heading'=>'名目'),
		'type'=>array('heading'=>array('data'=>'方向','width'=>'45px'),'eval'=>true,'cell'=>array(
			'data'=>"
				if({amount}>0){
					return '<span style=\"color:#0F0\"><<</span>';
				}else{
					return '<span style=\"color:#F00\">>></span>';
				}
			",
			'style'=>'text-align:center'
		)),
		'amount'=>array('heading'=>'金额'),
		'client_name'=>array('heading'=>'付款/收款人')
	);
	
	function __construct(){
		parent::__construct();
		$this->load->model('achievement_model','achievement');
	}
	
	function index(){
		
		if($this->input->post('name')){
			$this->config->set_user_item('search/name', $this->input->post('name'));
		}
		
		if($this->input->post('labels')){
			$this->config->set_user_item('search/labels', $this->input->post('labels'));
		}
		
		if($this->input->post('name')===''){
			$this->config->unset_user_item('search/name');
		}
		
		if($this->input->post('submit')==='search' && $this->input->post('labels')===false){
			$this->config->unset_user_item('search/labels');
		}
		
		if($this->input->post('submit')==='search_cancel'){
			$this->config->unset_user_item('search/name');
			$this->config->unset_user_item('search/labels');
		}
		
		$list=$this->table->setFields($this->list_args)
				->setRowAttributes(array('hash'=>'account/edit/{id}'))
				->setData($this->account->getList($this->config->user_item('search')))
				->generate();
		
		$this->load->addViewData('list', $list);
		
		$this->load->view_data['account_sum']=array(
			'_heading'=>array('总创收'),
			array($this->achievement->sum('collected'))
		);
		
		$this->load->view('list');
		$this->load->view('account/list_sidebar',true,'sidebar');
	}

	function add(){
		$data=array('name'=>'律师费');
		
		if($this->input->get('project')){
			$data['project']=intval($this->input->get('project'));
		}
		if($this->input->get('client')){
			$data['people']=intval($this->input->get('client'));
		}

		$this->account->id=$this->account->add($data);
		$this->edit($this->account->id);
		redirect('#'.CONTROLLER.'/edit/'.$this->account->id);
	}

	function edit($id){
		$this->account->id=$id;
		
		$this->load->model('client_model','client');
		$this->load->model('cases_model','cases');
		
		try{
			$this->account->data=$this->account->fetch($this->account->id);

			if($this->account->data['name']){
				$tab_title=$this->account->data['name'];
			}

			if($this->account->data['people']){
				$client=$this->client->fetch($this->account->data['people']);

				if(isset($client['abbreviation'])){
					$tab_title=$client['abbreviation'].' '.$tab_title;
				}else{
					$tab_title=$client['name'].' '.$tab_title;
				}

				//根据客户ID获得收费array
				$case_fee_array=$this->cases->getFeeListByClient($this->account->data['people']);
			}else{
				$tab_title='未命名流水';
			}

			$this->section_title=$tab_title;

			if($this->account->data['project']){
				//根据案件ID获得收费array
				$case_fee_array=$this->cases->getFeeOptions($this->account->data['project']);
				
				$this->load->model('people_model','people');
				
				$case_client_array=$this->people->getArray(array('limit'=>false,'project'=>$this->account->data['project'],'is_staff'=>false));
			}

			$this->load->addViewArrayData(compact('account','client','case_fee_array','case_client_array'));
			$this->load->view('account/edit');
			$this->load->view('account/edit_sidebar',true,'sidebar');
		}
		catch(Exception $e){
			$this->output->status='fail';
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
		}

	}
	
	function submit($submit,$id){
		$this->account->id=$id;

		$this->account->data=array_merge($this->account->fetch($id),$this->input->sessionPost('account'));
		
		try{
			
			if($submit=='cancel'){
				unset($_SESSION[CONTROLLER]['post'][$this->account->id]);
				$this->output->status='close';
			}
			
			if($submit=='account'){
				$this->load->model('cases_model','cases');
				
				if(!$this->account->data['name']){
					$this->output->message('请填写摘要','warning');
					throw new Exception;
				}

				if(!$this->account->data['date']){
					$this->output->message('请填写日期','warning');
					throw new Exception;
				}
				
				if(!$this->account->data['people']){
					$this->output->message('请输入关联客户','warning');
					throw new Exception;
				}

				if($this->account->data['way']=='out'){
					post('account/amount',-abs($this->account->data['amount']));
				}
				//根据way设置amount的正负号

				post('account/project',$this->cases->getIdByCaseFee($this->account->data['project_account']));
				//根据提交的case_fee先找出case.id

				post('account',array_trim(post('account')));//imperfect 2012/5/25 uicestone 为了让没有case_fee 和case的account能够保存

				$this->account->update($this->account->id,post('account'));
				
				unset($_SESSION[CONTROLLER]['post'][$this->account->id]);
				$this->output->status='close';
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