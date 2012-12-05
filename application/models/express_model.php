<?php
class Express_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
            $query="
				SELECT * 
				FROM express 
				WHERE id = '{$id}' AND company='{$this->company->id}'";
            $result=$this->db->query($query)->result_array();
            return $result[0];
	}
	
	function getList(){
		$query="
			SELECT express.id,express.destination,express.content,express.comment,express.time_send,express.num,staff.name AS sender_name
			FROM express LEFT JOIN staff ON staff.id=express.sender
			WHERE express.display=1
		";
		
		$query=$this->search($query,array('num'=>'单号','staff.name'=>'寄送人','destination'=>'寄送地点'));//为当前sql对象添加搜索条件
		$query=$this->orderBy($query,'time_send','DESC');//为当前sql对象添加orderby从句
		$query=$this->pagination($query);//为当前sql对象添加limit从句

		return $this->db->query($query)->result_array();
	}
}
?>