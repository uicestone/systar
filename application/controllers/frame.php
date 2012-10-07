<?php
class Frame extends SS_Controller{
	function __construct(){
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		$this->load->view('frame');
	}
}
?>