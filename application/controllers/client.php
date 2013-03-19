<?php
require_once APPPATH.'/controllers/people.php';
class Client extends People{
	
	var $section_name='客户';
	
	function __construct(){
		parent::__construct();
		
		$this->people=$this->client;
		
		$this->form_validation_rules['people'][]=array(
			'field'=>'profiles[来源类型]',
			'label'=>'客户来源类型',
			'rules'=>'required'
		);
	}

	function potential(){
		if(is_null(option('search/labels'))){
			option('search/labels',array('潜在客户'));
		}
		
		$this->index();
	}

	function index(){
		if(is_null(option('search/labels'))){
			option('search/labels',array('成交客户'));
		}
		
		option('search/type','客户');
		option('search/in_my_case',true);
		
		parent::index();
	}

}
?>