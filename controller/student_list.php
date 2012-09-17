<?php
if(got('update')){
	student_update();
	showMessage('学生视图更新完成');
}

$q=
"SELECT 
	student.id,student.name AS name,student.id_card,student.phone,student.mobile,student.address,
	student_num.num,
	class.name AS class_name,
	relatives.contacts AS relatives_contacts
FROM 
	student
	INNER JOIN (
		SELECT student,class,
			right((1000000 + concat(student_class.class,right((100 + student_class.num_in_class),2))),6) AS num
		FROM student_class
		WHERE student_class.term = '".$_SESSION['global']['current_term']."'
	)student_num ON student_num.student=student.id
	INNER JOIN class ON class.id=student_num.class
	LEFT JOIN (
		SELECT student.id AS student,GROUP_CONCAT(student_relatives.contact) AS contacts
		FROM student INNER JOIN student_relatives ON student_relatives.student=student.id
		WHERE student_relatives.contact<>''
		GROUP BY student.id
	)relatives
	ON relatives.student=student.id
WHERE student.display=1
	AND (class.id=(SELECT id FROM class WHERE class_teacher='".$_SESSION['id']."')
		OR '".(is_logged('jiaowu') || is_logged('zhengjiao') || is_logged('health'))."'='1')
";
//班主任可以看到自己班级的学生，教务和政教可以看到其他班级的学生

//将班主任的视图定位到自己班级
if(!option('class') && !option('grade') && isset($_SESSION['manage_class'])){
	option('class',$_SESSION['manage_class']['id']);
	option('grade',$_SESSION['manage_class']['grade']);
}
addCondition($q,array('class'=>'class.id','grade'=>'class.grade'),array('grade'=>'class'));
		
$search_bar=processSearch($q,array('num'=>'学号','student.name'=>'姓名'));

processOrderby($q,'num','ASC',array('num','student.name'));

$listLocator=processMultiPage($q);

$field=array(
	'num'=>array('title'=>'学号','td'=>'id="{id}" '),
	'student.name'=>array('title'=>'姓名','content'=>'<a href="student?edit={id}">{name}</a>'),
	'student_num.class'=>array('title'=>'班级','content'=>'{class_name}')
);

if(is_logged('health')){
	$field+=array(
		'id_card'=>array('title'=>'身份证'),
		'mobile'=>array('title'=>'手机'),
		'relatives_contacts'=>array('title'=>'亲属电话'),
		'phone'=>array('title'=>'家庭电话'),
		'address'=>array('title'=>'家庭地址')
	);
}

if(is_logged('jiaowu')){
	$field['student_num.class']['td']='class="editable"';
}

$submitBar=array(
	'head'=>'<div class="right">'.
				$listLocator.
			'</div>'
);

$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];

exportTable($q,$field,$submitBar,true);
?>