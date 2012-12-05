<?php
class Nav extends SS_Controller{
	function __construct() {
		$this->default_method='index';
		$this->require_permission_check=false;
		parent::__construct();
	}
	
	function index(){
		$this->load->require_head=FALSE;
		$this->load->view('head_nav');
		$this->load->view('nav');
	}
}
?>