<?php
$q_user="SELECT * FROM user WHERE id = '".$_SESSION['id']."'";
$r_user=db_query($q_user);
post('user',db_fetch_array($r_user));

$submitable=false;

if(is_posted('submit')){
	
	$submitable=true;
	
	$_SESSION[IN_UICE]['post']=array_replace_recursive($_SESSION[IN_UICE]['post'],$_POST);
	
	if($_G['ucenter']){
		if(uc_user_edit($_SESSION['username'],$_POST['password'],$_POST['password_new'],$_POST['email'])>0){
			redirect('/','js');
		}
	}else{
		if(!post('user/password_new')){
			unset($_SESSION[IN_UICE]['post']['user']['password_new']);
			if(is_null(post('user/password'))){
				$submitable=false;
				showMessage('你还没有设置密码~','warning');
			}
		}
		
		if(user_edit($_SESSION['id'],post('user/password_new'),post('user/username'))){
			if(post('user/password_new')){
				post('user/password',post('user/password_new'));
			}

		}else{
			$submitable=false;
		}
		
		if($submitable){
			redirect('/','js',NULL,true);
		}
	}
}
?>