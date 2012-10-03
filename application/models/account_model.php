<?php
class Account_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function account_fetch($id){
		$query="SELECT * FROM account WHERE id='".$id."'";
		return db_fetch_first($query,true);
	}
}
?>