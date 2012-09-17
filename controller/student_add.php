<?php
getPostData(function(){
	global $_G;
	post('student/name','新学生'.$_G['timestamp']);
	
	post(IN_UICE.'/id',db_insert('user',array('group'=>'student')));
	//先创建用户，再创建学生
	
	db_insert(IN_UICE,post(IN_UICE));
},false);

$q_student_class="
	SELECT student_class.num_in_class AS num_in_class,
		CONCAT(RIGHT(10000+class.id,4),num_in_class) AS num,
		class.id AS class,class.name AS class_name,
		staff.name AS class_teacher_name
	FROM student_class
		INNER JOIN class ON student_class.class=class.id
		LEFT JOIN staff ON class.class_teacher=staff.id AND staff.company='".$_G['company']."'
	WHERE student='".post('student/id')."' 
		AND term='".$_SESSION['global']['current_term']."'
";
$student_class=db_fetch_first($q_student_class);
post('student_class',array('class'=>$student_class['class'],'num_in_class'=>$student_class['num_in_class']));
post('class/name',$student_class['class_name']);
isset($student_class['class_teacher_name']) && post('student_extra/class_teacher_name',$student_class['class_teacher_name']);
$submitable=false;//可提交性，false则显示form，true则可以跳转

if(is_posted('submit')){
	$submitable=true;

	$_SESSION[IN_UICE]['post']=array_replace_recursive($_SESSION[IN_UICE]['post'],$_POST);
	
	if(is_posted('submit/student_relatives')){
		student_addRelatives(post('student/id'),post('student_relatives'));
		unset($_SESSION[IN_UICE]['post']['student_relatives']);
	}
	
	if(is_posted('submit/student_relatives_delete')){
		student_deleteRelatives(post('student_relatives_check'));
	}
	
	if(is_posted('submit/student_behaviour')){
		if(student_addBehaviour(post('student/id'),post('student_behaviour'))){
			unset($_SESSION[IN_UICE]['post']['student_behaviour']);
		}else{
			$submitable=false;
		}
	}
	
	if((is_posted('submit/student_comment') || is_posted('submit/student')) && 
		is_permitted('student','interactive') && 
		(post('student_comment/title')!='' || post('student_comment/content')!='')
	){

		if(student_addComment(post('student/id'),post('student_comment'))){
			unset($_SESSION[IN_UICE]['post']['student_comment']);
		}else{
			$submitable=false;
		}
	}

	if(!is_posted('student/youth_league')){
		post('student/youth_league',0);
	}
	
	if(!is_posted('student/resident')){
		post('student/resident',0);
		post('student/dormitory','');
	}
	
	if(post('student/birthday')==''){
		unset($_SESSION[IN_UICE]['post']['student']['birthday']);
	}
	
	if(is_logged('student') && is_posted('submit/student')){
		$form_check=array(
			'birthday'=>'生日',
			'id_card'=>'身份证号',
			'race'=>'民族',
			'junior_school'=>'初中',
			'mobile'=>'手机',
			'phone'=>'固定电话',
			'email'=>'电子邮箱',
			'address'=>'地址',
			'neighborhood_committees'=>'居委会',
			'bank_account'=>'银行卡号'
			
		);
		
		foreach($form_check as $item => $warning){
			if(!post(IN_UICE.'/'.$item)){
				showMessage('请输入'.$warning,'warning');
				$submitable=false;
			}
		}
	}
	
	if(is_logged('student') && db_fetch_field("SELECT COUNT(id) FROM student_relatives WHERE student = '".$_SESSION['id']."'")<2){
		showMessage('请至少输入两位亲属，每输入一行需要点击“添加”按钮');
		$submitable=false;
	}
	
	if(empty($student_class)){
		if(!db_insert('student_class',array('student'=>post('student/id'),'class'=>post('student_class/class'),'num_in_class'=>post('student_class/num_in_class'),'term'=>$_SESSION['global']['current_term']))){
			$submitable=false;
		}
	}else{
		if(!db_update('student_class',post('student_class'),"student='".post('student/id')."' AND term='".$_SESSION['global']['current_term']."'")){
			$submitable=false;
		}
	}
	
	processSubmit($submitable,function(){
		$username=db_fetch_field("SELECT username FROM user WHERE id = '".post(IN_UICE.'/id')."'");
		if(!$username){
			student_update();
			db_query("UPDATE user INNER JOIN view_student USING (id) SET user.username=CONCAT(view_student.name,view_student.num),user.alias=view_student.num WHERE view_student.id = '".post(IN_UICE.'/id')."'");
		}
	});
}

$q_student_relatives="
	SELECT 
		id,name,relationship,work_for,contact
	FROM 
		student_relatives
	WHERE company='".$_G['company']."'
		AND `student`='".post('student/id')."'
";

$field_student_relatives=array(
	'checkbox'=>array('title'=>'<input type="submit" name="submit[student_relatives_delete]" value="删" />','orderby'=>false,'content'=>'<input type="checkbox" name="student_relatives_check[{id}]" >','td_title'=>' width=60px'),
	'name'=>array('title'=>'姓名','orderby'=>false),
	'relationship'=>array('title'=>'关系','orderby'=>false),
	'contact'=>array('title'=>'电话','orderby'=>false),
	'work_for'=>array('title'=>'单位','orderby'=>false)
);

$q_student_behaviour="
	SELECT name,type,date,level,content FROM student_behaviour WHERE student = '".post('student/id')."'
	LIMIT 5
";
$field_student_behaviour=array(
	'type'=>array('title'=>'类别','td_title'=>'width="10%"','orderby'=>false),
	'date'=>array('title'=>'日期','orderby'=>false),
	'name'=>array('title'=>'名称','td_title'=>'width="40%"','td'=>'title="{content}"','orderby'=>false),
	'level'=>array('title'=>'级别','orderby'=>false)
);

$q_student_comment="
	SELECT student_comment.title,student_comment.content,
		FROM_UNIXTIME(time,'%Y-%m-%d') AS time,IF(staff.name IS NULL,student_comment.username,staff.name) AS username 
	FROM student_comment LEFT JOIN staff ON staff.id=student_comment.uid 
	WHERE student = '".post('student/id')."' AND (reply_to IS NULL OR reply_to = '".$_SESSION['id']."' OR uid = '".$_SESSION['id']."')
	ORDER BY student_comment.time DESC
	LIMIT 5
";
$field_student_comment=array(
	'title'=>array('title'=>'标题','orderby'=>false),
	'content'=>array('title'=>'内容','td_title'=>'width="60%"','orderby'=>false),
	'username'=>array('title'=>'留言人','orderby'=>false),
	'time'=>array('title'=>'时间','orderby'=>false)
);

if($_G['as_controller_default_page']){
	$_SESSION['last_list_action']=$_SERVER['REQUEST_URI'];
}

$scores=student_get_scores(post('student/id'));
?>