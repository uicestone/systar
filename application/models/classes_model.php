<?php
class Classes_model extends Team_model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="
			SELECT * 
			FROM class 
			WHERE id='{$id}' AND company='{$this->company->id}'";
		return $this->db->query($query)->row_array();
	}
	
	/**
	 * 根据学生id返回本学期所在班级的信息
	 * @param int $student_id
	 * @return array array(
	 *	num_in_class=>班中学号
	 *	num=>学号
	 *	class_name=>班级名称
	 *	class_teacher_name=>班主任姓名
	 * )
	 */
	function fetchByStudent($student_id){
		$student_id=intval($student_id);
		
		$this->db->select('
			team.id,team.name,team.leader,leader.name AS leader_name,
			team_people.id_in_team,
			CONCAT(RIGHT(10000+team.num,4),team_people.id_in_team) AS num
		',false)
			->from('team')
			->join('team_people','team_people.team = team.id','inner')
			->join('people leader','leader.id = team.leader','left')
			->where('team_people.people',$student_id)
			->where('team_people.till >= CURDATE()',NULL,FALSE);
		
		return $this->db->get()->row_array();
	}
	
	/**
	 * 获得一个团队的相关团队
	 * @param 被关联团队id
	 * @param 关联名称，如“隶属”
	 * @return array(related_team_id_1=>related_team_name_1,...)
	 */
	function getRelatedTeams($team_id=NULL,$relation=NULL,$type=NULL){
		isset($team_id) && $team_id=intval($team_id);
		
		$query="
			SELECT team.id,team.name 
			FROM team 
				INNER JOIN team_relationship ON team.id=team_relationship.relative";
		
		if(isset($relation)){
			$query.=" AND team_relationship.relation='$relation'";
		}
		
		if(isset($type)){
			$query.=" AND team.type='$type'";
		}
		
		$query.="
			WHERE team.company={$this->company->id} AND  team.display=1 
		";
			
		if(isset($team_id)){
			$query.=" AND team_relationship.team=$team_id";
		}

		$result=$this->db->query($query)->result_array();
		
		return array_sub($result,'name','id');
	}
}
?>