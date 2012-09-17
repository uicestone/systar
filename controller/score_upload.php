<?php
$q_exam="
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

$examArray=db_toArray($q_exam);

if(got('exam_paper')){
	foreach($examArray as $exam){
		if($exam['exam_paper']==intval($_GET['exam_paper'])){
			$currentExam=$exam;
		}
	}
	
}elseif(!empty($examArray)>0){
	$currentExam=$examArray[0];

}else{
	$currentExam=false;
}

while(is_posted('submit/score_table')){
	if ($_FILES['score_table']['error'] > 0){
		showMessage('文件上错错误：错误代码: '.$_FILES['score_table']['error'],'warning');break;
	}
	
	if(!(preg_match('/\.(\w*?)$/',$_FILES['score_table']['name'],$extname_match) && $extname_match[1]=='xls')){
		showMessage('文件格式错误，请上传xls格式的excel表格','warning');break;
	}

	require 'plugin/PHP-ExcelReader/reader.php';
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('utf-8');
	$data->setRowColOffset(0);

	$data->read($_FILES['score_table']['tmp_name']);
	
	$rows=$data->sheets[0]['numRows'];
	$cols=$data->sheets[0]['numCols'];
	
	$exam_part_array=array();

	$break=false;
	for($i=1;$i<$cols;$i++){
		if($data->sheets[0]['cells'][0][$i]=='' || is_numeric($data->sheets[0]['cells'][0][$i])){
			showMessage('某大题的名字是空的或是数字','warning');
			$break=true;break;
		}
		$exam_part_data_array[]=array('exam_paper'=>$currentExam['exam_paper'],'name'=>$data->sheets[0]['cells'][0][$i]);
	}
	if($break)break;

	for($i=1;$i<$rows;$i++){
		for($j=1;$j<$cols;$j++){
			$cell = isset($data->sheets[0]['cells'][$i][$j])?$data->sheets[0]['cells'][$i][$j]:'';
			if(!(is_numeric($cell) || $cell=='') || $cell<0){
				showMessage('第'.($i+1).'行 第'.($j+1).'列的数据'.$cell.'中包含错误字符，注意只能是数字或留空（缺考）','warning');
				$break=true;break;
			}
		}
		if($break)break;
		if(array_sum(array_slice($data->sheets[0]['cells'][$i],1))>150){
			showMessage('第'.$i.'行的小分和超过了150分！注意不用填写总分','warning');
			$break=true;break;
		}
	}
	if($break)break;

	if($rows-1<$currentExam['students']){
		//showMessage('本张试卷有'.$currentExam['students'].'人参考，上传的分数为'.($rows-1).'条，请检核数据重新上传','warning');break;
	}
	
	foreach($exam_part_data_array as $exam_part_data){
		//插入大题
		$exam_part_array[]=db_insert('exam_part',$exam_part_data);
	}
	
	//创建一张临时表
	$q_create_temp_table="CREATE TEMPORARY TABLE `t` (`id` INT NOT NULL AUTO_INCREMENT, `num` CHAR( 6 ) NOT NULL,";
	
	foreach($exam_part_array as $exam_part){
		$q_create_temp_table.="`".$exam_part."` DECIMAL( 10, 1 ) NULL,";
	}
	
	$q_create_temp_table.=" PRIMARY KEY (`id`) ,UNIQUE (`num`))";
	
	db_query($q_create_temp_table);
	
	//excel表格上传到临时表
	$q_insert_t_score='INSERT INTO t (num,`'.implode('`,`',$exam_part_array).'`) VALUES';
	for($i=1; $i<$rows; $i++) {
		$q_insert_t_score.="('".$data->sheets[0]['cells'][$i][0]."'";
		for($j=1; $j<$cols; $j++) {
			$cell = isset($data->sheets[0]['cells'][$i][$j])?$data->sheets[0]['cells'][$i][$j]:'';
			$q_insert_t_score.=",".($cell==''?'NULL':"'".$cell."'")."";
		}
		$q_insert_t_score.=')';
		if($i!=$rows-1){
			$q_insert_t_score.=',';
		}
	}
	if(!db_query($q_insert_t_score)){
		showMessage('上传错误，可能有重复学号或者错误的学号','warning');
		break;
	}

	$q_search_illegal_student="
		SELECT id,num FROM t WHERE num NOT IN
		(
			SELECT view_student.num 
			FROM exam_student INNER JOIN view_student ON view_student.id=exam_student.student
			WHERE exam_student.exam='".$currentExam['exam']."'".(isset($currentExam['extra_course'])?" AND exam_student.extra_course='".$currentExam['extra_course']."'":'')."
		)
		LIMIT 1
	";

	$r_search_illegal_student=db_query($q_search_illegal_student);

	if(db_rows($r_search_illegal_student)==1){
		$illegal_line=db_fetch_array($r_search_illegal_student);
		showMessage(($illegal_line['id']+1).'行的"'.$illegal_line['num'].'"学号不正确，可能填写错误或学生不属于本场考试','warning');
		break;
	}
	
	foreach($exam_part_array as $exam_part){
		//插入分数
		$q_insert_score="
		REPLACE INTO score (student,exam,exam_paper,exam_part,score,is_absent,scorer,scorer_username,time)
		SELECT view_student.id,'".$currentExam['exam']."','".$currentExam['exam_paper']."','".$exam_part."',t.`".$exam_part."`,if(t.`".$exam_part."` IS NULL,1,0),'".$_SESSION['id']."','".$_SESSION['username']."','".$_G['timestamp']."'  
		FROM t INNER JOIN view_student ON t.num=view_student.num
		";
		mysql_query($q_insert_score);
	}
	
	showMessage('文件上传成功！');break;
}
?>