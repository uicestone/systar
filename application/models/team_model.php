<?php
class Team_model extends BaseItem_model{
	
	function __construct() {
		parent::__construct();
		$this->table='team';
	}
	
	/**
	 * @param array $args
	 *	people_type	只列出含有某类人员的组
	 */
	function getList($args=array()){
		
		if(isset($args['people_type'])){
			$this->db->join('team_people','team_people.team = team.id','INNER')
				->join('people',"people.id = team_people.people AND people.type = '{$args['people_type']}'",'INNER');
		}
		
		if(isset($args['has_relative']) && $args['has_relative']){
			$this->db->join('team_relationship has_relative',"has_relative.team = team.id",'INNER');
		}
		
		if(isset($args['is_relative_of'])){
			if(is_array($args['is_relative_of']) && count($args['is_relative_of'])>0){
				$this->db->join('team_relationship is_relative_of',"is_relative_of.relative = team.id",'INNER')
					->where_in('is_relative_of.team',$args['is_relative_of']);
			}
			elseif(!is_array($args['is_relative_of'])){
				$this->db->join('team_relationship is_relative_of',"is_relative_of.relative = team.id",'INNER')
					->where('is_relative_of.team',intval($args['is_relative_of']));
			}
		}
		
		return parent::getList($args);
	}
	
	/**
	 * 返回人的所在组，并追溯及其父组
	 * @param int $people
	 * @return array(team_id=>team_name,...)
	 */
	function traceByPeople($people){
		
		$people=intval($people);
		
		$teams=array();
		
		$result=$this->db->select('team.id,team.name')
			->from('team_people')
			->join('team',"team.id = team_people.team",'INNER')
			->where('people',$people)
			->get();
		
		foreach($result->result() as $row){
			$teams[$row->id]=$row->name;
			$teams+=$this->trace($row->id);
		}
		
		return $teams;
		
	}
	
	/**
	 * 追踪并返回一个组的所有父组
	 */
	function trace($id,$relation=NULL,$teams=array()){
		
		$id=intval($id);
		
		$result=$this->db->select('team.id,team.name')
			->from('team_relationship')
			->join('team','team.id = team_relationship.team','INNER')
			->where(is_null($relation)?array('relative'=>$id):array('relative'=>$id,'relation'=>$relation))
			->get();
			
		foreach($result->result() as $row){
			$teams[$row->id]=$row->name;
			$teams+=$this->trace($row->id,$relation,$teams);
		}
		
		return $teams;
	}

}

?>
