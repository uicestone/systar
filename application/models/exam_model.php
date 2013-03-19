<?php
class Exam_model extends SS_Model{
	function __construct(){
		parent::__construct();
	}
	
	function getList(){
		$q="SELECT 
				exam.id AS id,exam.name AS name,exam.term AS term,exam.is_on,exam.seat_allocated,exam.depart,
				grade.name AS grade_name
			FROM exam INNER JOIN grade ON exam.grade=grade.id
			WHERE
				1=1
			";
				
		$q=$this->orderby($q,'exam.id','DESC',array('exam.name'));
		
		$q=$this->pagination($q);
		
		return $this->db->query($q)->result_array();
	}
	
	/**
	 * 考试相关信息列表，用于阅卷模块
	 */
	function getInfoList(){
		$query="
		SELECT 
			exam.id AS exam,exam.name AS name,
			exam_paper.id AS exam_paper,exam_paper.is_extra_course AS is_extra_course,
			if(exam_paper.is_extra_course,course.id,NULL) AS extra_course,
			grade.name AS grade_name,course.id AS course,course.name AS course_name,
			exam_paper.students AS students, exam_paper.teacher_group AS teacher_group 
		FROM 
			exam_paper 
			INNER JOIN exam ON (exam_paper.exam=exam.id)
			INNER JOIN course ON exam_paper.course=course.id
			INNER JOIN grade ON exam.grade=grade.id
		WHERE  exam_paper.is_scoring=1 
			AND exam.is_on=1
			AND (".db_implode($_SESSION['teacher_group'],' OR ','teacher_group').')';
		
		return $this->db->query($query)->result_array();
	}
	
	function getPaperList($exam_id){
		$q="SELECT 
				course.id AS course,course.name AS course_name,
				exam_paper.id AS id,exam_paper.is_extra_course,exam_paper.students,exam_paper.is_scoring,exam_paper.term,
				grade.name,
				staff_group.name AS teacher_group_name
			FROM exam_paper
				INNER JOIN course ON course.id=exam_paper.course
				INNER JOIN exam ON exam_paper.exam=exam.id
				INNER JOIN grade ON grade.id=exam.grade
				LEFT JOIN staff_group ON staff_group.id=exam_paper.teacher_group
			WHERE
				exam.id='{$exam_id}'
			";
				
		$q=$this->orderby($q,'course.id');
		
		$q=$this->pagination($q);
		
		return $this->db->query($q)->result_array();
	}
	
	function getSeatList($exam_id){
		$q="SELECT 
				view_student.num,view_student.class_name,view_student.name AS student_name,
				exam_student.room,exam_student.seat,course.name AS course_name
			FROM exam_student INNER JOIN view_student ON exam_student.student=view_student.id
				LEFT JOIN course ON exam_student.extra_course=course.id
			WHERE
				exam_student.exam='".$exam_id."'
		";
				
		$q=$this->orderby($q,'view_student.num','ASC');
		
		$q=$this->pagination($q);
		
		return $this->db->query($q)->result_array();
	}

	function allocate_seat(){
		set_time_limit(0);
		
		$r_active_exam=$this->db->query("SELECT id,depart,grade FROM exam WHERE is_on=1 ORDER BY depart");
		//列出激活的考试
		
		$this->db->query("CREATE TEMPORARY TABLE exam_student_temp SELECT * FROM exam_student WHERE 1=0");
		
		while($exam=db_fetch_array($r_active_exam)){
			//向exam_student表插入学生，生成随机数，教室和座位留空
			$this->db->query("
				INSERT INTO exam_student (exam,student,depart,extra_course,time,rand)
				SELECT ".$exam['id'].",id,depart,extra_course,".$this->date->now.",1000*rand() FROM view_student 
				WHERE grade = ".$exam['grade']." AND depart='".$exam['depart']."'
				ORDER BY extra_course
			");
		}
		
		//建立教室和年级的对应关系
		$q_exam_room="
			SELECT exam_room.capacity,exam_room.id AS room,
				exam.id AS exam,exam.depart
			FROM exam_room INNER JOIN exam ON ( exam_room.grade = exam.grade AND exam_room.depart = exam.depart )
			WHERE exam.is_on=1
		";
		$r_exam_room=$this->db->query($q_exam_room);
		
		while($exam_room=db_fetch_array($r_exam_room)){
			$q_update_exam_student_of_a_room="
				UPDATE 
				(
					SELECT exam_student.id,exam_room.name AS room FROM `exam_student`,exam_room 
					WHERE room IS NULL AND exam='".$exam_room['exam']."' AND exam_room.id='".$exam_room['room']."'
					ORDER BY exam_student.extra_course,exam_student.rand
					LIMIT ".$exam_room['capacity']."
				)roomed,
				exam_student
				SET exam_student.room=roomed.room 
				WHERE exam_student.id=roomed.id";
				
			$this->db->query($q_update_exam_student_of_a_room);
		}
		$q_exam_student="SELECT * FROM `exam_student` WHERE exam IN (SELECT id FROM exam WHERE is_on=1) ORDER BY room,rand";
		$r_exam_student=$this->db->query($q_exam_student);
		
		$seat=1;
		$room='';
		while($exam_student=db_fetch_array($r_exam_student)){
			if($room==$exam_student['room']){
				$seat++;
			}else{
				$seat=1;
			}
			$q_update_seat="UPDATE exam_student SET seat='".$seat."' WHERE id = '".$exam_student['id']."'";
			$this->db->query($q_update_seat);
			$room=$exam_student['room'];
		}
		
		$this->db->query("UPDATE exam SET seat_allocated=1 WHERE is_on=1");
	}
}
?>