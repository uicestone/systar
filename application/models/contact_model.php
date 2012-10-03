<?php
class Contact_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM client WHERE id = '".$id."' AND classification IN ('相对方','联系人')";
		return db_fetch_first($query,true);
	}
}
?>