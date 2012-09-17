<?php
$q_exam="SELECT 
	exam.id AS exam,exam.name AS name,exam_paper.id AS exam_paper,exam_paper.is_extra_course AS is_extra_course,
	grade.name AS grade_name,course.id AS course,course.name AS course_name,
	exam_paper.students AS students, exam_paper.teacher_group AS teacher_group 
FROM 
	(
		(
			exam_paper LEFT JOIN exam ON (exam_paper.exam=exam.id)
		)
		LEFT JOIN course ON exam_paper.course=course.id
	)
	LEFT JOIN grade ON exam.grade=grade.id
WHERE  exam_paper.is_scoring=1
	AND (".db_implode($_SESSION['teacher_group'],' OR ','teacher_group').')';

$examArray=db_toArray($q_exam);

if(got('exam_paper')){
	foreach($examArray as $exam){
		if($exam['exam_paper']==$_GET['exam_paper']){
			$currentExam=$exam;
		}
	}
	
}elseif(count($examArray)>0){
	$currentExam=$examArray[0];
}else{
	$currentExam=false;
}


$q_partArray="
	SELECT * FROM exam_part WHERE exam_paper='".$currentExam['exam_paper']."'
";

$partArray=db_toArray($q_partArray);

$q_students_left="
	SELECT *
		FROM score
	WHERE score.exam_paper='".$currentExam['exam_paper']."'
	GROUP BY student
";

$r_students_left=mysql_query($q_students_left);
$student_left=$currentExam['students']-db_rows($r_students_left);
?>