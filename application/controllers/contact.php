<?php
class Contact extends People{
	
	var $section_title='联系人';
	
	function __construct(){
		parent::__construct();
		$this->load->model('contact_model','contact');
		$this->people=$this->contact;
	}

	function index(){
		parent::index();
	}

}
?>