<?php
class Nav extends SS_Controller{
	function __construct(){
		$this->default_method='index';
		$this->require_permission_check=false;
		parent::__construct();
		$this->load->require_inner_js=false;
		$this->output->selector='nav';
	}
	
	function index(){
		$this->load->view('nav');
	}
}
?>