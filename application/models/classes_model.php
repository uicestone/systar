<?php
class Classes_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function fetch($id){
		$query="SELECT * FROM class WHERE id='".$id."'";
		return $this->db->query($query)->row_array();
	}
	
	function getList(){
		$q="
			SELECT class.id, class.name, grade.name AS grade_name, depart, course.name AS extra_course_name,
				staff.name AS class_teacher_name
			FROM class INNER JOIN grade ON class.grade = grade.id
				LEFT JOIN course ON course.id = class.extra_course
				LEFT JOIN staff ON staff.id = class.class_teacher
			WHERE grade>='".$_SESSION['global']['highest_grade']."'
		";
		
		$q=$this->addCondition($q,array('grade'=>'class.grade'));
				
		$q=$this->search($q,array('name'=>'班级','depart'=>'部门'));
		
		$q=$this->orderby($q,'class.id','ASC');
		
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
					AND term='{$_SESSION['global']['current_term']}'
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
		$query="
			SELECT student.name,student_class.position 
			FROM student INNER JOIN student_class 
				ON (student.id=student_class.student AND student_class.term='".$_SESSION['global']['current_term']."')
			WHERE student_class.class='{$team_id}'
				AND student_class.position IS NOT NULL
		";
		
		return $this->db->query($query)->result_array();
	}
}
?>