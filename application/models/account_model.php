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
	
	/**
	 * @param array $args
	 * name: 帐目摘要
	 * received: 是否到帐
	 * project: 指定项目id
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
	 * group: 
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
		
		if(isset($args['show_project'])){
			$this->db->select('project.id AS project, project.name AS project_name');
			
			if(isset($args['project_name'])){
				$this->db->like('project.name',$args['project_name']);
			}
		}
		
		if(isset($args['show_payer'])){
			$this->db->join('people payer',"payer.id = account.people", 'LEFT')
				->select('IF(payer.abbreviation IS NULL, payer.name, payer.abbreviation) AS payer_name,payer.id AS payer',false);
			
			if(isset($args['payer_name'])){
				$this->db->like('payer.name',$args['payer_name']);
			}
		}
		
		if(isset($args['show_account'])){
			$this->db->join('account a','a.id = account.account','inner')
				->select('a.name AS name, a.type AS type');
		}
		
		if(isset($args['date']['from']) && $args['date']['from']){
			$this->db->where("TO_DAYS(account.date) >= TO_DAYS('{$args['date']['from']}')",NULL,FALSE);
		}
		elseif(isset($args['date/from']) && isset($args['date/from'])){
			$this->db->where("TO_DAYS(account.date) >= TO_DAYS('{$args['date/from']}')",NULL,FALSE);
		}
		
		if(isset($args['date']['to']) && $args['date']['to']){
			$this->db->where("TO_DAYS(account.date) <= TO_DAYS('{$args['date']['to']}')",NULL,FALSE);
		}
		elseif(isset($args['date/to']) && isset($args['date/to'])){
			$this->db->where("TO_DAYS(account.date) <= TO_DAYS('{$args['date/to']}')",NULL,FALSE);
		}
		
		//同时支持数组型和目录型的日期参数
		if(isset($args['contract_date']['from']) && $args['contract_date']['from']){
			$this->db->where("TO_DAYS(project.time_contract) >= TO_DAYS('{$args['contract_date']['from']}')",NULL,FALSE);
		}
		elseif(isset($args['contract_date/from']) && $args['contract_date/from']){
			$this->db->where("TO_DAYS(project.time_contract) >= TO_DAYS('{$args['contract_date/from']}')",NULL,FALSE);
		}
		
		if(isset($args['contract_date']['to']) && $args['contract_date']['to']){
			$this->db->where("TO_DAYS(project.time_contract) <= TO_DAYS('{$args['contract_date']['to']}')",NULL,FALSE);
		}
		elseif(isset($args['contract_date/to']) && $args['contract_date/to']){
			$this->db->where("TO_DAYS(project.time_contract) <= TO_DAYS('{$args['contract_date/to']}')",NULL,FALSE);
		}
		
		if(isset($args['team'])){
			$team=intval($args['team']);
			$this->db->where('account.team',$team);
		}
		
		if(isset($args['people'])){
			$people=intval($args['people']);
			$this->db->join('project_people',"project_people.project = project.id AND project_people.people = $people",'inner');
			
			if(isset($args['role'])){
				
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
		
		if(isset($args['group'])){
			if($args['group']==='account'){
				$this->db->group_by('account.account')
					->select('
						MAX(account.type) AS type, 
						SUM(IF(received,amount,0)) AS received, 
						SUM(IF(received,0,amount)) AS total, 
						MAX(IF(received,date,NULL)) AS received_date, 
						MAX(IF(received,NULL,date)) AS receivable_date, 
						GROUP_CONCAT(account.comment) AS comment
					',false);
			}
			elseif($args['group']==='team'){
				$this->db->group_by('account.team')
					->join('team','team.id = account.team','inner')
					->select('team.name AS team_name, team.id AS team');
			}
			elseif($args['group']==='people'){
				
				if($args['role']){
					
					$this->db->join('project_people','project_people.project = account.project','inner')
						->select('project_people.role, project_people.weight, account.amount * project_people.weight AS amount',false);
					
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
			elseif($args['group']==='month'){
				$this->db->group_by('LEFT(account.date,7)',false)
					->order_by('month')
					->select('LEFT(account.date,7) AS month',false);
			}
			elseif($args['group']==='month_contract'){
				$this->db->group_by('LEFT(project.time_contract,7)',false)
					->order_by('month')
					->select('LEFT(project.time_contract,7) AS month',false);
			}
		}

		if(isset($args['sum']) && $args['sum']===true){
			
			$key=array_search('account.*',$this->db->ar_select);
			if($key!==false){
				unset($this->db->ar_select[$key]);
			}
			
			$key=array_search('account.amount * project_people.weight AS amount',$this->db->ar_select);
			if($key!==false){
				unset($this->db->ar_select[$key]);
			}
			
			if(isset($args['role'])){
				$this->db->select('ROUND(SUM(amount)) * weight AS sum',false);
			}
			else{
				$this->db->select('ROUND(SUM(amount)) AS sum',false);
			}
			
			if(isset($args['ten_thousand_unit']) && $args['ten_thousand_unit']){
				$this->db->select('ROUND(SUM(amount)/1E4,1) AS sum',false);
			}
			
			$this->db->having('sum >',0);
		}else{
			if(isset($args['ten_thousand_unit']) && $args['ten_thousand_unit']){
				$this->db->select('ROUND(amount/1E4,1) AS amount',false);
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
		}else{
			$account=$this->db->select('project, team')
				->from('account')
				->where('id',intval($data['account']))
				->limit(1)
				->get()->row();
			$this->db->update('account',$account,array('id'=>$insert_id));
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