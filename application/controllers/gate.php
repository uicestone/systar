<?php
class Gate extends SS_Controller{
	function __construct() {
		$this->require_login=false;
		parent::__construct();
	}
	
	function index(){
		$this->load->view('head');
		$this->load->view('nav');
		$this->load->view('menu');
		$this->load->view('frame');
		$this->load->view('foot');
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
		$this->load->view('user/signup');
		$this->load->view('user/signup_sidebar',true,'sidebar');
	}
	
	/**
	 * ie6跳转提示页面
	 */
	function browser(){
		$this->section_title='请更新您的浏览器';
		$this->load->view('head');
		$this->load->view('browser');
		$this->load->view('foot');
	}
	
	/**
	 * 接待台
	 * 接受系统外部提交的数据至本公司日程
	 */
	function reception(){
		try{
			$this->load->model('schedule_model','schedule');
			$this->load->model('staff_model','staff');
			$receptionist=$this->staff->check($this->input->post('to'));

			$content='';
			foreach($this->input->post() as $name=>$value){
				$content.=$name.': '.$value."\n";
			}
			$insert_id=$this->schedule->add(array(
				'name'=>$this->input->post('title'),
				'people'=>$receptionist,
				'content'=>$content,
				'time_start'=>$this->date->now,
				'time_end'=>$this->date->now+3600,
				'completed'=>false
			));

			if($insert_id){
				echo '您提交的信息已经收到！';
			}
		}catch(Exception $e){
			foreach($this->output->message as $messages){
				foreach($messages as $message){
					echo $message."\n";
				}
			}
		}
		
	}
}

?>
