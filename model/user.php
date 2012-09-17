<?php
function user_verify($username,$password){
	global $_G;

	$q_user="SELECT id,username,password,`group`,lastip,lastlogin,company FROM user 
			WHERE (username = '".$_POST['username']."' OR alias='".$_POST['username']."')
				AND (password = '".$_POST['password']."' OR password IS NULL)
				AND company='".$_G['company']."'
		";
	
	$user=db_fetch_first($q_user);
	
	if(empty($user)){
		return false;

	}else{
		return $user;
	}
}

function user_check($username,$data_type='id',$show_error=true){
	//$data_type:id,array
	global $_G;
	
	if(!$username){
		if($show_error){
			showMessage('请输入用户名','warning');
		}
		return -3;
	}

	$q="SELECT * FROM `user` WHERE company='".$_G['company']."' AND `username` = '".$username."'";
	$r=db_query($q);
	$num_lawyers=db_rows($r);

	if($num_lawyers==0){
		if($show_error){
			showMessage('没有这个用户：'.$username,'warning');
		}
		return -1;
		
	}/*elseif($num_lawyers>1){
		if($show_error){
			showMessage('此关键词存在多个符合职员','warning');
		}
		return -2;

	}*/else{
		$data=db_fetch_array($r);
		if($data_type=='array'){
			$return=$data;
		}else{
			$return=$data[$data_type];
		}
		return $return;
	}
}

function user_update_login_time(){
	global $_G;
	db_update('user',array('lastip'=>getIP(),'lastlogin'=>$_G['timestamp']),"id='".$_SESSION['id']."' AND company='".$_G['company']."'");
}

function user_student_set_session($user_id){
	$q_student="SELECT * from `view_student` WHERE `id`='".$user_id."'";
	$student=db_fetch_first($q_student);

	$_SESSION['class']=$student['class'];
	$_SESSION['class_name']=$student['class_name'];
	$_SESSION['grade']=$student['grade'];
	$_SESSION['grade_name']=$student['grade_name'];	
}

function user_teacher_set_session($user_id){
	$q_teacher="SELECT * FROM staff WHERE id = '".$user_id."'";
	$teacher=db_fetch_first($q_teacher);

	$_SESSION['course']=$teacher['course'];
	$_SESSION['teacher_group']=explode(',',$teacher['group']);
	
	if($class=db_fetch_first("SELECT id,grade FROM class WHERE class_teacher='".$_SESSION['id']."'")){
		$_SESSION['manage_class']=$class;
	}
}

function user_parent_set_session($user_id){
	$q_child="SELECT id FROM student WHERE parent='".$user_id."'";
	$_SESSION['child']=db_fetch_field($q_child);
}

function user_edit($user_id,$new_password=NULL,$new_username=NULL){
	if(isset($new_password)){
		if(db_update('user',array('password'=>$new_password),"id='".$user_id."'")){
			showMessage('成功修改密码');
		}else{
			return false;
		}
	}
	
	if(isset($new_username)){
		if(db_update('user',array('username'=>$new_username),"id='".$user_id."'") && db_affected_rows()){
			showMessage('成功修改用户名');
		}else{
			return false;
		}
	}
	
	return true;
}

function user_getRegionByIdcard($idcard){
	$region = db_fetch_field("SELECT name FROM user_idcard_region WHERE num = '".substr($idcard,0,6)."'");
	if($region){
		return $region;
	}else{
		return false;
	}
}

function user_verifyIdCard($idcard){
	if(!is_string($idcard) || strlen($idcard)!=18){
		return false;
	}
	$sum=$idcard[0]*7+$idcard[1]*9+$idcard[2]*10+$idcard[3]*5+$idcard[4]*8+$idcard[5]*4+$idcard[6]*2+$idcard[7]+$idcard[8]*6+$idcard[9]*3+$idcard[10]*7+$idcard[11]*9+$idcard[12]*10+$idcard[13]*5+$idcard[14]*8+$idcard[15]*4+$idcard[16]*2;
	$mod = $sum % 11;
	$vericode_dic=array(1, 0, 'x', 9, 8, 7, 6, 5, 4, 3, 2);
	if($vericode_dic[$mod] == strtolower($idcard[17])){
		return true;
	}
}

function user_getGenderByIdcard($idcard){
	if(is_string($idcard) && strlen($idcard)==18){
		return $idcard[16] % 2 == 1 ? '男' : '女';
	}else{
		return false;
	}
}
?>