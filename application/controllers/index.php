<?php
class Index extends SS_Controller{
	function __construct() {
		$this->permission=array(
			'index'=>array(),
			'browser'=>array(),
			'reception'=>true,
			'favicon'=>true,
			'robots'=>true
		);
		parent::__construct();
	}
	
	function index(){
		
		$this->load->addViewData('css', $this->config->user_item('css'));
		$this->load->view('head');
		$this->load->view('menu');
		$this->load->view('nav');
		$this->load->view('frame');
		$this->load->view('foot');
	}
	
	/**
	 * ie6跳转提示页面
	 */
	function browser(){
		$this->output->title='请更新您的浏览器';
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
				'start'=>time(),
				'end'=>time()+3600,
				'completed'=>false
			));

			if($insert_id){
				$this->output->set_output('您提交的信息已经收到！');
			}
		}catch(Exception $e){
			foreach($this->output->message as $messages){
				foreach($messages as $message){
					echo $message."\n";
				}
			}
		}
		
	}
	
	function robots(){
		$this->output->set_output($this->config->user_item('robots'));
	}
	
	function favicon(){
		
		$this->output->set_content_type('ico');
		
		foreach(array(
			APPPATH.'../web/images/favicon_'.COMPANY_CODE.'.ico',
			APPPATH.'../web/images/favicon_'.COMPANY_TYPE.'.ico',
			APPPATH.'../web/images/favicon.ico',
		) as $path){
			if(file_exists($path)){
				readfile($path);
			}
		}
	}
}

?>
