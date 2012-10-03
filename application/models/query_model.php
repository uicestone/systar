<?php
class Query_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function query_fetch($id){
		$query="SELECT * FROM `case` WHERE id='".$id."'";
		return db_fetch_first($query,true);
	}
}
?>