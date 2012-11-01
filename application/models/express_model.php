<?php
class Express_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		return $this->db->get_where('express',array('id'=>$id))->result();
	}
	
	function getList(){
		$this->db
			->select('express.id,express.destination,express.content,express.comment,express.time_send,express.num,staff.name AS sender_name')
			->from('express')
			->join('staff','staff.id=express.sender','left')
			->where('express.display',1);
		
		$this->session->set_userdata('last_list_action',$_SERVER['REQUEST_URI']);
		
		return $this->fetchTable();
	}
}
?>