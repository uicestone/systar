<?php
class Account_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="
			SELECT * 
			FROM account 
			WHERE id='{$id}' AND company='{$this->company->id}'
";
		return $this->db->query($query)->row_array();
	}
	
	function getList(){
		$query="
			SELECT
				account.id,account.time,account.name,account.amount,account.time_occur,
				client.abbreviation AS client_name
			FROM account LEFT JOIN client ON account.client=client.id
			WHERE amount<>0
		";
		
		if(!$this->user->isLogged('finance')){
			$query.=" AND account.case IN (SELECT `case` FROM case_lawyer WHERE lawyer={$this->user->id} AND role='主办律师')";
		}
		
		$query=$this->search($query,array('client.name'=>'客户','account.name'=>'名目','account.amount'=>'金额'));
		
		$query=$this->dateRange($query,'account.time_occur');
		
		$query=$this->orderby($query,'time_occur','DESC');
		
		$query=$this->pagination($query);
		
		return $this->db->query($query)->result_array();
	}
}
?>