<?php
class Score_model extends CI_Model{
	function __construct(){
		parent::__construct();
	}
	
	function getList(){
		$q="SELECT * FROM view_score INNER JOIN view_student ON view_student.id=view_score.student WHERE 1=1";

		if(!option('class') && !option('grade')){
			$manage_class=$this->db->query("SELECT id,grade FROM class WHERE class_teacher='{$this->user->id}'")->row_array();
			if($manage_class){
				//将班主任的视图定位到自己班级
				option('class',$manage_class['id']);
				option('grade',$manage_class['grade']);
			}else{
				//默认显示的年级
				option('grade',$this->school->highest_grade);
			}
		}

		$q=$this->addCondition($q,array('class'=>'view_student.class','grade'=>'view_student.grade'),array('grade'=>array('class','exam')));

		if(!option('exam')){
			option('exam',db_fetch_field("SELECT id FROM exam WHERE grade='".option('grade')."' ORDER BY id DESC LIMIT 1"));
		}

		$q=$this->addCondition($q, array('exam'=>'view_score.exam'));

		$q=$this->search($q,array('view_student.name'=>'学生'));

		$q=$this->orderby($q,'view_student.num');
		
		$q=$this->pagination($q);

		return $this->db->query($q)->result_array();
	}
	
	function getAvg(){
		$query="
		SELECT *,
			ROUND(AVG(course_1),2) AS course_1,
			ROUND(AVG(course_2),2) AS course_2,
			ROUND(AVG(course_3),2) AS course_3,
			ROUND(AVG(course_4),2) AS course_4,
			ROUND(AVG(course_5),2) AS course_5,
			ROUND(AVG(course_6),2) AS course_6,
			ROUND(AVG(course_7),2) AS course_7,
			ROUND(AVG(course_8),2) AS course_8,
			ROUND(AVG(course_9),2) AS course_9,
			ROUND(AVG(course_10),2) AS course_10,
			ROUND(AVG(course_sum_3),2) AS course_sum_3,
			ROUND(AVG(course_sum_5),2) AS course_sum_5
		FROM view_score INNER JOIN view_student ON view_student.id=view_score.student
		WHERE exam IN (SELECT id FROM exam WHERE is_on=1)";
		
		$query=$this->addCondition($query,array('class'=>'view_student.class','grade'=>'view_student.grade','exam'=>'view_score.exam'),array('grade'=>'class'));
		
		return $this->db->query($query)->result_array();
	}
	
}

?>
