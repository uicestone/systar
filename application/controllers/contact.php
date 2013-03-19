<?php
require_once APPPATH.'/controllers/people.php';
class Contact extends People{
	
	var $section_name='联系人';
	
	function __construct(){
		parent::__construct();
		$this->people=$this->contact;
	}

	function index(){
		$this->output->setData($this->section_name, 'name');
		option('search/type','联系人');
		parent::index();
	}

}
?>