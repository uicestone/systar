<?php
class Test extends SS_controller{
	function __construct() {
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		$this->load->library('session');
		//$this->session->sess_destroy();
		print_r($this->session->all_userdata());
		print_r($_SESSION);
		session_destroy();
	}
}

?>
