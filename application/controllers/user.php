<?php
class user extends SS_controller{
	function __construct(){
		$this->require_permission_check=false;
		parent::__construct();
	}
	
	function logout(){
		$this->user->sessionLogout();
		redirect('');
	}
	
	function login(){
		
		if($this->user->isLogged()){
			//用户已登陆，则不显示登录界面
			redirect('','js',NULL,true);
		}
		
		if($this->input->post('submit/login')){
			
			if($this->company->ucenter){
		
				$ucenter_user=uc_user_login($this->input->post('username'),$this->input->post('password'));//ucenter验证密码
				
				if(!$ucenter_user){
					showMessage('Ucenter Error','warning');
		
				}elseif($ucenter_user[0]>0){
					if($this->sessionLogin($ucenter_user[0])){
						$this->user->updateLoginTime();
						echo uc_user_synlogin($ucenter_user[0]);
						redirect('','js');
					}
				}else{
					showMessage('用户名或密码错','warning');
				}
			}else{
		
				if($user=$this->user->verify($this->input->post('username'),$this->input->post('password'))){
			
					$this->session->set_userdata('user/id', $user['id']);
					$this->session->set_userdata('user/name', $user['name']);
					
					$user['group']=explode(',',$user['group']);
					$this->session->set_userdata('user/group', $user['group']);
					
					$this->user->__construct();
					
					foreach($this->user->group as $group){
						$company_type=$this->company->type;
						if($this->company_type_model_loaded && method_exists($this->$company_type,$group.'_setSession')){
							call_user_func(array($this->$company_type,$group.'_setSession'),$this->user->id);
						}
					}
					
					$this->user->updateLoginTime();
			
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
		$this->load->view('head');
		$this->load->view('user/login');
		$this->load->main_view_loaded=true;
		$this->load->view('foot');
		session_destroy();
	}

	function profile(){
		$q_user="SELECT * FROM user WHERE id = {$this->user->id}";
		$r_user=db_query($q_user);
		post('user',db_fetch_array($r_user));
		
		$submitable=false;
		
		if($this->input->post('submit')){
			
			$submitable=true;
			
			$_SESSION[CONTROLLER]['post']=array_replace_recursive($_SESSION[CONTROLLER]['post'],$_POST);
			
			if($this->company->ucenter){
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
				
				if($this->user->edit($this->user->id,post('user/password_new'),post('user/username'))){
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
		$this->load->require_nav_menu=false;
	}
}
?>