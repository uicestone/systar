<?php
class Account_model extends SS_Model{
	
	var $id;
	
	var $fields=array(
		'name'=>'摘要',
		'amount'=>'数额',
		'date'=>'日期',
		'case'=>'案件',
		'case_fee'=>'对应应收帐款',
		'people'=>'人员',
		'comment'=>'备注',
		'display'=>'显示在列表'
	);
	
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$id=intval($id);
		
		$query="
			SELECT * 
			FROM account 
			WHERE id=$id AND company={$this->company->id}
		";
		return $this->db->query($query)->row_array();
	}
	
	function add(array $data=array()){
		
		if(!isset($data['date'])){
			$data['date']=$this->config->item('date');
		}

		$data=array_intersect_key($data, $this->fields);
		
		$data+=uidTime(true,true);
		
		$this->db->insert('account',$data);
		return $this->db->insert_id();
	}
	
	function update($id,array $data){
		$data=array_intersect_key($data, $this->fields);
		
		$data+=uidTime(false);
		
		$this->db->update('account',$data,array('id'=>$id));
	}
	
	function getList(){
		$query="
			SELECT
				account.id,account.time,account.name,account.amount,account.date,
				client.abbreviation AS client_name
			FROM account LEFT JOIN people client ON account.people=client.id
			WHERE amount<>0
		";
		
		if(!$this->user->isLogged('finance')){
			$query.=" AND account.case IN (SELECT `case` FROM case_lawyer WHERE lawyer={$this->user->id} AND role='主办律师')";
		}
		
		$query=$this->search($query,array('client.name'=>'客户','account.name'=>'名目','account.amount'=>'金额'));
		
		$query=$this->dateRange($query,'account.date',false);
		
		$query=$this->orderby($query,'date','DESC');
		
		$query=$this->pagination($query);
		
		return $this->db->query($query)->result_array();
	}
}
?>