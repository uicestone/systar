<?php
class Nav extends SS_Controller{
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		$this->load->view('nav');
	}
}
?>