<?php
class Query_model extends Cases_model{
	function __construct(){
		parent::__construct();
		$this->default_type='query';
	}
	
	function getList(array $args=array()){
		!isset($args['type']) && $args['type']='query';
		return parent::getList($args);
	}
}
?>