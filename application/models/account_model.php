<?php
class Account_model extends BaseItem_model{
	
	static $fields=array(
		'name'=>'摘要',
		'type'=>'类型',
		'subject'=>'科目',
		'amount'=>'数额',
		'received'=>'是否到账',
		'date'=>'日期',
		'project'=>'案件',
		'account'=>'关联帐目',
		'people'=>'人员',
		'comment'=>'备注',
		'display'=>'显示在列表'
	);
	
	function __construct(){
		parent::__construct();
		$this->table='account';
	}
	
	function getList($args=array()){
		$this->db->select('account.*,people.name');
		$this->db->join('people',"people.id = account.people", 'LEFT');
		
		if(isset($args['name'])){
			$this->db->like('account.name',$args['name']);
		}
		
		if(isset($args['type'])){
			$this->db->where('account.type',$args['type']);
		}
		
		if(isset($args['project'])){
			$this->db->where('project',$args['project']);
		}
		
		if(isset($args['group']) && $args['group']==='account'){
			$this->db->group_by('account.account')
				->select('MAX(account.type) AS type, SUM(IF(received,amount,0)) AS received, SUM(IF(received,0,amount)) AS total, MAX(IF(received,date,NULL)) AS received_date, MAX(IF(received,NULL,date)) AS receivable_date, GROUP_CONCAT(account.comment) AS comment',false);
		}
		
		return parent::getList($args);
	}

	function add(array $data=array()){
		
		$data=array_intersect_key($data, self::$fields);
		
		if(isset($data['account']) && !$data['account']){
			unset($data['account']);
		}
		
		if(isset($data['comment']) && $data['comment']===''){
			unset($data['comment']);
		}
		
		$data['display']=true;
		
		$data+=uidTime(true,true);
		
		$this->db->insert('account',$data);
		$insert_id=$this->db->insert_id();
		
		if(!isset($data['account'])){
			$this->db->update('account',array('account'=>$insert_id),array('id'=>$insert_id));
		}
		
		return $insert_id;
	}
	
	function update($id,array $data){
		$data=array_intersect_key($data, self::$fields);
		
		$data+=uidTime(false);
		
		$this->db->update('account',$data,array('id'=>$id));
	}
	
}
?>