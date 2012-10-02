<?php
class user extends SS_controller{
	function __construct(){
		parent::__construct();
	}
	
	function logout(){
		session_logout();
		redirect('/');
	}
	
	function login(){
		global $_G;
		$this->load->model('user_model','model');
		
		if(is_logged()){
			//用户已登陆，则不显示登录界面
			redirect('');
		}
		
		if(is_posted('submit/login')){
			
			if($_G['ucenter']){
		
				$ucenter_user=uc_user_login($_POST['username'],$_POST['password']);//ucenter验证密码
				
				if(!$ucenter_user){
					showMessage('Ucenter Error','warning');
		
				}elseif($ucenter_user[0]>0){
					if(session_login($ucenter_user[0])){
						$this->model->update_login_time();
						echo uc_user_synlogin($ucenter_user[0]);
						redirect('','js');
					}
				}else{
					showMessage('用户名或密码错','warning');
				}
			}else{
		
				if($user=$this->model->verify($_POST['username'],$_POST['password'])){
			
					$_SESSION['id']=$user['id'];
					$_SESSION['usergroup']=explode(',',$user['group']);
					$_SESSION['username']=$user['username'];
					
					foreach($_SESSION['usergroup'] as $group){
						if(function_exists('user_'.$group.'_set_session')){
							call_user_func('user_'.$group.'_set_session',$_SESSION['id']);
						}
					}
					
					$this->model->update_login_time();
					
					if(function_exists($_G['company_type'].'_init')){
						call_user_func($_G['company_type'].'_init');
					}
			
					if(!isset($user['password'])){
						redirect('user?profile');
					}else{
						redirect('');
					}
			
				}else{
					showMessage('名字或密码错','warning');
				}
			}
		}
		session_destroy();
		$this->load->view('head');
		$this->load->view('user/login');
		$this->load->view('foot');	
	}

	function profile(){
		$q_user="SELECT * FROM user WHERE id = '".$_SESSION['id']."'";
		$r_user=db_query($q_user);
		post('user',db_fetch_array($r_user));
		
		$submitable=false;
		
		if(is_posted('submit')){
			
			$submitable=true;
			
			$_SESSION[IN_UICE]['post']=array_replace_recursive($_SESSION[IN_UICE]['post'],$_POST);
			
			if($_G['ucenter']){
				if(uc_user_edit($_SESSION['username'],$_POST['password'],$_POST['password_new'],$_POST['email'])>0){
					redirect('','js');
				}
			}else{
				if(!post('user/password_new')){
					unset($_SESSION[IN_UICE]['post']['user']['password_new']);
					if(is_null(post('user/password'))){
						$submitable=false;
						showMessage('你还没有设置密码~','warning');
					}
				}
				
				if($this->model->edit($_SESSION['id'],post('user/password_new'),post('user/username'))){
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
}
?>