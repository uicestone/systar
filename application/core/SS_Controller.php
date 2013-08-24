<?php
class SS_Controller extends CI_Controller{
	
	var $default_method='index';
	
	/**
	 * 当前控制器允许的用户组
	 * array()为登录即可
	 * true为不限制
	 * 包含子数组时，按照独立方法区分权限，子数组的键名是方法名
	 * @var bool or array 
	 */
	var $permission=array();
	
	function __construct(){
		parent::__construct();
		
		/*
		 * 处理$class和$method，并定义为常量
		 */
		global $class,$method;
		
		//使用controller中自定义的默认method
		if($method=='index'){
			$method=$this->default_method;
		}
		
		//定义$class常量，即控制器的名称
		define('CONTROLLER',$class);
		define('METHOD',$method);
		
		//CONTROLLER !=='index' && $this->output->enable_profiler(TRUE);

		/*
		 * 自动载入的资源，没有使用autoload.php是因为后者载入以后不能起简称...
		 */
		$this->load->model('company_model','company');
		$this->load->model('people_model','people');
		$this->load->model('group_model','group');
		$this->load->model('user_model','user');
		$this->load->model('tag_model','tag');
		$this->load->model('message_model','message');
		
		$this->user->initialize();
		
		if($this->input->is_ajax_request()){
			$this->output->as_ajax=true;
			$this->output->set_content_type('application/json');
		}
		
		/*
		 * 页面权限判断
		 */
		if(isset($this->permission[METHOD])){
			$this->permission=$this->permission[METHOD];
		}
		
		if($this->permission===array() && !$this->user->isLogged()){
			if($this->output->as_ajax){
				$this->output->status='login';
				$this->_output();
				exit;
			}else{
				redirect('login');
			}
		}
		
		if(is_array($this->permission) && $this->permission && (!$this->user->groups || !array_intersect(array_keys($this->user->groups),$this->permission))){
			if($this->output->as_ajax){
				$this->output->status='denied';
				$this->output->message('no permission','warning');
				exit;
			}else{
				show_error('no permission');
			}
		}

		if(file_exists(APPPATH.'language/chinese/'.$this->company->syscode.'_lang.php')){
			$this->load->language($this->company->syscode);
		}
		
		if(CONTROLLER==='index' && METHOD!=='browser' && $this->agent->browser()==='Internet Explorer' && $this->agent->version()<8){
			redirect('browser');
		}
		
		$this->config->session=$this->session->all_userdata('config');
		
		$this->output->title=lang(CONTROLLER);
		
		$uri=$this->uri->uri_string;
		$get=$this->input->get();
		$post=$this->input->post();
		if($post){
			$this->db->insert('log',array(
				'uri'=>$uri,
				'host'=>$this->input->server('HTTP_HOST'),
				'get'=>json_encode($get,256),
				'post'=>json_encode($post,256),
				'client'=>$this->input->user_agent(),
				//'duration'=>$this->benchmark->elapsed_time('total_execution_time_start'),
				'ip'=>$this->input->ip_address(),
				'company'=>$this->company->id,
				'username'=>$this->user->name,
				'time'=>date('Y-m-d H:i:s',time())
			));
		}
	}
	
	function fetch($id){
		$args=$this->input->get();
		
		if($args===false){
			$args=array();
		}
		
		$controller=CONTROLLER;
		$this->output->set_output(json_encode($this->$controller->fetch($id,$args)));
	}
	
	function getList(){
		$args=$this->input->get();

		if($args===false){
			$args=array();
		}
		
		$controller=CONTROLLER;
		$this->output->set_output(json_encode($this->$controller->getList($args)));
	}
	
	function removeTag($item_id){
		
		$controller=CONTROLLER;
		
		$label_name=$this->input->post('label');
		
		$this->$controller->removeTag($item_id, $label_name);
	}
	
	function addTag($item_id){
		$controller=CONTROLLER;
		
		$label_name=$this->input->post('label');
		
		$this->$controller->addTag($item_id, $label_name);
	}
	
}
?>