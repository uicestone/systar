<?php
class user extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function logout(){
		session_logout();
		redirect('');
	}
	
	function login(){
		if(is_logged()){
			//用户已登陆，则不显示登录界面
			redirect('');
		}
		
		if(is_posted('submit/login')){
			
			if($this->config->item('ucenter')){
		
				$ucenter_user=uc_user_login($this->input->post('username'),$this->input->post('password'));//ucenter验证密码
				
				if(!$ucenter_user){
					showMessage('Ucenter Error','warning');
		
				}elseif($ucenter_user[0]>0){
					if(session_login($ucenter_user[0])){
						$this->user->updateLoginTime();
						echo uc_user_synlogin($ucenter_user[0]);
						redirect('','js');
					}
				}else{
					showMessage('用户名或密码错','warning');
				}
			}else{
		
				if($user=$this->user->verify($this->input->post('username'),$this->input->post('password'))){
			
					$_SESSION['id']=$user['id'];
					$_SESSION['usergroup']=explode(',',$user['group']);
					$_SESSION['username']=$user['username'];
					
					foreach($_SESSION['usergroup'] as $group){
						if(method_exists($this->user,$group.'_set_session')){
							call_user_func(array($this->user,$group.'_set_session'),$_SESSION['id']);
						}
					}
					
					$this->user->updateLoginTime();
					if(method_exists($this->company,$this->config->item('company_type').'_init')){
						call_user_func(array($this->company,$this->config->item('company_type').'_init'));
					}
			
					if(!isset($user['password'])){
						redirect('user/profile');
					}else{
						redirect();
					}
			
				}else{
					showMessage('名字或密码错','warning');
				}
			}
		}
		session_destroy();
	}

	function profile(){
		$q_user="SELECT * FROM user WHERE id = '".$_SESSION['id']."'";
		$r_user=db_query($q_user);
		post('user',db_fetch_array($r_user));
		
		$submitable=false;
		
		if($this->input->post('submit')){
			
			$submitable=true;
			
			$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'],$_POST);
			
			if($this->config->item('ucenter')){
				if(uc_user_edit($_SESSION['username'],post('user_extra/password'),post('user/password_new'),NULL)>0){
					redirect('','js',NULL,true);
				}
			}else{
				if(!post('user/password_new')){
					unset($_SESSION[CONTROLLER]['post']['user']['password_new']);
					if(is_null(post('user/password'))){
						$submitable=false;
						showMessage('你还没有设置密码~','warning');
					}
				}
				
				if($this->user->edit($_SESSION['id'],post('user/password_new'),post('user/username'))){
					if(post('user/password_new')){
						post('user/password',post('user/password_new'));
					}
		
				}else{
					$submitable=false;
				}
				
				if($submitable){
					redirect('','js',NULL,true);
				}
			}
		}
	}
	
	/**
	 * ie6跳转提示页面
	 */
	function browser(){
	}
}
?>