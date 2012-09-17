<?php
if(is_posted('partChooseSubmit')){
	//刚从试卷选择界面到登分,界面获得当前大题和所属试卷信息
	$q_exam_part="SELECT 
		exam.id AS exam,exam.name AS name,
		exam_paper.id AS exam_paper,exam_paper.is_extra_course,exam_paper.course AS course,
		exam_part.id AS exam_part, exam_part.name AS part_name,
		grade.name AS grade_name,course.name AS course_name,
		exam_paper.students AS students, exam_paper.teacher_group AS teacher_group 
	FROM 
		(
			(
				(
					exam_paper INNER JOIN exam ON (exam_paper.id='".$_POST['exam_paper']."' AND exam_paper.exam=exam.id AND exam_paper.is_scoring=1)
				)
				INNER JOIN exam_part ON (exam_part.id='".$_POST['part']."' AND exam_paper.id=exam_part.exam_paper)
			)
			INNER JOIN course ON exam_paper.course=course.id
		)
		INNER JOIN grade ON exam.grade=grade.id
	WHERE 
		".db_implode($_SESSION['teacher_group'],' OR ','teacher_group');
	$r_exam_part=mysql_query($q_exam_part);
	$_SESSION['score']['currentExam']=mysql_fetch_array($r_exam_part);
}

if(is_null(array_dir('_SESSION/score/currentStudent_id_in_exam'))){
	array_dir('_SESSION/score/currentStudent_id_in_exam',1);
}

if(is_posted('nextScore') || is_posted('previousScore') || is_posted('backToPartChoose')){
	
	$scoreData=array(
		'student'=>$_SESSION['score']['currentStudent']['student'],
		'exam'=>$_SESSION['score']['currentExam']['exam'],
		'exam_paper'=>$_SESSION['score']['currentExam']['exam_paper'],
		'exam_part'=>$_SESSION['score']['currentExam']['exam_part'],
		'score'=>is_posted('is_absent')?'0':$_POST['score'],
		'is_absent'=>is_posted('is_absent')?'1':'0',
		'scorer'=>$_SESSION['id'],
		'scorer_username'=>$_SESSION['username'],
		'time'=>$_G['timestamp']
	);
	
	if($_POST['score']!=$_SESSION['score']['currentScore']['score'] || isset($_POST['is_absent'])!=$_SESSION['score']['currentScore']['is_absent'])
		db_insert('score',$scoreData,false,true);//当前学生-大题-分数插入数据表，不返回insertid，使用replace
	
	if(is_posted('nextScore'))
		$_SESSION['score']['currentStudent_id_in_exam']++;
	if(is_posted('previousScore'))
		$_SESSION['score']['currentStudent_id_in_exam']--;
	if(is_posted('backToPartChoose')){
		unset($_SESSION['score']['currentExam']);
		redirect('score.php');
	}
}

if(is_posted('studentSearch')){
	$q_student="
		SELECT * FROM exam_student,view_student 
		WHERE view_student.num='".$_POST['studentNumForSearch']."'
			AND exam_student.student=view_student.id
			AND exam_student.exam='".$_SESSION['score']['currentExam']['exam']."'
	";
}else{
	$q_student="
		SELECT * FROM
			(
				SELECT * 
				FROM exam_student
				WHERE exam='".$_SESSION['score']['currentExam']['exam']."'
				AND (".(int)!$_SESSION['score']['currentExam']['is_extra_course']." OR extra_course='".$_SESSION['score']['currentExam']['course']."')
				ORDER BY room, seat
				LIMIT ".($_SESSION['score']['currentStudent_id_in_exam']-1).",1
			)current_exam_student
		LEFT JOIN view_student ON view_student.id = current_exam_student.student
	";
}
$r_student=mysql_query($q_student);
$_SESSION['score']['currentStudent']=mysql_fetch_array($r_student);

$q_score="SELECT * FROM score WHERE student='".$_SESSION['score']['currentStudent']['student']."' AND exam_part='".$_SESSION['score']['currentExam']['exam_part']."' LIMIT 1";
$r_score=mysql_query($q_score);
if(db_rows($r_score)==0){
	$_SESSION['score']['currentScore']=array();
}else{
	$_SESSION['score']['currentScore']=mysql_fetch_array($r_score);
}
?>