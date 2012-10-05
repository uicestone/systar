<?php
class Express_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM express WHERE id='".$id."'";
		return db_fetch_first($query,true);
	}
}
?>