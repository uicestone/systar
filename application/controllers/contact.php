<?php
class Contact extends People{
	
	var $section_title='联系人';
	
	function __construct(){
		parent::__construct();
		$this->people=$this->contact;
	}

	function index(){
		$this->config->set_user_item('search/labels', array('联系人'), false);
		parent::index();
	}

}
?>