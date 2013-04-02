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
