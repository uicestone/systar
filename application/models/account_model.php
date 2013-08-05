<?php
class Account_model extends BaseItem_model{
	
	function __construct(){
		parent::__construct();
		$this->table='account';
		$this->fields=array_merge($this->fields,array(
			'type'=>'account',
			'subject'=>NULL,//科目
			'amount'=>0,//数额
			'received'=>false,//是否到账
			'date'=>$this->date->today,//日期
			'project'=>NULL,//案件
			'count'=>true,//是否记入创收
			'account'=>NULL,//关联帐目
			'people'=>NULL,//人员
			'comment'=>NULL,//备注
		));

	}
	
	/**
	 * @param array $args
	 * name: 帐目摘要
	 * received: 是否到帐
	 * project: 指定项目id
	 * project_labels
	 * project_without_labels
	 * show_payer: 获得付款人信息payer, payer_name
	 * date: (预估)到账日期
	 *	array(
	 *		from=>日期字符串
	 *		to=>日期字符串
	 *	)
	 * contract_date: 签约日期
	 *	array(
	 *		from=>日期字符串
	 *		to=>日期字符串
	 *	)
	 * group_by: 
	 *	account: 根据帐目编号分组并获得帐目中创收金额received, 总额total, receive_date, receivable_date, comment
	 *	team: 根据团组分组并获得team_name, team
	 *	people: 根据项目人员分组并获得people_name, people,role,type
	 *	month: 根据收款月份分组并获得month
	 *	month_contract: 根据签约月份分组并获得month
	 * team: 团队
	 * people: 指定项目人员
	 * role: 指定项目人员角色(需配合group:people或people)
	 * account: 指定帐目编号
	 * sum: 获得amount求和，此操作将使除group获得的其他值失去意义
	 * @return array
	 */
	function getList(array $args=array()){
		$this->db->select('account.*')
			->join('project','project.id = account.project','LEFT');
		
		if(isset($args['received'])){
			$this->db->where('account.received',(bool)intval($args['received']));
		}
		
		if(isset($args['project'])){
			$this->db->where('project',$args['project']);
		}
		
		if(isset($args['project_labels'])){
			foreach($args['project_labels'] as $id => $label_name){
				$this->db->join("project_label t_$id","account.project = t_$id.project AND t_$id.label_name = '$label_name'",'inner');
			}
		}
		
		if(isset($args['project_without_labels'])){
			foreach($args['project_without_labels'] as $id => $label_name){
				$this->db->where("account.project NOT IN (SELECT project FROM project_label WHERE label_name = '$label_name')");
			}
		}
		
		if(isset($args['show_project'])){
			$this->db->select('project.id project, project.type project_type, project.name project_name');
			
			if(isset($args['project_name'])){
				$this->db->like('project.name',$args['project_name']);
			}
		}
		
		if(isset($args['show_payer'])){
			$this->db->join('people payer',"payer.id = account.people", 'left')
				->select('IF(payer.abbreviation IS NULL, payer.name, payer.abbreviation) AS payer_name,payer.id AS payer',false);
			
			if(isset($args['payer_name'])){
				$this->db->like('payer.name',$args['payer_name']);
			}
		}
		
		if(isset($args['show_account'])){
			$this->db->join('account a','a.id = account.account','inner')
				->select('a.name AS name, a.type AS type');
		}
		
		foreach(array('date','contract_date') as $date_args){
			if(!isset($args[$date_args])){
				$args[$date_args]=array_prefix($args, $date_args);
			}
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
		
		if(isset($args['team'])){
			$team=intval($args['team']);
			$this->db->where('account.team',$team);
		}
		
		if(isset($args['people'])){
			$people=intval($args['people']);
			$this->db->join('project_people',"project_people.project = project.id AND project_people.people = $people",'inner');
			
			if(isset($args['role'])){
				
				$this->db->select('weight',false);
				
				if(is_array($args['role'])){
					$this->db->where_in('project_people.role',$args['role']);
				}
				else{
					$this->db->where('project_people.role',$args['role']);
				}
				
			}
		}
		
		if(isset($args['account'])){
			$account=intval($args['account']);
			$this->db->where('account.account',$account);
		}
		
		if(isset($args['amount'])){
			$this->db->where('account.amount',$args['amount']);
		}
		
		if(isset($args['reviewed'])){
			$this->db->where('account.received',(bool)intval($args['reviewed']));
		}
		
		if(isset($args['count'])){
			$this->db->where('account.count',(bool)intval($args['count']));
		}
		
		if(isset($args['group_by'])){
			if($args['group_by']==='account'){
				$this->db->group_by('account.account')
					->select('
						MAX(account.type) AS type, 
						SUM(IF(account.received,account.amount,0)) AS received_amount,
						SUM(IF(account.received,0,account.amount)) AS total_amount,
						MAX(IF(account.received,account.date,NULL)) AS received_date, 
						MAX(IF(account.received,NULL,account.date)) AS receivable_date, 
						GROUP_CONCAT(account.comment) AS comment
					',false);
			}
			elseif($args['group_by']==='team'){
				$this->db->group_by('account.team')
					->join('team','team.id = account.team','inner')
					->select('team.name AS team_name, team.id AS team');
			}
			elseif($args['group_by']==='people'){
				
				if($args['role']){
					
					$this->db->join('project_people','project_people.project = account.project','inner')
						->select('project_people.role, project_people.weight, account.amount * project_people.weight `amount`',false);
					
					if(is_array($args['role'])){
						$this->db->where_in('project_people.role',$args['role']);
					}
					else{
						$this->db->where('project_people.role',$args['role']);
					}
					
				}else{
					$this->db->join('project_people','project_people.project = account.project','inner');
				}
				
				$this->db->join('people','people.id = project_people.people','inner')
					->group_by('project_people.people')
					->select('people.name AS people_name, people.id AS people');
			}
			elseif($args['group_by']==='month'){
				$this->db->group_by('LEFT(account.date,7)',false)
					->order_by('month')
					->select('LEFT(account.date,7) AS month',false);
			}
			elseif($args['group_by']==='month_contract'){
				$this->db->group_by('LEFT(project.time_contract,7)',false)
					->order_by('month')
					->select('LEFT(project.time_contract,7) AS month',false);
			}
		}

		if(isset($args['sum']) && $args['sum']===true){
			
			array_remove_value($this->db->ar_select, 'account.*');
			array_remove_value($this->db->ar_select, '`amount`',true);
			
			if(isset($args['role'])){
				
				if(isset($args['ten_thousand_unit']) && $args['ten_thousand_unit']){
					$this->db->select('ROUND(SUM(account.amount * weight)/1E4,1) `sum`',false);
				}
				else{
					$this->db->select('ROUND(SUM(account.amount * weight)) `sum`',false);
				}
			}
			else{
				if(isset($args['ten_thousand_unit']) && $args['ten_thousand_unit']){
					$this->db->select('ROUND(SUM(account.amount)/1E4,1) `sum`',false);
				}
				else{
					$this->db->select('ROUND(SUM(account.amount)) `sum`',false);
				}
			}
			
			//$this->db->having('sum >',0);
		}else{
			if(isset($args['ten_thousand_unit']) && $args['ten_thousand_unit']){
				$this->db->select('ROUND(account.amount/1E4,1) `amount`',false);
			}
		}
		
		return parent::getList($args);
	}
	
	function getSum(array $args=array()){
		$args=array_merge($args,array('sum'=>true));
		$result_array=$this->getList($args);
		return isset($result_array[0]['sum'])?$result_array[0]['sum']:NULL;
	}

	function add(array $data=array()){
		
		foreach(array('account','received','people') as $field){
			if(empty($data[$field])){
				unset($data[$field]);
			}
		}
		
		$insert_id=parent::add($data);
		
		if(!isset($data['account'])){
			$this->db->update('account',array('account'=>$insert_id),array('id'=>$insert_id));
		}else{
			$account=$this->db->select('project, team, subject')
				->from('account')
				->where('id',intval($data['account']))
				->limit(1)
				->get()->row();
			$this->db->update('account',$account,array('id'=>$insert_id));
		}
		
		if(isset($data['received']) && $data['received']){
			
		}
		
		return $insert_id;
	}
	
}
?>