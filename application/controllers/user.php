<?php
class user extends SS_controller{
	function __construct(){
		$this->require_permission_check=false;
		parent::__construct();
	}
	
	function submit($submit){
		$this->load->require_head=false;
		
		if(parent::submit($submit)){
			echo 'success';
			return;
		}
		
		if($submit=='login'){
			if($this->company->ucenter){

				$ucenter_user=uc_user_login($this->input->post('username'),$this->input->post('password'));//ucenter验证密码

				if(!$ucenter_user){
					$this->output->message('Ucenter Error','warning');

				}elseif($ucenter_user[0]>0){
					if($this->sessionLogin($ucenter_user[0])){
						$this->user->updateLoginTime();
						echo uc_user_synlogin($ucenter_user[0]);
						echo json_encode(array('uri'=>'/'));
					}
				}else{
					$this->output->message('用户名或密码错','warning');
				}
			}else{
				$user=$this->user->verify($this->input->post('username'),$this->input->post('password'));

				if($user){

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
						echo json_encode(array('url'=>'/#user/profile'));
					}else{
						echo json_encode(array('url'=>'_default'));
					}

				}else{
					$this->output->message('名字或密码错','warning');
					echo json_encode(array('message'=>$this->output->message));
				}
				
			}	
		}
	}
	
	function logout(){
		$this->user->sessionLogout();
		echo json_encode(array('status'=>'require_login'));
	}
	
	function login(){
		
		if($this->user->isLogged()){
			//用户已登陆，则不显示登录界面
			redirect('','js',NULL,true);
		}
		
		$this->load->view('user/login');
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