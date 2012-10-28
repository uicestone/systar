<?php
class Test extends SS_controller{
	function __construct() {
		$this->default_method='index';
		parent::__construct();
	}
	
	function index(){
		$query=$this->db->select('a')->from('b')->join('c','1=1')->join('d','1=1')->where('a = 2');
		
		print_r($query);
	}
}

?>
