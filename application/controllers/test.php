<?php
class Test extends SS_controller{
	function __construct() {
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		//$this->load->view('test');
		//$this->session->sess_destroy();
		//session_destroy();
		print_r($this->session->all_userdata());
		print_r($_SESSION);
	}
}

?>
