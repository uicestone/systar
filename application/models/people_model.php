<?php
class People_model extends SS_Model{
	function __construct() {
		parent::__construct();
	}

	function fetch($id){
		$id=intval($id);
		
		$query="
			SELECT * 
			FROM people
			WHERE id=$id AND company={$this->config->item('company/id')}";
		
		return $this->db->query($query)->row_array();
	}
	
	function add(array $data=array()){
		
		$this->db->insert('people',$data);
	}

}
?>
