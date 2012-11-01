<?php
class Frame extends SS_Controller{
	function __construct(){
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		$this->load->require_head=FALSE;
		$this->load->view('head_frame');
		$this->load->view('frame');
	}
}
?>