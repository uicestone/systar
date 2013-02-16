<?php
class user extends SS_controller{
	function __construct(){
		$this->require_permission_check=false;
		parent::__construct();

		if($this->company->ucenter){
			require APPPATH.'third_party/ucenter_client/config.php';
			require APPPATH.'third_party/ucenter_client/client.php';
		}
	}
	
	function logout(){
		$this->user->sessionLogout();
		redirect('login');
	}
	
	function login(){
		
		if($this->input->post('login')){
			
			$user=array();
			
			if($this->company->ucenter){
				
				$ucenter_user=uc_user_login($this->input->post('username'),$this->input->post('password'));//ucenter验证密码

				if(!$ucenter_user){
					showMessage('ucenter Error','warning');

				}elseif($ucenter_user[0]>0){
					$user=$this->user->fetch($ucenter_user[0]);
				}
				
			}else{
				$user=$this->user->verify($this->input->post('username'),$this->input->post('password'));
			}

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

				if($this->company->ucenter && !isset($user['password'])){
					redirect('#user/profile');
				}else{
					redirect();
				}

			}else{
				showMessage('名字或密码错','warning');
			}
		}
		
		if($this->user->isLogged()){
			//用户已登陆，则不显示登录界面
			$this->output->setData('uri');
		}else{
			$this->output->as_ajax=false;
		
			$this->load->view('head');
			$this->load->view('user/login');
			$this->load->view('foot');
		}

	}

	function profile(){
		$this->output->setData('用户资料','name');
		$this->load->view('user/profile');
	}
	
	function submit($submit){
		if($submit=='profile'){
			$this->user->updatePassword($this->user->id, $this->input->post('user/password_new'), $this->input->post('user/username')?$this->input->post('user/username'):NULL);
			$this->output->message('用户名/密码修改成功');
		}
		$this->output->status='success';
	}
	
	/**
	 * ie6跳转提示页面
	 */
	function browser(){
		$this->load->view('user/browser');
	}
}
?>