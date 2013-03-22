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
		
		$args['orderby']=false;
		
		return parent::getList($args);
	}
	
	/**
	 * 返回人的所在组，并追溯及其父组
	 * @param int $people
	 */
	function traceByPeople($people){
		
		$people=intval($people);
		
		$teams=array();
		
		$result=$this->db->get_where('team_people',array('people'=>$people));
		
		foreach($result->result() as $row){
			$teams[]=$row->team;
			$teams+=$this->trace($row->id);
		}
		
		return $teams;
		
	}
	
	/**
	 * 追踪并返回一个组的所有父组
	 */
	function trace($id,$relation=NULL,$teams=array()){
		
		$id=intval($id);
		
		$result=$this->db->get_where('team_relationship',
			is_null($relation)?array('relative'=>$id):array('relative'=>$id,'relation'=>$relation)
		);
		
		foreach($result->result() as $row){
			$teams[]=$row->id;
			$teams+=$this->trace($row->id);
		}
		
		return $teams;
	}

}

?>
