<?php
class SS_Form_validation extends CI_Form_validation{
	function __construct($rules = array()) {
		parent::__construct();
		$controller=CONTROLLER;
		isset($_SESSION[CONTROLLER]['post']) && $_POST=$_SESSION[CONTROLLER]['post'][$this->CI->$controller->id];
	}
}

?>
