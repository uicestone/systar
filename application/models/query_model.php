<?php
class Query_model extends Cases_model{
	function __construct(){
		parent::__construct();
		$this->default_type='query';
		$this->default_labels=array('咨询');
	}
}
?>