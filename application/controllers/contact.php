<?php
class Contact extends People{
	
	var $section_title='联系人';
	
	function __construct(){
		parent::__construct();
		$this->people=$this->contact;
	}

	function index(){
		option('search/type','联系人');
		parent::index();
	}

}
?>