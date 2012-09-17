<?php
if(got('update') && is_posted('id')){
	$data=array($_POST['field']=>$_POST['value']);
	db_update($_POST['table'],$data,"id='".intval($_POST['id'])."'");
	
}elseif(got('action','exam')){
	$new_exam=array_trim($_POST);
	
	if(is_numeric($new_exam['grade_name'])){
		$new_exam['grade']=$new_exam['grade_name'];
	
	}else{
		$r=db_query("SELECT id FROM grade WHERE name LIKE '%".$new_exam['grade_name']."%'");
		if(db_rows($r)==1){
			$new_exam['grade']=mysql_result($r,0,'id');
		}else{
			echo ' 没有这个年级或存在多个匹配';
		}
	}
	unset($new_exam['grade_name']);
	
	if(!array_diff(array('name','depart','grade','term','is_on'),array_keys($new_exam))){
		$insert_id=db_insert('exam',$new_exam);
		$r=db_query('
			SELECT 
				exam.id,exam.name,exam.term,exam.is_on,exam.depart,
				grade.name AS grade_name
			FROM exam INNER JOIN grade ON exam.grade=grade.id 
			ORDER BY id DESC LIMIT 1
		');
		$exam=db_fetch_array($r);
		if($exam['id']==$insert_id){
			echo json_encode($exam);
		}
	}
}elseif(got('action','exam_paper')){
	$new_line=array_trim($_POST);

	$r_course=db_query("SELECT id FROM course WHERE name LIKE '".$new_line['course_name']."'");
	if(db_rows($r_course)==1){
		$new_line['course']=mysql_result($r_course,0,'id');
		unset($new_line['course_name']);
	}else{
		echo '没有这个学科';
	}

	$new_line_id=db_insert('exam_paper',$new_line);
	
	//_imperfect 此二处采取了全部更新，理应更新一条即可 uicestone 2012/2/15
	#更新exam_paper的students
	db_query("
		UPDATE 
		exam_paper,
		(
			SELECT exam_paper.id,exam_paper.exam,extra_course,COUNT(1) AS students 
			FROM exam_student 
				LEFT JOIN exam_paper ON (exam_paper.exam=exam_student.exam AND
			(exam_paper.is_extra_course=0 OR exam_paper.course=exam_student.extra_course)) 
			WHERE exam_paper.exam IN (SELECT id FROM exam WHERE is_on=1)
			GROUP BY exam_paper.id
		)exam_paper_students
		
		SET exam_paper.students=exam_paper_students.students
		WHERE exam_paper.id=exam_paper_students.id
	");
	
	#按照备课组分配试卷权限
	db_query("
		UPDATE 
		exam_paper INNER JOIN exam ON exam_paper.exam=exam.id
		INNER JOIN staff_group ON
		(
		exam_paper.course=staff_group.course
		AND
		(exam.grade=staff_group.grade OR staff_group.grade=0)
		)
		AND exam_paper.is_scoring=1
		SET exam_paper.teacher_group=staff_group.id
	");

	$r=db_query('
		SELECT 
			course.name AS course_name,
			exam_paper.id AS id,exam_paper.is_extra_course,exam_paper.students,exam_paper.is_scoring,
			staff_group.name AS teacher_group_name
		FROM exam_paper
			INNER JOIN course ON course.id=exam_paper.course
			LEFT JOIN staff_group ON staff_group.id=exam_paper.teacher_group
		WHERE exam_paper.id='.$new_line_id.'
	');

	if($exam=db_fetch_array($r)){
		echo json_encode($exam);
	}
}
?>