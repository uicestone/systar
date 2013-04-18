<?php
class Query_model extends Project_model{
	function __construct(){
		parent::__construct();
	}
	
	function add($data=array()){
		$data['type']='业务';
		$this->id=parent::add($data);
		$this->addLabel($this->id, '咨询');
		$this->addLabel($this->id, '所内案源');
		return $this->id;
	}
	
	function getList($args = array()) {
		
		if(isset($args['date/from']) && isset($args['date/from'])){
			$this->db->where("TO_DAYS(project.first_contact) >= TO_DAYS('{$args['date/from']}')",NULL,FALSE);
		}
		
		if(isset($args['date/to']) && isset($args['date/to'])){
			$this->db->where("TO_DAYS(project.first_contact) <= TO_DAYS('{$args['date/to']}')",NULL,FALSE);
		}
		
		return parent::getList($args);
	}
}
?>