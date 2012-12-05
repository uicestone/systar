<?php
class Classes_model extends Team_model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="
			SELECT * 
			FROM class 
			WHERE id='{$id}' AND company='{$this->config->item('company/id')}'";
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
		
		$q_student_class="
			SELECT team_people.id_in_team AS num_in_class,
				CONCAT(RIGHT(10000+team.num,4),team_people.id_in_team) AS num,
				team.id AS class,team.name AS class_name,
				people.name AS class_teacher_name
			FROM team_people
				INNER JOIN team ON team_people.team=team.id
				LEFT JOIN people ON team.leader=people.id
			WHERE team.company={$this->config->item('company/id')}
				AND team_people.people=$student_id
				AND team_people.term='{$this->school->current_term}'
		";
		
		return $this->db->query($q_student_class)->row_array();
	}
	
	function getList(){
		$q="
			SELECT class.id, class.name, grade.name AS grade_name, depart.name AS depart, course.name AS extra_course_name,
				teacher.name AS class_teacher_name
			FROM team AS class 
				INNER JOIN team_relationship AS grade_class ON grade_class.relative=class.id
				INNER JOIN team AS grade ON grade.id=grade_class.team AND grade.type='grade'
				INNER JOIN team_relationship AS depart_class ON depart_class.relative=class.id
				INNER JOIN team AS depart ON depart.id=depart_class.team AND depart.type='depart'
				LEFT JOIN course ON course.id = class.extra_course
				LEFT JOIN people AS teacher ON teacher.id = class.leader
			WHERE class.company={$this->config->item('company/id')} AND class.display=1 
				AND class.type='class'
				AND grade.num>='".$this->school->highest_grade."'
		";
		
		$q=$this->addCondition($q,array('grade'=>'grade.id'));
				
		$q=$this->search($q,array('class.name'=>'班级','depart.name'=>'部门'));
		
		$q=$this->orderby($q,'class.num','ASC');
		
		$q=$this->pagination($q);
		
		return $this->db->query($q)->result_array();
	}
	
	function check($class_name,$data_type='id',$show_error=true,$save_to=NULL){
		//$data_type:id,array
		if(!$class_name){
			if($show_error){
				showMessage('请输入班级名称','warning');
			}
			return -3;
		}
	
		$q_lawyer="SELECT * FROM `class` WHERE `name` LIKE '%".$class_name."%'";
		$r_lawyer=db_query($q_lawyer);
		$num_classes=db_rows($r_lawyer);
	
		if($num_classes==0){
			if($show_error){
				showMessage('没有这个班级','warning');
			}
			return -1;
			
		}elseif($num_classes>1){
			if($show_error){
				showMessage('此关键词存在多个符合班级','warning');
			}
			return -2;
	
		}else{
			$data=db_fetch_array($r_lawyer);
			if($data_type=='array'){
				$return=$data;
			}else{
				$return=$data[$data_type];
			}
			
			if(!is_null($save_to)){
				post($save_to,$return);
			}
			return $return;
		}
	}
	
	function fetchByStudentId($student_id){
		return $this->db->query("
			SELECT * FROM class 
			WHERE id = (
				SELECT class FROM student_class 
				WHERE student = '{$student_id}'
					AND term='{$this->school->current_term}'
			)
		")->row_array();
	}

	/**
	 * 获得团队负责人信息
	 */
	function fetchLeader($team_id,$field=NULL){
		$query="
			SELECT name 
			FROM staff 
			WHERE id=(SELECT class_teacher FROM class WHERE id='{$team_id}')
		";
		
		$row=$this->db->query($query)->row_array();
		if(isset($field) && isset($row[$field])){
			return $row[$field];
		}else{
			return $row;
		}
	}
	
	function getLeadersList($team_id){
		$team_id=intval($team_id);
		
		$query="
			SELECT student.name,student_class.position 
			FROM student INNER JOIN student_class 
				ON (student.id=student_class.student AND student_class.term='{$this->school->current_term}')
			WHERE student_class.class=$team_id
				AND student_class.position IS NOT NULL
		";
		
		return $this->db->query($query)->result_array();
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
			WHERE team.company={$this->config->item('company/id')} AND  team.display=1 
		";
			
		if(isset($team_id)){
			$query.=" AND team_relationship.team=$team_id";
		}

		$result=$this->db->query($query)->result_array();
		
		return array_sub($result,'name','id');
	}
}
?>