<?php
class Client extends People{
	
	var $section_name='客户';
	
	function __construct(){
		parent::__construct();
		$this->form_validation_rules['people'][]=array(
			'field'=>'profiles[来源类型]',
			'label'=>'客户来源类型',
			'rules'=>'required'
		);
	}

	function potential(){
		$this->index('potential');
	}

	function index($method=NULL){
		$this->output->setData($this->section_name, 'name');
		parent::index($method);
	}

}
?>