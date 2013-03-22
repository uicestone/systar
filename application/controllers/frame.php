<?php
class Frame extends SS_Controller{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$this->output->as_ajax=false;
		
		$this->load->view('head');
		$this->load->view('nav');
		$this->load->view('menu');
		$this->load->view('frame');
		$this->load->view('foot');
	}
}
?>