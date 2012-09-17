<?php
if(is_logged('student')){
	$student=$_SESSION['id'];
}elseif(is_logged('parent')){
	$student=$_SESSION['child'];
}else{
	$student=intval($_GET['student']);
}

$course_array=db_toArray("SELECT id,name,chart_color FROM course");

$score_array=db_toArray("SELECT * FROM view_score WHERE student = '".$student."'");

$category=$series_raw=$series=array();

foreach($score_array as $score_line_id => $score){
	$category[]=$score['exam_name'];
	foreach($course_array as $course_id => $course){
		if(!isset($series_raw[$course_id])){
			$series_raw[$course_id]=array('name'=>$course['name'],'color'=>'#'.$course['chart_color']);
		}
		if(isset($score['rank_'.$course_id])){
			$series_raw[$course_id]['data'][]=$score['rank_'.$course_id];
		}else{
			$series_raw[$course_id]['data'][]=NULL;
		}
	}
}

foreach($series_raw as $series_id => $series_single){
	//将$series_raw中有分数的系列取出
	if(isset($series_single['data']) && array_sum($series_single['data'])>0){//data不都为NULL
		$series[]=$series_single;
	}
}

$series=json_encode($series,JSON_NUMERIC_CHECK);
$category=json_encode($category);

$scores=student_get_scores($student);
?>