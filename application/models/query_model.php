<?php
class Query_model extends Cases_model{
	function __construct(){
		parent::__construct();
		parent::$fields['type']='query';
	}
	
	function getList(array $args=array()){
		!isset($args['type']) && $args['type']='query';
		return parent::getList($args);
	}
}
?>