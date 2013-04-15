<?php
class Client extends People{
	
	var $section_title='客户';
	
	function __construct(){
		parent::__construct();
		
		$this->people=$this->client;
		
		$this->form_validation_rules['people']=array(
			array(
				'field'=>'profiles[来源类型]',
				'label'=>'客户来源类型',
				'rules'=>'required'
			),
			array(
				'field'=>'labels[类型]',
				'label'=>'客户类型',
				'rules'=>'required'
			),
		);
	}

	function potential(){
		$this->config->set_user_item('search/labels', array('潜在客户'));
		
		$this->index();
	}

	function index(){
		$this->config->set_user_item('search/type', '客户');
		$this->config->set_user_item('search/in_same_project_with',$this->user->id,false);
		
		if(!$this->config->user_item('search/labels')){
			$this->config->set_user_item('search/labels', array('成交客户'));
		}
		
		parent::index();
	}
	
}
?>