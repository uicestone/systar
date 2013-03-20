<?php
class Account_model extends BaseItem_model{
	
	static $fields=array(
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
		$this->table='account';
	}

	function add(array $data=array()){
		
		if(!isset($data['date'])){
			$data['date']=$this->date->today;
		}

		$data=array_intersect_key($data, self::$fields);
		
		$data+=uidTime(true,true);
		
		$this->db->insert('account',$data);
		return $this->db->insert_id();
	}
	
	function update($id,array $data){
		$data=array_intersect_key($data, self::$fields);
		
		$data+=uidTime(false);
		
		$this->db->update('account',$data,array('id'=>$id));
	}
	
}
?>