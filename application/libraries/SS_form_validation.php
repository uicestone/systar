<?php
class SS_Form_validation extends CI_Form_validation{
	function __construct($rules = array()){
		parent::__construct();
		$controller=CONTROLLER;

		$_POST+=$this->CI->input->post();
		
		if(isset($_SESSION[CONTROLLER]['post'][$this->CI->$controller->id])){
			$_POST=array_merge_recursive($_POST,$_SESSION[CONTROLLER]['post'][$this->CI->$controller->id]);
		}
	}
}

?>
