<?php
class Team_model extends People_model{
	
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * @param array $args
	 *	project 查找某个事务关联的组
	 *	leaded_by 根据组长查找组
	 *	open 开放报名 
	 */
	function getList($args=array()){
		
		$this->db->join('team','team.id = people.id','inner')
			->select('team.*');
		
		if(isset($args['project'])){
			$project=intval($args['project']);
			$this->db->join('project_team',"project_team.team = team.id AND project_team.project = $project",'inner');
		}
		
		if(isset($args['leaded_by'])){
			$this->db->where("team.leader{$this->db->escape_int_array($args['leaded_by'])}");
		}
		
		if(isset($args['get_leader'])){
			$this->db->join('people leader','leader.id = team.leader','left')
				->select('leader.name leader_name, leader.type leader_type');
		}
		
		if(isset($args['open'])){
			if($args['open']){
				$this->db->where('team.open',true);
			}
			else{
				$this->db->where('team.open',false);
			}
		}
		
		return parent::getList($args);
	}
	
	/**
	 * 追踪并返回一个人或组的所有父组
	 */
	function trace($id,$relation=NULL,$teams=array()){
		
		$id=intval($id);
		
		$result=$this->db->select('people.id, people.name, people.num, people.type')
			->from('people_relationship')
			->join('team','team.id = people_relationship.people','inner')
			->join('people','people.id = people_relationship.people','inner')
			->where(is_null($relation)?array('relative'=>$id):array('relative'=>$id,'relation'=>$relation))
			->get();
			
		foreach($result->result_array() as $row){
			$teams[$row['id']]=$row;
			$teams+=$this->trace($row['id'],$relation,$teams);
		}
		
		return $teams;
	}

	/**
	 * 根据部分团队名称返回匹配的id、名称和；类别列表
	 * @param $part_of_name
	 * @return array
	 */
	function match($part_of_name){
		$this->db->select('team.id,team.name,team.type')
			->from('team')
			->where('team.company',$this->company->id)
			->where('team.display',true)
			->like('name',$part_of_name)
			->order_by('team.id','desc');
		
		return $this->db->get()->result_array();
	}
	
}

?>
