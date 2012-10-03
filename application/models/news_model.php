<?php
class News_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM news WHERE id = '".$id."'";
		return db_fetch_first($query,true);
	}
}
?>