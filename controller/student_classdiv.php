<?php
$classes=2;
$subjects=4;

if(got('run')){
	set_time_limit(0);

	$data=db_toArray("
		SELECT id,gender,
			course_1 AS `0`,
			course_2 AS `1`,
			course_3 AS `2`,
			extra_course_score AS `3`
		FROM student_classdiv 
		WHERE type<>'借读'
			AND new_class IS NULL
			AND extra_course=5
	");
	
	//将数组key改为id
	$data_tmp=array();
	
	foreach($data as $key => $value){
		$data_tmp[$value['id']]=$value;
	}
	
	$data=$data_tmp;
	
	$students=count($data);
	
	//print_r($data);
	
	/*$data:array(
		学号=>$student:array(
			id=>学号
			gender=>性别
			1=>学科成绩
			2=>学科成绩
			...
		)
	)*/
	
	$exchanges=$tests=0;
	
	//每个性别一个数组
	$student_list_div_by_gender=array();
	foreach($data as $id => $line_data){
		$student_list_div_by_gender[$line_data['gender']][]=$line_data;
	}
	//print_r($student_list_div_by_gender);
	
	//生成第一种分班方案
	$div=array();
	for($gender=0;$gender<2;$gender++){
		for($i=0;$student=array_pop($student_list_div_by_gender[$gender]);$i++){
			$div[$gender][$i % $classes][]=$student['id'];
		}
		unset($student);
	}
	
	//print_r($div);
	
	/*分班方案$div:array(
		1(性别)=>array(
			1(班号)=>array(
				序号=>学号
			)
		)
		2=>array(
			...
		)
	)*/
	
	forceExport();
	
	while(!isset($foundBest[1]) || !isset($foundBest[0])){
		for($gender=0;$gender<2;$gender++){
			if(isset($foundBest[$gender])){
				continue;
			}
			$former_s=$s=student_testClassDiv($div,$data,$classes,$gender);
			$exchange=array();
			for($class_l=0;$class_l<$classes;$class_l++){
				for($class_r=$class_l+1;$class_r<$classes;$class_r++){
					for($student_l=0;$student_l<count($div[$gender][$class_l]);$student_l++){
						for($student_r=0;$student_r<count($div[$gender][$class_r]);$student_r++){
							$new_div[$gender]=$div[$gender];
							$tmp_student=$new_div[$gender][$class_r][$student_r];
							$new_div[$gender][$class_r][$student_r]=$new_div[$gender][$class_l][$student_l];
							$new_div[$gender][$class_l][$student_l]=$tmp_student;
							$t=student_testClassDiv($new_div,$data,$classes,$gender);
							$exchange[]=array('class_l'=>$class_l,'class_r'=>$class_r,'student_l'=>$student_l,'student_r'=>$student_r,'differ'=>$s-$t);
						}
					}
				}
			}
			$max_differ=0;
			foreach($exchange as $id=>$exchange_plan){
				if($exchange_plan['differ']>$max_differ){
					$max_differ=$exchange_plan['differ'];
					$max_id=$id;
				}
			}
			if($max_differ>0){
				$tmp_student=$div[$gender][$exchange[$max_id]['class_r']][$exchange[$max_id]['student_r']];
				$div[$gender][$exchange[$max_id]['class_r']][$exchange[$max_id]['student_r']]=$div[$gender][$exchange[$max_id]['class_l']][$exchange[$max_id]['student_l']];
				$div[$gender][$exchange[$max_id]['class_l']][$exchange[$max_id]['student_l']]=$tmp_student;
				
				$s=student_testClassDiv($div,$data,$classes,$gender);
				
				echo 'gender='.$gender.', s='.$s."<br>\n";flush();
	
				$exchanges++;
			}
	
			
			if($s==$former_s){
				$foundBest[$gender]=true;
			}
		}
		
		echo "\n".$tests.'tests, '.$exchanges.'exchanges'."<br><br>\n\n";flush();
	}
	echo 'completed';
	
	//student_testClassDiv($div,$data,$classes,0,true);
	//student_testClassDiv($div,$data,$classes,1,true);
	//print_r($div);
	
	foreach($div as $gender_in_array1 => $array1){
		foreach($array1 as $class=>$array2){
			db_update('student_classdiv',array('new_class'=>$class),'id IN ('.implode(',',$array2).')');
		}
	}
}else{

	$q="SELECT * FROM student_classdiv";
	
	$list_locator=processMultipage($q);
	
	$field=array(
		'name'=>array('title'=>'姓名'),
		'gender'=>array('title'=>'性别'),
		'course_1'=>array('title'=>'语文'),
		'course_2'=>array('title'=>'数学'),
		'course_3'=>array('title'=>'英语')
	);
	
	$menu=array('head'=>'<div class="right">'.$list_locator.'</div>');
	
	exportTable($q,$field,$menu);
}
?>