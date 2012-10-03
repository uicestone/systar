<?php
class Express_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function express_fetch($id){
		$query="SELECT * FROM express WHERE id='".$id."'";
		return db_fetch_first($query,true);
	}
}
?>