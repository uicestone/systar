<?php
class Contact extends People{
	
	var $section_name='联系人';
	
	function __construct(){
		parent::__construct();
	}

	function index(){
		$this->output->setData($this->section_name, 'name');
		option('search/type','联系人');
		parent::index();
	}

}
?>