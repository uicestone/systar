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
}
?>