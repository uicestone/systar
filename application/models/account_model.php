<?php
class Account_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM account WHERE id='".$id."'";
		return db_fetch_first($query,true);
	}
}
?>