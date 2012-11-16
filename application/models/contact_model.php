<?php
class Contact_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM client WHERE id = '".$id."' AND classification IN ('相对方','联系人')";
		return $this->db->query($query)->row_array();
	}
	
	function getList(){
		$q="SELECT client.id,client.name,client.abbreviation,client.work_for,client.position,client.comment,
				phone.content AS phone,address.content AS address
			FROM `client` LEFT JOIN (
				SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type IN('手机','固定电话') GROUP BY client
			)phone ON client.id=phone.client
			LEFT JOIN (
				SELECT client,GROUP_CONCAT(content) AS content FROM client_contact WHERE type='地址' GROUP BY client
			)address ON client.id=address.client
		
		 WHERE display=1";
		
		if($this->input->get('opposite')){
			$q.=" AND classification='相对方'";
		
		}else{
			$q.=" AND classification='联系人'";
		}
		$q=$this->search($q,array('name'=>'姓名','type'=>'类型','work_for'=>'单位','address'=>'地址'));
		$q=$this->orderBy($q,'time','DESC',array('abbreviation','address','comment'));
		$q=$this->pagination($q);
		return $this->db->query($q)->result_array();
	}
}
?>