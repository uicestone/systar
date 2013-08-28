<?php
class Client extends People{
	
	function __construct(){
		parent::__construct();
		$this->load->model('client_model', 'client');
		$this->people=$this->client;
		
		$this->form_validation_rules['people']=array(
			array(
				'field'=>'profiles[来源类型]',
				'label'=>'客户来源类型',
				'rules'=>'required'
			),
			array(
				'field'=>'people[staff]',
				'label'=>'来源律师',
				'rules'=>'required'
			)
		);
		
		$this->load->view_path['edit']='client/edit';
	}

	function potential(){
		$this->config->set_user_item('search/labels', array('潜在客户'));
		
		$this->index();
	}

	function index(){
		$this->config->set_user_item('search/in_same_project_with',$this->user->id,false);
		
		if(!$this->config->user_item('search/labels')){
			$this->config->set_user_item('search/labels', array('成交客户'));
		}
		
		parent::index();
	}
	
}
?>