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
		$this->db->select('account.*')
			->join('project','project.id = account.project','inner');
		
		if(isset($args['name'])){
			$this->db->like('account.name',$args['name']);
		}
		
		if(isset($args['type'])){
			$this->db->where('account.type',$args['type']);
		}
		
		if(isset($args['show_payer'])){
			$this->db->join('people payer',"payer.id = account.people", 'LEFT')
				->select('IF(payer.abbreviation IS NULL, payer.name, payer.abbreviation) AS payer_name,payer.id AS payer',false);
		}
		
		if(isset($args['project'])){
			$this->db->where('project',$args['project']);
		}
		
		if(isset($args['group']) && $args['group']==='account'){
			$this->db->group_by('account.account')
				->select('MAX(account.type) AS type, SUM(IF(received,amount,0)) AS received, SUM(IF(received,0,amount)) AS total, MAX(IF(received,date,NULL)) AS received_date, MAX(IF(received,NULL,date)) AS receivable_date, GROUP_CONCAT(account.comment) AS comment',false);
		}
		
		if(isset($args['group']) && $args['group']==='team'){
			$this->db->group_by('account.team')
				->join('team','team.id = account.team','inner')
				->select('team.name AS team_name, team.id AS team');
		}
		
		if(isset($args['date']['from']) && $args['date']['from']){
			$this->db->where("TO_DAYS(account.date) >= TO_DAYS('{$args['date']['from']}')",NULL,FALSE);
		}
		
		if(isset($args['date']['to']) && $args['date']['to']){
			$this->db->where("TO_DAYS(account.date) <= TO_DAYS('{$args['date']['to']}')",NULL,FALSE);
		}
		
		if(isset($args['contract_date']['from']) && $args['contract_date']['from']){
			$this->db->where("TO_DAYS(project.time_contract) >= TO_DAYS('{$args['contract_date']['from']}')",NULL,FALSE);
		}
		
		if(isset($args['contract_date']['to']) && $args['contract_date']['to']){
			$this->db->where("TO_DAYS(project.time_contract) <= TO_DAYS('{$args['contract_date']['to']}')",NULL,FALSE);
		}
		
		if(isset($args['received'])){
			if($args['received']===false){
				$this->db->where('received',false);
			}elseif($args['received']===true){
				$this->db->where('received',true);
			}
		}
		
		if(isset($args['team'])){
			$team=intval($args['team']);
			$this->db->where('account.team',$team);
		}
		
		if(isset($args['people'])){
			$people=intval($args['people']);
			$this->db->join("project_people','project_people.project = project.id AND project.people = $people",'inner');
			
			if(isset($args['role'])){
				$this->db->where('project_people.role',$args['role']);
			}
			
			if(isset($args['group']) && $args['group']==='people'){
				$this->db->group_by('project_people.people')
					->join('people','people.id = project.people','inner')
					->select('people.name AS people_name, people.id AS people');
			}
		
		}
		
		if(isset($args['sum']) && $args['sum']===true){
			$this->db->select('SUM(amount) AS sum');
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