<?php
class Nav extends SS_Controller{
	function __construct() {
		parent::__construct();
		$this->default_method='index';
	}
	
	function index(){
		$this->load->view('nav');
	}
}
?>