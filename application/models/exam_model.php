<?php
class Evaluation extends SS_Model{
	function __construct(){
		parent::__construct();
	}

	function allocate_seat(){
		global $_G;
		
		set_time_limit(0);
		
		$r_active_exam=db_query("SELECT id,depart,grade FROM exam WHERE is_on=1 ORDER BY depart");
		//列出激活的考试
		
		db_query("CREATE TEMPORARY TABLE exam_student_temp SELECT * FROM exam_student WHERE 1=0");
		
		while($exam=db_fetch_array($r_active_exam)){
			//向exam_student表插入学生，生成随机数，教室和座位留空
			db_query("
				INSERT INTO exam_student (exam,student,depart,extra_course,time,rand)
				SELECT ".$exam['id'].",id,depart,extra_course,".$_G['timestamp'].",1000*rand() FROM view_student 
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
		$r_exam_room=db_query($q_exam_room);
		
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
				
			db_query($q_update_exam_student_of_a_room);
		}
		$q_exam_student="SELECT * FROM `exam_student` WHERE exam IN (SELECT id FROM exam WHERE is_on=1) ORDER BY room,rand";
		$r_exam_student=db_query($q_exam_student);
		
		$seat=1;
		$room='';
		while($exam_student=db_fetch_array($r_exam_student)){
			if($room==$exam_student['room']){
				$seat++;
			}else{
				$seat=1;
			}
			$q_update_seat="UPDATE exam_student SET seat='".$seat."' WHERE id = '".$exam_student['id']."'";
			db_query($q_update_seat);
			$room=$exam_student['room'];
		}
		
		db_query("UPDATE exam SET seat_allocated=1 WHERE is_on=1");
	}
}
?>