<?php
class Staff extends People{
	
	function __construct(){
		parent::__construct();
		$this->load->model('staff_model','staff');
		$this->people=$this->staff;
	}
}
?>