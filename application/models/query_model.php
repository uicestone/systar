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
	
	function getList($args=array()){
		if(isset($args['name'])){
			$this->db->like('case.name',$args['name']);
		}
		return parent::getList($args);
	}
}
?>