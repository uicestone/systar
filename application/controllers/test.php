<?php
class Test extends SS_controller{
	function __construct() {
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		
		print_r($_SESSION);
		
	}
}

?>
