<?php
define('IN_UICE','student');
chdir('../');

require 'config/config.php';
require 'view/common/head.htm';

print_r(db_list_fields('case'));

/*$course=10;
$prior_exam=14;

$course_field='course_'.$course;

if(in_array($course,array(1,2,3))){
	$is_extra_course=false;
}else{
	$is_extra_course=true;
}*/

/*$q="
UPDATE student_classdiv INNER JOIN
(
	SELECT view_student.id,view_student.name,view_student.gender,SUM(unioned.score) as score
	FROM (
		select student,".$course_field."*0.3 as score from view_score where exam = 2 and ".$course_field." IS NOT NULL
		union
		select student,".$course_field."*0.3 as score from view_score where exam = 11 and ".$course_field." IS NOT NULL
		union
		select student,".$course_field."*0.4 as score from view_score where exam = 14 and ".$course_field." IS NOT NULL
	)unioned INNER JOIN view_student ON unioned.student=view_student.id
	INNER JOIN student_classdiv USING(id)
";

if($is_extra_course){
	$q.="WHERE student_classdiv.extra_course=".$course;
}

$q.="
	GROUP BY student
	HAVING count(*)=3
)a USING (id)
SET student_classdiv.".($is_extra_course?'extra_course_score':$course_field)."=a.score
";*/

/*$q="
UPDATE 
	student_classdiv 
	INNER JOIN(
		SELECT view_student.id,view_student.name,view_student.gender,SUM(unioned.score) AS score
		FROM (
			SELECT student,".$course_field." AS score FROM view_score where exam = ".$prior_exam." and ".$course_field." IS NOT NULL
		)unioned INNER JOIN view_student ON unioned.student=view_student.id
";

if($is_extra_course){
	$q.="
		INNER JOIN student_classdiv USING(id)
		WHERE student_classdiv.extra_course=".$course."
	";
}

$q.="
		GROUP BY student 
	)b USING(id)
SET student_classdiv.".($is_extra_course?'extra_course_score':$course_field)."=b.score
WHERE student_classdiv.".($is_extra_course?'extra_course_score':$course_field)." IS NULL
";
echo $q;
db_query($q);

showMessage(db_affected_rows());*/
//print_r($_G);
print_r($_SESSION);

//forceExport();

/*$recipients=array();

$recipients=array_sub(db_toArray("SELECT content FROM client_contact WHERE type='电子邮件'"),'content');

$recipients=array_merge($recipients,array('uicestone@gmail.com','uicestone@126.com','uicestone@yahoo.cn'));

require 'Mail.php';

$headers['From'] = 'luqiushi@lawyerstars.com';
$headers['Return-Path']='luqiushi@lawyerstars.com';
$headers['Subject'] = '星瀚法律专刊2012年8月·“地王之争”的诉讼法律分析·中小企业融资难的解决之道';
$headers['Content-Type'] = 'text/html';
$headers['X-Mailer'] = 'php'.phpversion();
$body = file_get_contents('temp/journal1208.html');
$params['username'] = 'luqiushi@lawyerstars.com';
$params['password'] = '123';
$params['auth'] = true;
// Create the mail object using the Mail::factory method

foreach($recipients as $recipient){
	$headers['To']=$recipient;
	//$mail_object=new Mail();
	//$mail_object->factory('smtp', $params)->send($recipient, $headers, $body);
	echo $recipient.'<br>';
	//sleep(1);
}*/
?>