<?php
class Test extends SS_controller{
	function __construct() {
		parent::__construct();
	}
	
	function index(){
		
		global $_G;
		
		$this->load->view('head');
		
		print_r($_G);
		
		print_r($_SESSION);
	}
}

?>
