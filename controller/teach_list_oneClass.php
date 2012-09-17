<?php
if(!got('class'))
	exit('class not define');

$_SESSION['teach']['class']=$_GET['class'];

$q="SELECT 
		course.name as course,
		teacher.name as teacher ,
		teach.term,
		teach.id
	FROM `teach`,teacher ,course
	WHERE 
		teach.class = '".$_SESSION['teach']['class']."' 
		and teach.teacher = teacher.id
		and teacher.course = course.id
	ORDER BY term DESC,course.id";

$field=array('id'=>array('title'=>'','content'=>'<input type="checkbox" name="teach[{id}]">','td_head'=>'width="30px"'),'course'=>'学科','teacher'=>'教师','term'=>'学期');

$form=array(
	'head'=>'<form method="get">'.
			'<div class="contentTableMenu">'.
				'<input type="text" name="action" value="oneClass" style="display:none;">'.
				'<input type="submit" value="转到"><label><input type="text" name="class" size="4">班</label>'.
			'</div>'.
			'</form>',
	'foot'=>'<form method="post">'.
			'<div class="contentTableMenu">'.
				'<label>本班本学期所有教师编号：（空格分隔）<input type="text" name="teachOfOneClass" size="80"></label><input type="submit" name="addByClass" value="提交">'.
			'</div>'.
			'</form>'
);

exportTable($field,$q,$form);
?>