<?php
class Nav extends SS_Controller{
	function __construct(){
		$this->default_method='index';
		$this->require_permission_check=false;
		parent::__construct();
		$this->output->loadinto='nav';
	}
	
	function index(){
		$this->load->view('nav');
	}
}
?>