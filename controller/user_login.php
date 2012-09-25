<?php
if(is_logged()){
	//用户已登陆，则不显示登录界面
	redirect('/');
}

if(is_posted('submit/login')){
	
	if($_G['ucenter']){

		$ucenter_user=uc_user_login($_POST['username'],$_POST['password']);//ucenter验证密码
		
		if(!$ucenter_user){
			showMessage('Ucenter Error','warning');

		}elseif($ucenter_user[0]>0){
			if(session_login($ucenter_user[0])){
				user_update_login_time();
				echo uc_user_synlogin($ucenter_user[0]);
				redirect('/','js');
			}
		}else{
			showMessage('用户名或密码错','warning');
		}
	}else{

		if($user=user_verify($_POST['username'],$_POST['password'])){
	
			$_SESSION['id']=$user['id'];
			$_SESSION['usergroup']=explode(',',$user['group']);
			$_SESSION['username']=$user['username'];
			
			foreach($_SESSION['usergroup'] as $group){
				if(function_exists('user_'.$group.'_set_session')){
					call_user_func('user_'.$group.'_set_session',$_SESSION['id']);
				}
			}
			
			user_update_login_time();
			
			if(function_exists($_G['company_type'].'_init')){
				call_user_func($_G['company_type'].'_init');
			}
	
			if(!isset($user['password'])){
				redirect('user?profile');
			}else{
				redirect('/');
			}
	
		}else{
			showMessage('名字或密码错','warning');
		}
	}
}
session_destroy();
?>