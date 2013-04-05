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
		$this->config->set_user_item('search/labels', array('潜在客户'), false);
		
		$this->index();
	}

	function index(){
		$this->config->set_user_item('search/type', '客户', false);
		$this->config->set_user_item('search/in_my_case',true);
		
		$this->config->set_user_item('search/labels', array('成交客户'), false);
		
		parent::index();
	}
	
}
?>