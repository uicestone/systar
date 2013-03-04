<?php
class Express_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function getList(){
		$query="
			SELECT express.id,express.destination,express.content,express.comment,express.time_send,express.num,
				people.name AS sender_name
			FROM express LEFT JOIN people ON people.id=express.sender
			WHERE express.company={$this->company->id} AND express.display=1
		";
		
		$query=$this->search($query,array('num'=>'单号','people.name'=>'寄送人','destination'=>'寄送地点'));//为当前sql对象添加搜索条件
		$query=$this->orderBy($query,'time_send','DESC');//为当前sql对象添加orderby从句
		$query=$this->pagination($query);//为当前sql对象添加limit从句

		return $this->db->query($query)->result_array();
	}
}
?>