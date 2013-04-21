<?php
class user extends SS_controller{
	
	function __construct(){
		
		$this->gate_pages=array('signup','login','setfields','submit');
		
		parent::__construct();
		
		$this->load->model('people_model','people');
		
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

				$this->user->__construct($user['id']);

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
		
		$this->load->view('head_simple');
		$this->load->view('user/login');
		$this->load->view('foot');

	}
	
	function signUp(){
		$this->section_title='新用户注册';
		$this->load->view('user/signup');
		$this->load->view('user/signup_sidebar',true,'sidebar');
	}
	
	function profile(){
		
		$people=array_merge_recursive($this->people->fetch($this->user->id),$this->input->sessionPost('people'));
		$people_profiles=array_merge_recursive(array_sub($this->people->getProfiles($this->user->id),'content','name'),$this->input->sessionPost('people'));
		$this->load->addViewArrayData(compact('people','people_profiles'));
		
		$this->section_title='用户资料';
		$this->load->view('user/profile');
		$this->load->view('user/profile_sidebar',true,'sidebar');
	}
	
	function submit($submit){
		
		$this->load->library('form_validation');
		
		try{
			
			if($submit=='profile'){
				if($this->input->post('password_new')){
					if($this->user->verify($this->user->name, $this->input->post('password'))===false){
						throw new Exception('当前密码错误');
					}
					if($this->input->post('password_new')!==$this->input->post('password_new_confirm')){
						throw new Exception('两次密码输入不一致');
					}
					$this->user->updatePassword($this->user->id, $this->input->post('password_new'));
					$this->output->message('用户名/密码修改成功');
				}
				
				if($this->input->post('username') && $this->input->post('username')!==$this->user->name){
					$this->form_validation->set_rules('username','用户名','is_unique[user.name]');
					if($this->form_validation->run()!==false){
						$this->user->updateUsername($this->user->id, $this->input->post('username'));
					}else{
						throw new Exception(validation_errors());
					}
				}
				
				$people=$this->input->sessionPost('people');
				$profiles=$this->input->sessionPost('people_profiles');
				
				$this->people->update($this->user->id,$people);
				$this->people->updateProfiles($this->user->id, $profiles);
				
				$this->output->message($this->section_title.' 已保存');
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
				
				$this->session->set_userdata('user/id',$user_id);
				$this->user->__construct($user_id);
				
				$this->output->status='redirect_href';
				$this->output->data='';
			}
		}
		catch (Exception $e){
			if($e->getMessage()){
				$this->output->message($e->getMessage(), 'warning');
			}
			$this->output->status='failed';
		}
		
		if(is_null($this->output->status)){
			$this->output->status='success';
		}
	}
}
?>