<?php
class Frame extends SS_Controller{
	function __construct(){
		$this->default_method='index';
		$this->require_permission_check=false;
		parent::__construct();
	}
	
	function index(){
		$this->load->view('head');
		$this->load->view('nav');
		$this->load->view('frame');
		$this->load->main_view_loaded=true;
		$this->load->view('foot');
	}
}
?>