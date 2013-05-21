<?php
class Staff extends People{
	
	var $section_title='职员';
	
	function __construct(){
		parent::__construct();
		$this->people=$this->staff;
	}
}
?>