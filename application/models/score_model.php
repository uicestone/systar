<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Score_Model
 *
 * @author Uice
 */
class Score_model {
	$q="SELECT * FROM view_score INNER JOIN view_student ON view_student.id=view_score.student WHERE 1=1";

	if(!option('class') && !option('grade')){
		$manage_class=db_fetch_first("SELECT id,grade FROM class WHERE class_teacher='".$_SESSION['id']."'");
		if($manage_class){
			//将班主任的视图定位到自己班级
			option('class',$manage_class['id']);
			option('grade',$manage_class['grade']);
		}else{
			//默认显示的年级
			option('grade',$_SESSION['global']['highest_grade']);
		}
	}

	$q=$this->addCondition($q,array('class'=>'view_student.class','grade'=>'view_student.grade'),array('grade'=>array('class','exam')));

	if(!option('exam')){
		option('exam',db_fetch_field("SELECT id FROM exam WHERE grade='".option('grade')."' ORDER BY id DESC LIMIT 1"));
	}

	$q=$this->addCondition($q, array('exam'=>'view_score.exam'));

	$q=$this->search($q,array('view_student.name'=>'学生'));

	$q=$this->orderby($q,'view_student.num');

}

?>
