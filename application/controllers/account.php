<?php
class Account extends SS_controller{
	
	var $section_title='资金';
	var $list_args;
	
	function __construct(){
		
		parent::__construct();
		$this->load->model('achievement_model','achievement');
		
		$this->list_args=array(
			'id'=>array('heading'=>'账目编号'),
			'date'=>array('heading'=>'日期'),
			'name'=>array('heading'=>'名目','cell'=>array('class'=>'ellipsis','title'=>'{name}')),
			'type'=>array('heading'=>array('data'=>'方向','width'=>'45px'),'parser'=>array('function'=>function($amount){
				if($amount>0){
					return '<span style="color:#0F0"><<</span>';
				}else{
					return '<span style="color:#F00">>></span>';
				}
			},'args'=>array('{amount}'))),

			'received'=>array('heading'=>array('data'=>'状态','width'=>'50px'),'parser'=>array('function'=>function($received){
				return intval($received)?'实际':'预计';
			},'args'=>array('{received}'))),
			'amount'=>array('heading'=>'金额'),
			'payer_name'=>array('heading'=>'付款/收款人')
		);
	}
	
	function index(){
		
		$this->config->set_user_item('search/show_payer', true , false);
		
		$this->config->set_user_item('search/orderby', 'account.time desc', false);
		
		$this->config->set_user_item('search/received', true, false);
		
		if($this->input->post('name')){
			$this->config->set_user_item('search/name', $this->input->post('name'));
		}
		
		if($this->input->post('labels')){
			$this->config->set_user_item('search/labels', $this->input->post('labels'));
		}
		
		if($this->input->post('name')===''){
			$this->config->unset_user_item('search/name');
		}
		
		if($this->input->post('received')!==false){
			$this->config->set_user_item('search/received', (bool)$this->input->post('received'));
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
		
		$this->load->model('people_model','people');
		$this->load->model('project_model','project');
		
		try{
			$this->account->data=$this->account->fetch($this->account->id);

			if($this->account->data['name']){
				$this->section_title=$this->account->data['name'];
			}else{
				$this->section_title='未命名'.$this->section_title;
			}
			
			$this->load->addViewData('account',$this->account->data);

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
				
				if(!$this->account->data['name']){
					$this->output->message('请填写摘要','warning');
					throw new Exception;
				}

				if(!$this->account->data['date']){
					$this->output->message('请填写日期','warning');
					throw new Exception;
				}
				
				if($this->account->data['way']=='out'){
					$this->account->data['amount']=-abs($this->account->data['amount']);
				}
				//根据way设置amount的正负号

				$this->account->update($this->account->id,$this->account->data);
				
				unset($_SESSION[CONTROLLER]['post'][$this->account->id]);
				
				$this->output->message($this->section_title.' 已保存');
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