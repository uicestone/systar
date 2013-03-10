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
		if($this->company->ucenter){
			redirect('login','js');
		}else{
			redirect('login');
		}
	}
	
	function login(){
		
		if($this->user->isLogged()){
			//用户已登陆，则不显示登录界面
			redirect();
		}
		
		if($this->input->post('username')){
			
			$user=array();
			
			if($this->company->ucenter){
				
				$ucenter_user=uc_user_login($this->input->post('username'),$this->input->post('password'));//ucenter验证密码

				if(!$ucenter_user){
					$this->load->addViewData('warning','用户名或密码错');

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

				if(!$this->company->ucenter && !isset($user['password'])){
					redirect('#user/profile');
				}elseif(!$this->company->ucenter){
					redirect();
				}else{
					redirect('','js');
				}

			}else{
				$this->load->addViewData('warning','用户名或密码错');
			}
		}
		
		$this->output->as_ajax=false;

		$this->load->view('head_simple');
		$this->load->view('user/login');
		$this->load->view('foot');

	}
	
	function profile(){
		
		$people=array_merge_recursive($this->user->fetch($this->user->id),$this->input->sessionPost('people'));
		$people_profiles=array_merge_recursive(array_sub($this->user->getProfiles($this->user->id),'content','name'),$this->input->sessionPost('people'));
		$this->load->addViewArrayData(compact('people','people_profiles'));
		
		$this->output->setData('用户资料','name');
		$this->load->view('user/profile');
		$this->load->view('user/profile_sidebar',true,'sidebar');
	}
	
	function signUp(){
		$this->load->view('user/signup');
		$this->load->view('user/signup_sidebar',true,'sidebar');
	}
	
	function submit($submit){
		
		$this->load->library('form_validation');
		
		try{
			
			if($submit=='profile'){
				
				if($this->input->post('user/password')){
					$this->user->updatePassword($this->user->id, $this->input->post('user/password_new'), $this->input->post('user/username')?$this->input->post('user/username'):NULL);
					$this->output->message('用户名/密码修改成功');
				}
				
				$people=$this->input->sessionPost('people');
				$profiles=$this->input->sessionPost('people_profiles');
				
				$this->user->update($this->user->id,$people);
				$this->user->updateProfiles($this->user->id, $profiles);
				
				$this->output->message('你的报名信息已经保存，我们将在合适的时候联系你。你也可以随时回来补充资料，或是用“日程”功能为自己安排计划');
				
				unset($_SESSION['user']['post']);
				
				$this->output->status='close';
			}

			elseif($submit=='signup'){

				$this->form_validation->set_rules('username','用户名','required|is_unique[user.name]')
						->set_rules('password','密码','required')
						->set_rules('password_confirm','确认密码','matches[password]')
						->set_message('matches','两次密码输入不一致');

				if($this->form_validation->run()===false){
					$this->output->message(validation_errors(),'warning');
					throw new Exception;
				}
				
				$data=array(
					'name'=>$this->input->post('username'),
					'password'=>$this->input->post('password')
				);
				
				$user_id=$this->user->add($data);
				
				$this->output->status='login_required';

			}
		}
		catch (Exception $e){
			$this->output->status='failed';
		}
		
		if(is_null($this->output->status)){
			$this->output->status='success';
		}
	}
	
	/**
	 * ie6跳转提示页面
	 */
	function browser(){
		$this->output->as_ajax=false;
		$this->output->setData('请更新您的浏览器','name');
		$this->load->view('head');
		$this->load->view('browser');
		$this->load->view('foot');
	}
}
?>