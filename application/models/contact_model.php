<?php
class Contact_model extends People_model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="
			SELECT * 
			FROM client 
			WHERE id = '{$id}'  AND company='{$this->company->id}' AND classification IN ('相对方','联系人')";
		return $this->db->query($query)->row_array();
	}
	
}
?>