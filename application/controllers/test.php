<?php
class Test extends SS_controller{
	function __construct() {
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		$this->load->library('session');
		//print_r($this->session->all_userdata());
		//print_r($_SESSION);
		//$this->load->view('test');
		$this->db->where('id IN (1,2,4)');
		print_r($this->db);
	}
}

?>
