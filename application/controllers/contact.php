<?php
class Contact extends People{
	
	var $section_name='联系人';
	
	function __construct(){
		parent::__construct();
	}

	function contact(){
		$this->index('contact');
	}

	function index($method=NULL){
		$this->output->setData($this->section_name, 'name');
		parent::index($method);
	}

}
?>