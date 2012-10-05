<?php
class Query_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM `case` WHERE id='".$id."'";
		return db_fetch_first($query,true);
	}
}
?>