<?php
class Query_model extends Project_model{
	function __construct(){
		parent::__construct();
	}
	
	function add($data=array()){
		$this->id=parent::add($data);
		$this->addLabel($this->id, '咨询');
		return $this->id;
	}
}
?>