<?php
class Test extends SS_controller{
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		
		$this->config->set_item('require_export',false);
		
		//$this->load->view('head');
		
		print_r($_SESSION);
		
	}
}

?>
