<?php
class Client extends People{
	
	var $section_name='客户';
	
	function __construct(){
		parent::__construct();
		
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