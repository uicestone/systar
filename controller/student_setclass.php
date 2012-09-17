<?php
model('class');

$student_id=intval($_POST['id']);
$value=$_POST['value'];

$return=array();

$class_check=class_check($value,'array',false);

if($class_check==-1){
	$return['notice']='没有这个班级';
}elseif($class_check==-2){
	$return['notice']='此关键词存在多个符合班级';
}elseif($class_check==-3){
	$return['notice']='请输入班级名称';
}

if($class_check<0){
	$class_original=class_fetchByStudentId($student_id);
	$return['value']=$class_original['name'];

}else{
	$new_class_id=$class_check['id'];
	$old_class=class_fetchByStudentId($student_id);
	
	$return['num']=student_changeClass($student_id,$old_class['id'],$new_class_id);
	
	$new_class=class_fetch($new_class_id);
	
	$return['value']=$new_class['name'];
}

echo json_encode($return);
?>