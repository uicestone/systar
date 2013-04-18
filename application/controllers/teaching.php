<?php
class Teaching extends SS_Controller{
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		
	}
	
	function openClass(){
		
	}
	
	function document(){
		$this->config->set_user_item('search/labels', array('教学'), false);
	}
	
	function exam(){
		
	}
}
?>
