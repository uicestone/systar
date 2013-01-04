<?php
class Frame extends SS_Controller{
	function __construct(){
		$this->default_method='index';
		$this->require_permission_check=false;
		parent::__construct();
		$this->load->require_inner_js=false;
	}
	
	function _output($output) {
		echo $output;
	}
	
	function index(){
		$this->load->view('head');
		$this->load->view('frame');
		$this->load->view('innerjs');
		$this->load->view('foot');
	}
}
?>