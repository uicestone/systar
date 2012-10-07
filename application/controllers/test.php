<?php
class Test extends SS_controller{
	function __construct() {
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		
		$this->require_export=false;
		
		//$this->load->view('head');
		
		print_r($_SESSION);
		
	}
}

?>
